#coding: utf-8

import sys
import os
from utils import get_list_words_pos, Rule, numb_amb_corpus
from rules_stat import scoring_function, apply_rule

"""2. начать считать кол-во однозначных токенов и кол-во разборов на одно слово на каждой итерации"""
"""3. объединить правила с числами (лучше всего сделать опцию: объединять или нет, т.к. 
через некоторое время, когда будем снимать sing / plur, захотим отключить это)"""
"""4. начать считать точность"""
"""и что-то там вместо полноты"""
# TODO: применять после каждого правила все предыдущие


if __name__ == '__main__':
    args = sys.argv[1:]
    apply_all = False
    if args[0] == '-r':
        apply_all = True
    #out = open('rules.txt', 'w')
    #out = open('rulesx.txt', 'r').read()
    input_corpus = sys.stdin.read()
    iter_c = 0
    best_rules = []
    best_score = 0
    print numb_amb_corpus(input_corpus)
    '''for line in out.rstrip('\n').split('\n')[::2]:
        rule = []
        line = line.split(' ')
        rule.append(line[3])
        rule.append(line[5])
        rule.append(' '.join(line[7:9]))
        rule.append(line[10])
        r = Rule(*rule)
        input_corpus = apply_rule(r, input_corpus[:])
        print r.display()
    out.close()
    with open('iterx_corpus.txt', 'w') as output:
        output.write(input_corpus)'''
    #input_corpus = open('iterx_corpus.txt', 'r').read()
    out = open('rulesx.txt', 'w')
    while True:
        context_freq = get_list_words_pos(input_corpus)
        #print after_iter()
        with open('iterx_%s.txt' % iter_c, 'w') as output:
            for amb_tag in context_freq.keys():
                for context in context_freq[amb_tag].keys():
                    if context is not 'freq':
                        try:
                            for c_variant in context_freq[amb_tag][context].keys():
                                output.write(str(amb_tag).rstrip('_') + '\t' + \
                                             context + '\t' + c_variant + \
                                            '\t' + str(context_freq[amb_tag][context][c_variant]) + '\n')
                        except:
                            pass
                    else:
                        output.write(str(amb_tag).rstrip('_') + '\t' + 'freq' + \
                                     '\t' + str(context_freq[amb_tag][context]) + '\n')
        scores_rule = scoring_function(context_freq, best_rules)
        scores = scores_rule[0]
        best_rule = scores_rule[1]
        best_rules.append(best_rule)
        best_score = scores_rule[2]
        rule = Rule(*best_rule)
        with open('iterx_%s_scores.txt' % iter_c, 'w') as output:
            for amb_tag in scores.keys():
                for tag in scores[amb_tag].keys():
                    for context in scores[amb_tag][tag].keys():
                        for c_variant in scores[amb_tag][tag][context].keys():
                            output.write(str(scores[amb_tag][tag][context][c_variant][0]) + '\t' + str(amb_tag) + '\t' + tag + \
                                         '\t' + context + '\t' + \
                                         c_variant + '\t' + str(scores[amb_tag][tag][context][c_variant][1:]) + '\n')
        input_corpus = apply_rule(rule, input_corpus[:])
        out.write(rule.display() + '\n')
        if apply_all:
            for rule in best_rules[:-1]:
                r = Rule(*rule)
                input_corpus = apply_rule(r, input_corpus[:])
        with open('iterx_corpus.txt', 'w') as output:
            output.write(input_corpus)
        out.write(str(numb_amb_corpus(input_corpus)) + '\n')
        out.flush()
        os.fsync(out)
        iter_c += 1
        if best_score < 0:
                out.close()
                break
