#coding: utf-8

import sys
import os
from time import clock
from pprint import pprint
from itertools import combinations_with_replacement

from utils import read_corpus, write_corpus, Rule
from rules_stat import apply_rule
from collections import OrderedDict


def precision(atagged, mtagged):
    mistagged = 0
    pairs = zip(sorted(atagged), sorted(mtagged))
    for p in pairs:
        tokens = zip(p[0], p[1])
        for t in tokens:
            if t[0].getPOStags() != t[1].getPOStags():
                print '\t'.join((t[0].display(), t[1].display())).encode('utf-8')
                mistagged += 1
    return mistagged


def rank_init_list(rlist):
    ranked = dict(zip(rlist[0], range(1, (len(rlist[0]) + 1))))
    return ranked


def rank_rules(rules, ranked):
    res = {}
    i = 1
    for r in rules[0]:
        try:
            res[i] = ranked[r]
            i += 1
        except:
            i += 1
    return res


def spearman(rlist, clist):
    init_ranks = rank_init_list(rlist)
    s = 0.0
    n = min(len(rlist), len(clist))
    c_ranks = rank_rules(clist, init_ranks)
    for i in range(1, n + 1):
        try:
            s += float((i - c_ranks[i]) ** 2)
        except:
            pass
    return 1.0 - 6.0 * s / (float(n) * (n ** 2 - 1.0))


if __name__ == '__main__':
    '''r = open('/data/rubash/brill/1/rules.txt', 'r')
    s = clock()
    inc = open('annot.opcorpora.no_ambig_but_ambig.tab', 'r').read()
    print clock() - s
    s = clock()
    for line in r.read().split('\n')[:-1:2]:
        line = line.split()
        if len(line) > 3:
            rule = Rule(line[3], line[5], ' '.join(line[7:9]), line[10])
            inc = apply_rule(rule, inc[:])
            with open('my_annot.opcorpora.no_ambig.tab', 'w') as out:
                out.write(inc)
                out.flush()
                os.fsync(out)
        else:
            break
    outc = read_corpus(open('my_annot.opcorpora.no_ambig.tab', 'r').read())
    print clock() - s
    inc = read_corpus(sys.stdin.read())
    print precision(inc, outc)'''
    args = sys.argv[1:]
    #TODO: не указывать все пути
    paths = OrderedDict()
    for arg in args:
        paths[arg] = (open(arg, 'r').read().rstrip().split('\n')[::2])
    for pair in combinations_with_replacement(paths.keys(), 2):
        if len(set(pair)) > 1:
            print pair
            print spearman(*(paths[k] for k in pair))
