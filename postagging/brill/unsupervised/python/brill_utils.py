#coding: utf-8

import sys
#import re
from StringIO import StringIO
from time import clock


def split_into_sent(text):
    return text.split('</sent>')


def get_pos_tags(line):
    line = line.split('\t')
    grammar = line[2:]
    pos_tags = []
    pos_tags_str = StringIO()
    for item in grammar:
        pos = item.split(' ')[0]
        if pos not in pos_tags:
            pos_tags.append(pos)
    pos_tags.sort()
    for tag in pos_tags:
            pos_tags_str.write(tag + '_')
    return pos_tags_str.getvalue().lstrip('_').rstrip('_')


def process_table(sentence):
    to_return_list = []
    for line in sentence.split('\n'):
        line.decode('utf-8')
        if 'sent' not in line:
            pos_tags_str = get_pos_tags(line)
            try:
                to_return_list.append((line[1], pos_tags_str))
            except:
                pass
        else:
            pass
    return to_return_list


def get_list_words_pos(corpus):
    result_dict = {}
    for sent in split_into_sent(corpus):
        tokens = process_table(sent)
        tokens.insert(0, 'sent')
        tokens.append('/sent')
        word_2, tag_2 = 'sent', 'sent'
        word_1, tag_1 = tokens[1][0], tokens[1][1]
        for token in tokens[1:-1]:
            tag = token[1]
            word = token[0]
            if tag_1 in result_dict.keys():
                tag_entry = result_dict[tag_1]
                if tag_2 in tag_entry['t-1'].keys():
                    tag_entry['t-1'][tag_2] += 1
                else:
                    tag_entry['t-1'][tag_2] = 1
                if word_2 in tag_entry['w-1'].keys():
                    tag_entry['w-1'][word_2] += 1
                else:
                    tag_entry['w-1'][word_2] = 1
                if tag in tag_entry['t+1'].keys():
                    tag_entry['t+1'][tag] += 1
                else:
                    tag_entry['t+1'][tag] = 1
                if word in tag_entry['w+1'].keys():
                    tag_entry['w+1'][word] += 1
                else:
                    tag_entry['w+1'][word] = 1
                if 'freq' in tag_entry.keys():
                    tag_entry['freq'] += 1
                else:
                    tag_entry['freq'] = 1
            else:
                result_dict[tag_1] = dict(zip(('t-1', 'w-1', 't+1', 'w+1', 'freq'), \
                                              ({tag_2: 1}, {word_2: 1}, {tag: 1}, {word: 1}, 1)))
            tag_2, tag_1, word_2, word_1 = tag_1, tag, word_1, word
    return result_dict


def scoring_function(entries):
    rules_scores = {}
    context = ('w-1', 't-1', 'w+1', 't+1')
    for entry in entries:
        value = entries[entry]
        if len(entry) > 4:
            amb_tag = entry
            amb_tag = amb_tag.rstrip('_')
            tags = set(amb_tag.split('_'))
            if len(tags) > 1:
                result_scores = {}
                for tag in tags:
                    for c in context:
                        for amb_context in value[c]:
                            amb_context.decode('utf-8')
                            local_scores = {0: 0}
                            for unamb_tag in tags:
                                if unamb_tag != tag:
                                    if unamb_tag in entries.keys():
                                        loc_context = entries[unamb_tag][c]
                                        if amb_context in loc_context.keys():
                                            if tag in entries.keys() and \
                                            loc_context[amb_context] > 3:
                                                local_scores[unamb_tag] = float(entries[tag]['freq']) / \
                                                float(entries[unamb_tag]['freq']) * float(loc_context[amb_context])
                            try:
                                result_scores[tag][c][amb_context] = \
                                entries[tag][c][amb_context] - max(local_scores.values())
                            except:
                                try:
                                    result_scores[tag][c] = {amb_context: entries[tag][c][amb_context] - max(local_scores.values())}
                                except:
                                    try:
                                        result_scores[tag] = {c: {amb_context: entries[tag][c][amb_context] - max(local_scores.values())}}
                                    except:
                                        #result_scores[tag] = {c: 0}
                                        pass
                rules_scores[amb_tag] = result_scores
    return rules_scores

if __name__ == '__main__':
    start = clock()
    context_freq = get_list_words_pos(sys.stdin.read())
    finish = clock()
    print finish - start
    with open('full_output.txt', 'w') as output:
        #print context_freq
        for amb_tag in context_freq.keys():
            for context in context_freq[amb_tag].keys():
                if context is not 'freq':
                    for c_variant in context_freq[amb_tag][context].keys():
                        #print c_variant.decode('utf-8')
                        output.write(str(amb_tag).rstrip('_') + '\t' + \
                                     context + '\t' + c_variant + \
                                    '\t' + str(context_freq[amb_tag][context][c_variant]) + '\n')
                else:
                    output.write(str(amb_tag).rstrip('_') + '\t' + 'freq' + \
                                 '\t' + str(context_freq[amb_tag][context]) + '\n')
    print(clock() - finish)
    finish = clock()
    scores = scoring_function(context_freq)
    print(clock() - finish)
    finish = clock()
    with open('full_output_scores.txt', 'w') as output:
        for amb_tag in scores.keys():
            for tag in scores[amb_tag].keys():
                for context in scores[amb_tag][tag].keys():
                    for c_variant in scores[amb_tag][tag][context].keys():
                        output.write(str(scores[amb_tag][tag][context][c_variant]) + '\t' + str(amb_tag) + '\t' + tag + \
                                     '\t' + context + '\t' + \
                                     c_variant + '\n')
    print(clock() - finish)
