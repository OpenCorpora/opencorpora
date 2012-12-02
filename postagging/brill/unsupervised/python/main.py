#coding: utf-8

import sys
import os
from utils import get_list_words_pos, Rule, numb_amb_corpus, get_list_amb
from rules_stat import scoring_function, apply_rule


if __name__ == '__main__':
    args = sys.argv[1:]
    apply_all = False
    if args != []:
        if args[0] == '-r':
            apply_all = True
    out = open('rules.txt', 'r')
    input_corpus = sys.stdin.read()
    i = 0
    best_rules = []
    best_score = 0
    print numb_amb_corpus(input_corpus)
    while True:
        get_list_amb(input_corpus)
        context_freq = get_list_words_pos(input_corpus)
        with open('iter%s.txt' % i, 'w') as output:
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
        with open('iter%s_scores.txt' % i, 'w') as output:
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
        with open('icorpus.txt', 'w') as output:
            output.write(input_corpus)
        out.write(str(numb_amb_corpus(input_corpus)) + '\n')
        out.flush()
        os.fsync(out)
        i += 1
        if best_score < 0:
                out.close()
                break
