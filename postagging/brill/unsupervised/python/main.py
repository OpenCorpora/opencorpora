#coding: utf-8

import sys
import os
from utils import context_stats, Rule, numb_amb_corpus, get_list_amb, read_corpus
from rules_stat import scoring_function, apply_rule, scores


if __name__ == '__main__':
    args = sys.argv[1:]
    apply_all = False
    fullcorp = False
    continuous = False
    n = 0
    if args != []:
        if args[0] == '-r':
            apply_all = True
        if '-f' in args:
            fullcorp = True
        if '-n' in args:
            n = ' '.join(args).partition('-n')[2].lstrip(' ')
        if '-c' in args:
            continuous = True
    if fullcorp:
        out = open('/data/rubash/brill/full/rules.txt', 'w')
    if continuous:
        out = open('/data/rubash/brill/cont/%s/rules.txt' % n, 'w')
    else:
        out = open('/data/rubash/brill/rand/%s/rules.txt' % n, 'w')
    input_corpus = sys.stdin.read()
    i = 0
    best_rules = []
    best_score = 0
    print numb_amb_corpus(input_corpus)
    while True:
        context_freq = context_stats(read_corpus(input_corpus))
        if fullcorp:
            f = '/data/rubash/brill/full/iter%s.txt' % i
        if continuous:
            f = '/data/rubash/brill/cont/%s/iter%s.txt' % (n, i)
        else:
            f = '/data/rubash/brill/rand/%s/iter%s.txt' % (n, i)
        with open(f, 'w') as output:
            for amb_tag in context_freq.keys():
                for context in context_freq[amb_tag].keys():
                    if context != 'freq':
                        for c_variant in context_freq[amb_tag][context].keys():
                            try:
                                output.write('\t'.join((amb_tag, \
                                                        context, c_variant, \
                                                        str(context_freq[amb_tag][context][c_variant]))).encode('utf-8') + '\n')
                            except:
                                print amb_tag, context, c_variant, context_freq[amb_tag][context][c_variant]
                    else:
                        output.write('\t'.join((amb_tag, 'freq', str(context_freq[amb_tag][context]))) + '\n')
        scores_rule = scoring_function(context_freq, best_rules)
        ss = scores_rule[0]
        best_rule = scores_rule[1]
        best_rules.append(best_rule)
        best_score = scores_rule[2]
        rule = Rule(*best_rule)
        if best_score < 0:
                out.close()
                break
        if fullcorp:
            f = '/data/rubash/brill/full/iter%s.scores' % i
        if continuous:
            f = '/data/rubash/brill/cont/%s/iter%s.scores' % (n, i)
        else:
            f = '/data/rubash/brill/rand/%s/iter%s.scores' % (n, i)
        with open(f, 'w') as output:
            for amb_tag in ss.keys():
                for tag in ss[amb_tag].keys():
                    for context in ss[amb_tag][tag].keys():
                        for c_variant in ss[amb_tag][tag][context].keys():
                            output.write('\t'.join((str(ss[amb_tag][tag][context][c_variant]), amb_tag, tag, context, c_variant)).encode('utf-8') + '\n')
        input_corpus = apply_rule(rule, input_corpus[:])
        try:
            out.write(rule.display() + '\n')
        except:
            out.write(rule.display().encode('utf-8') + '\n')
        if apply_all:
            for rule in best_rules[:-1]:
                r = Rule(*rule)
                input_corpus = apply_rule(r, input_corpus[:])
        if fullcorp:
            f = '/data/rubash/brill/full/icorpus.txt'
        if continuous:
            f = '/data/rubash/brill/cont/%s/icorpus.txt' % n
        else:
            f = '/data/rubash/brill/rand/%s/icorpus.txt' % n
        with open(f, 'w') as output:
            output.write(input_corpus)
        out.write(str(numb_amb_corpus(input_corpus)) + '\n')
        out.flush()
        os.fsync(out)
        print best_score
        i += 1
