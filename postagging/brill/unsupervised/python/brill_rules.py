#coding: utf-8

import sys

from brill_utils import get_list_words_pos, scoring_function, \
split_into_sent, process_table, get_pos_tags


CONTEXT = ('w-1', 't-1', 'w+1', 't+1')


class Rule(object):

    def __init__(self, tagset, tag, context_type, context):
        self.tagset = tagset
        self.context = context
        self.tag = tag
        for item in zip(CONTEXT, ('previous word', 'previous tag',
                                   'next word', 'next tag')):
            if context_type == item[0]:
                self.context_type = item[1]

    def display(self):
        return 'Change tag from %s to %s if %s is %s' % (self.tagset, self.tag, self.context_type, self.context)


def apply_rule(rule, table):
    for sent in split_into_sent(table):
        sent = sent.lstrip('<sent>\n').rstrip('\n')
        tokens = sent.split('\n')
        if len(tokens) == 0:
            continue
        tokens.insert(0, 'sent')
        tokens.append('sent')
        word_2, tag_2 = 'sent', 'sent'
        id_1 = tokens[1].split('\t')[0]
        word_1, tag_1 = tokens[1].split('\t')[1], get_pos_tags(tokens[1])
        i = 1
        for token in tokens[2:-1]:
            tag = get_pos_tags(token)
            id = token.split('\t')[0]
            word = token.split('\t')[1]
            if tag_1 == rule.tagset:
                gr_list = tokens[i].split('\t')[2:]
                if rule.context_type == 't-1':
                    if tag_2 == rule.context:
			tokens[i] = id + '\t' + word
                        for grammeme in gr_list:
                            if rule.tag not in grammeme:
                                gr_list.remove(grammeme)
                        for grammeme in gr_list:
                            tokens[i] += ('\t' + grammeme + '\t')
                        tokens[i] += '\n'
                if rule.context_type == 'w-1':
                    if word_2 == rule.context:
                        tokens[i][1] = rule.tag
                if rule.context_type == 'next tag':
                    if tag == rule.context:
			tokens[i] = id_1 + '\t' + word_1
                        for grammeme in gr_list:
                            try:
                                if rule.tag not in grammeme:
                                    gr_list.remove(grammeme)
                            except:
                                print grammeme.decode('utf-8')
                                break
                        for grammeme in gr_list[:-1]:
                            tokens[i] += ('\t' + grammeme + '\t')
                        tokens[i] += '\n'
                if rule.context_type == 'w+1':
                    if word == rule.context:
                        tokens[i][1] = rule.tag
            tag_2, tag_1, word_2, word_1, id_1 = tag_1, tag, word_1, word, id
            i += 1
        yield tokens[1:-1]


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
                print entry
                print chosen_tag
                print cont_type
                chosen_cont = ''
            for tag in entry.keys():
                for cont in entry[tag][cont_type].keys():
                    score = entry[tag][cont_type][cont]
                    if score > chosen_score:
                        chosen_score = score
                        chosen_tag = tag
                        chosen_cont = cont.decode('utf-8')
        if chosen_score > 0:
                yield (key, chosen_tag, cont_type, chosen_cont)


if __name__ == '__main__':
    '''init_list = get_list_words_pos(sys.stdin)
    scores = scoring_function(init_list)
    with open('rules_list.txt', 'a') as f:
        for entry in get_unamb_tags(scores):
            r = Rule(*entry)
            f.write(r.display().encode('utf-8') + '\n')'''
    rule = Rule(u'ADJF_NOUN', u'NOUN', 't+1', u'VERB')
    print rule.display()
    with open('unamb_test.txt', 'w') as output:
        for sent in apply_rule(rule, sys.stdin.read()):
            output.write('<sent>\n')
            for item in sent:
                output.write(item + '\n')
            output.write('</sent>\n')
