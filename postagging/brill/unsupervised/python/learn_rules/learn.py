#!/usr/bin/env python2.7
# coding: utf-8
import json


if __name__ == '__main__':

    from argparse import ArgumentParser
    from learn_funcs import context_stats, scores
    #from pprint import pprint
    from shutil import copyfile
    from utils import apply_rule, read_corpus, write_corpus

    p = ArgumentParser()
    p.add_argument('-r', default=False)
    p.add_argument('-w', default=False)
    p.add_argument('-p', default=False)
    p.add_argument('-f', default=None)
    p.add_argument('corpus')
    args = p.parse_args()

    #name = os.path.split(args.corpus)[1]
    name = args.corpus
    path = '.'
    write = False
    n = 0
    #out = sys.stdout
    copyfile(name, '%s.orig' % name)
    out = open('%s.rules' % name, 'w')
    i = 0
    best_rules = []
    best_score = 0

    orig = open(args.corpus, 'r')
    inc = list(read_corpus(orig))
    orig.close()

    while True:
        context_freq = context_stats(inc, f=args.f)
        scores_rule = scores(context_freq, best_rules, f=args.f)
        #ss = scores_rule[0]
        best_rule = scores_rule[0]

        for r in best_rule.keys():
            best_rules.append(r)
        best_score = scores_rule[1]
        applied = scores_rule[2]

        if best_score <= 0:
            output = open('%s.final' % name, 'w')
            write_corpus(inc, output)
            output.close()
            out.close()
            break
        best_rule = reversed(sorted(best_rule.items(), key=lambda t: t[1]))

        for r, a in best_rule:
            inc = list(apply_rule(r, inc, f=args.f))
            try:
                out.write(r.display())
            except:
                out.write(r.display().encode('utf-8'))
            out.write('score=%s applied=%s\n' % (str(best_score), a))

        if args.p:
            for r in best_rules[:-1]:
                inc = list(apply_rule(r, inc, f=args.f))
        i += 1
