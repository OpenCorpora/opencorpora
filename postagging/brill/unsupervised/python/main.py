# coding: utf-8


from rules_stat import scores, apply_rule
import sys
from time import clock
from utils import context_stats, numb_amb_corpus, read_corpus, \
write_corpus

TYPES = {0: 'tag', 1: 'word'}

if __name__ == '__main__':
    s = clock()
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
            if arg == '-w':
                write = True
            i += 1
    out = sys.stdout
    i = 0
    best_rules = []
    best_score = 0
    inc = list(read_corpus(sys.stdin))
    while True:
        context_freq = context_stats(inc)
        scores_rule = scores(context_freq, best_rules)
        #ss = scores_rule[0]
        best_rule = scores_rule[0]
        for r in best_rule.keys():
            best_rules.append(r)
        best_score = scores_rule[1]
        applied = scores_rule[2]
        if best_score <= 0:
            output = open('icorpus.txt', 'w')
            write_corpus(inc, output)
            output.close()
            out.close()
            break
        best_rule = reversed(sorted(best_rule.items(), key=lambda t: t[1]))
        for r, a in best_rule:
            inc = list(apply_rule(r, inc))
            try:
                out.write(r.display())
            except:
                out.write(r.display().encode('utf-8'))
            out.write('score=%s applied=%s\n' % (str(best_score), a))
        if apply_all:
            for r in best_rules[:-1]:
                inc = list(apply_rule(r, inc))
        i += 1
