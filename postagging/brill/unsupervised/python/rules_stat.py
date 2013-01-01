#coding: utf-8

import sys
from time import clock
from cStringIO import StringIO
from pprint import pprint

from utils import split_into_sent, context_stats, read_corpus, get_pos_tags

CONTEXT = ('w-1', 't-1', 'w+1', 't+1')


def scores(s, best_rules):
    rule = []
    scores = {}
    score = 0
    bestscore = 0
    bestrule = []
    for atag in s.keys():
        if len(atag) > 4:
            stat = s[atag]
            vtags = atag.split('_')
            for y in vtags:
                scores[atag] = {y: dict(zip(CONTEXT, [{} for i in range(4)]))}
                for ctype in CONTEXT:
                    for context in stat[ctype].keys():
                        fr = -sys.maxint
                        r = y
                        for z in vtags:
                            if y != z:
                                # relative frequency
                                try:
                                    relf = float(s[y]['freq']) / float(s[z]['freq']) * float(s[z][ctype][context])
                                except:
                                    relf = 0
                                if relf > fr:
                                    fr = relf
                                    r = z
                        try:
                            x = s[y][ctype][context] - float(fr)
                            scores[atag][y][ctype][context] = x
                            if x >= bestscore and [atag, y, ctype, context] not in best_rules:
                                #if context in s[atag][ctype].keys():
                                bestscore = x
                                #print bestscore
                                bestrule = [atag, y, ctype, context]
                                #print bestrule
                        except:
                            #scores[atag][y][ctype][context] = - fr
                            pass
    return scores, bestrule, bestscore


def apply_rule(rule, table):
    applied = StringIO()
    for sent in split_into_sent(table):
        sent = sent.strip('\n')
        tokens = sent.split('\n')
        if len(tokens) == 0:
            continue
        tokens.append('/sent')
        word_2, tag_2 = 'sent', 'sent'
        try:
            id_1, word_1, tag_1 = tokens[1].split('\t')[0], tokens[1].split('\t')[1], get_pos_tags(tokens[1])
            if word_1.isdigit():
                word_1 = '_N_'
        except:
            pass
        i = 1
        for token in tokens[2:-1]:
            tag = get_pos_tags(token)
            id = token.split('\t')[0]
            try:
                word = token.split('\t')[1]
            except:
                print tokens[i - 1]
                raise Exception
            if word.isdigit():
                word = '_N_'
            if tag_1 == rule.tagset:
                gr_list = tokens[i].split('\t')[2:]
                if rule.context_type == 'previous tag':
                    if tag_2 == rule.context:
                        tokens[i] = id + '\t' + word
                        for grammeme in gr_list[:]:
                            if rule.tag not in grammeme.split(' ')[2:]:
                                gr_list.remove(grammeme)
                        for grammeme in gr_list:
                            tokens[i] += ('\t' + grammeme + '\t')
                if rule.context_type == 'previous word':
                    if word_2.decode('utf-8') == rule.context:
                        tokens[i] = id_1 + '\t' + word_1
                        for grammeme in gr_list[:]:
                            if rule.tag not in grammeme.split(' ')[2:]:
                                gr_list.remove(grammeme)
                        for grammeme in gr_list:
                            tokens[i] += ('\t' + grammeme + '\t')
                if rule.context_type == 'next tag':
                    if tag == rule.context:
                        tokens[i] = id_1 + '\t' + word_1
                        for grammeme in gr_list[:]:
                            if rule.tag not in grammeme.split(' ')[2:]:
                                gr_list.remove(grammeme)
                        for grammeme in gr_list:
                            tokens[i] += ('\t' + grammeme + '\t')
                if rule.context_type == 'next word':
                    if word.decode('utf-8') == rule.context:
                        tokens[i] = id_1 + '\t' + word_1
                        for grammeme in gr_list[:]:
                            if rule.tag not in grammeme.split(' ')[2:]:
                                gr_list.remove(grammeme)
                        for grammeme in gr_list:
                            tokens[i] += ('\t' + grammeme + '\t')
            tag_2, tag_1, word_2, word_1, id_1 = tag_1, tag, word_1, word, id
            i += 1
        applied.write('\n'.join(tokens) + '\n')
    return applied.getvalue()


def get_unamb_tags(entries):
    context = ('w-1', 't-1', 'w+1', 't+1')
    for key in entries:
        entry = entries[key]
        for cont_type in context:
            first_tag = entry.keys()[0]
            chosen_tag = first_tag
            chosen_score = 0
            try:
                chosen_cont = entry[entry.keys()[0]][cont_type].keys()[0]
            except:
                pass
            for tag in entry.keys():
                for cont in entry[tag][cont_type].keys():
                    score = entry[tag][cont_type][cont]
                    if score > chosen_score:
                        chosen_score = score
                        chosen_tag = tag
                        chosen_cont = cont.decode('utf-8')
        if chosen_score > 0:
                yield (key, chosen_tag, cont_type, chosen_cont)


def scoring_function(entries, best_rules):
    best_score = 0
    new_rule = []
    rules_scores = {}
    context = ('w-1', 't-1', 'w+1', 't+1')
    for entry in entries:
        value = entries[entry]
        if len(entry) > 4:
            amb_tag = entry
            amb_tag = amb_tag
            tags = set(amb_tag.split('_'))
            if len(tags) > 1:
                result_scores = {}
                for tag in tags:
                    for c in context:
                        freqs = [0, 0, 0]
                        for amb_context in value[c]:
                            loc_max = -sys.maxint
                            try:
                                amb_context.decode('utf-8')
                                for unamb_tag in tags:
                                    if unamb_tag != tag:
                                        try:
                                            loc_context = entries[unamb_tag][c]
                                            try:
                                                if tag in entries.keys() and \
                                                loc_context[amb_context] > 3:
                                                    s = float(entries[tag]['freq']) / float(entries[unamb_tag]['freq']) * float(loc_context[amb_context])
                                                    if s > loc_max:
                                                        loc_max = s
                                                    #freqs = [entries[tag]['freq'], entries[unamb_tag]['freq'], loc_context[amb_context]]
                                            except:
                                                pass
                                        except:
                                            pass
                            except:
                                pass
                            try:
                                score = entries[tag][c][amb_context] - loc_max
                                result_scores[tag][c][amb_context] = score
                                if score > best_score \
                                and [amb_tag, tag, c, amb_context] not in best_rules:
                                    best_score = score
                                    new_rule = [amb_tag, tag, c, amb_context]
                            except:
                                try:
                                    score = entries[tag][c][amb_context] - loc_max
                                    #result_scores[tag][c] = {amb_context: [score] + freqs}
                                    result_scores[tag][c] = {amb_context: score}
                                    if score > best_score \
                                    and [amb_tag, tag, c, amb_context] not in best_rules:
                                        best_score = score
                                        new_rule = [amb_tag, tag, c, amb_context]
                                except:
                                    try:
                                        score = entries[tag][c][amb_context] - loc_max
                                        result_scores[tag] = {c: {amb_context: score}}
                                        if score > best_score \
                                        and [amb_tag, tag, c, amb_context] not in best_rules:
                                            best_score = score
                                            new_rule = [amb_tag, tag, c, amb_context]
                                    except:
                                        pass
                rules_scores[amb_tag] = result_scores
    return rules_scores, new_rule, best_score

if __name__ == '__main__':
    pass
