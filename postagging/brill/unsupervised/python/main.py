#coding: utf-8

import sys
import os
from utils import get_list_words_pos, Rule, numb_amb_tokens, after_iter
from rules_stat import scoring_function, apply_rule

"""2. начать считать кол-во однозначных токенов и кол-во разборов на одно слово на каждой итерации"""
"""3. объединить правила с числами (лучше всего сделать опцию: объединять или нет, т.к. 
через некоторое время, когда будем снимать sing / plur, захотим отключить это)"""
#4. начать считать точность и что-то там вместо полноты


if __name__ == '__main__':
    out = open('rules.txt', 'w')
    input_corpus = sys.stdin.read()
    iter_c = 0
    best_rules = []
    best_score = 0
    while True:
        context_freq = get_list_words_pos(input_corpus, 0, 0,
                                          counter=numb_amb_tokens)
        #print after_iter()
        with open('iter_c%s.txt' % iter_c, 'w') as output:
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
        out.write(rule.display() + '\n')
        out.flush()
        os.fsync(out)
        with open('iter_c%s_scores.txt' % iter_c, 'w') as output:
            for amb_tag in scores.keys():
                for tag in scores[amb_tag].keys():
                    for context in scores[amb_tag][tag].keys():
                        for c_variant in scores[amb_tag][tag][context].keys():
                            output.write(str(scores[amb_tag][tag][context][c_variant][0]) + '\t' + str(amb_tag) + '\t' + tag + \
                                         '\t' + context + '\t' + \
                                         c_variant + '\t' + str(scores[amb_tag][tag][context][c_variant][1:3]) + '\n')
        new_c = apply_rule(rule, input_corpus[:])
        with open('iter_c%s_corpus.txt' % iter_c, 'w') as output:
            output.write(new_c)
        input_corpus = new_c
        iter_c += 1
        if best_score < 0:
            out.close()
            break
