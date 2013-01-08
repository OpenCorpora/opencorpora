# coding: utf-8

import sys
import os
from utils import context_stats, Rule, numb_amb_corpus, get_list_amb, read_corpus, \
write_corpus
from rules_stat import scoring_function, apply_rule, scores, apply


if __name__ == '__main__':
    args = sys.argv[1:]
    apply_all = False
    fullcorp = False
    continuous = False
    path = '.'
    write = False
    n = 0
    if args != []:
        i = 0
        for arg in args:
            if arg == '-r':
                apply_all = True
            if arg == '-f':
                fullcorp = True
            if arg == '-n':
                n = args[i + 1]
            if arg == '-c':
                continuous = True
            if arg == '-p':
                path = args[i + 1]
            if arg == '-w':
                write = True
            i += 1
    if fullcorp:
        out = open('%s/full/rules.txt' % path, 'w')
    if continuous:
        out = open('%s/cont/%s/rules.txt' % (path, n), 'w')
    else:
        out = open('%s/rand/%s/rules.txt' % (path, n), 'w')
    i = 0
    best_rules = []
    best_score = 0
    inc = read_corpus(sys.stdin.read())
    amb = numb_amb_corpus(inc)
    print amb
    while True:
        context_freq = context_stats(inc)
        if fullcorp:
            f = '%s/full/iter%s.txt' % (path, i)
        if continuous:
            f = '%s/cont/%s/iter%s.txt' % (path, n, i)
        else:
            f = '%s/rand/%s/iter%s.txt' % (path, n, i)
        if write:
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
        scores_rule = scores(context_freq, best_rules)
        ss = scores_rule[0]
        best_rule = scores_rule[1]
        best_rules.append(best_rule)
        best_score = scores_rule[2]
        applied = scores_rule[3]
        if best_score < 0 or best_rule == []:
            output = open('%s/rand/%s/icorpus.txt', 'w')
            write_corpus(inc, output)
            output.close()
            out.close()
            break
        rule = Rule(*best_rule)
        if write:
            if fullcorp:
                f = '%s/full/iter%s.scores' % (path, i)
            if continuous:
                f = '%s/cont/%s/iter%s.scores' % (path, n, i)
            else:
                f = '%s/rand/%s/iter%s.scores' % (path, n, i)
            with open(f, 'w') as output:
                for amb_tag in ss.keys():
                    for tag in ss[amb_tag].keys():
                        for context in ss[amb_tag][tag].keys():
                            for c_variant in ss[amb_tag][tag][context].keys():
                                output.write('\t'.join((str(ss[amb_tag][tag][context][c_variant]), amb_tag, tag, context, c_variant)).encode('utf-8') + '\n')
        apply(rule, inc)
        amb = numb_amb_corpus(inc)
        try:
            out.write(rule.display())
        except:
            out.write(rule.display().encode('utf-8'))
        if apply_all:
            for rule in best_rules[:-1]:
                r = Rule(*rule)
                apply(r, inc)
        if fullcorp:
            f = '%s/full/icorpus.txt' % path
        if continuous:
            f = '%s/cont/%s/icorpus.txt' % (path, n)
        else:
            f = '%s/rand/%s/icorpus.txt' % (path, n)
        if write:
            output = open(f, 'w')
            write_corpus(inc, output)
            output.close()
        out.write('score=%s applied=%s\n' % (str(best_score), applied))
        print rule.display()
        #out.flush()
        #os.fsync(out)
        #print best_score
        i += 1
