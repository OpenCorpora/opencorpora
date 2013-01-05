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
    ranked = dict(zip(rlist, range(1, (len(rlist) + 1))))
    return ranked


def rank_rules(rules, ranked):
    res = {}
    i = 1
    for r in rules:
        try:
            res[i] = ranked[r]
            i += 1
        except:
            res[i] = len(ranked) + 1
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
    args = sys.argv[1:]
    #TODO: не указывать все пути
    paths = OrderedDict()
    for arg in args:
        paths[arg] = (open(arg, 'r').read().rstrip().split('\n')[1::2])
    for pair in combinations_with_replacement(paths.keys(), 2):
        if len(set(pair)) > 1:
            print pair
            print spearman(*(paths[k] for k in pair))
