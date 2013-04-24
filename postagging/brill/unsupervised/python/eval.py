#coding: utf-8

import sys
import os
from time import clock
from pprint import pprint
from itertools import combinations_with_replacement

from utils import read_corpus, write_corpus, Rule, numb_amb_corpus
from collections import OrderedDict


def precision(atagged, mtagged, init=None):
    tagged = 0.0
    mistagged = 0
    mistakes = {}
    #pairs = zip(sorted(atagged), sorted(mtagged))
    #pairs = zip(atagged, mtagged)
    #for p in pairs:
    tokens = zip(atagged, mtagged)
    for t in tokens:
        if u'sent' in t[0] or u'SENT' in t[0]:
            continue
        tagged += 1
        try:
            if t[0].getPOStags() != t[1].getPOStags():
                #print t[0].display(), t[1].display()
                print t[0].getPOStags(), '|', t[1].getPOStags()
                mistagged += 1
        except:
            print 1
            pass
    return mistagged, tagged

def get_rules(text):
    to_return = []
    for line in text:
        line = line.partition('#')[0]
        if line not in to_return:
            to_return.append(line)
    return to_return


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
    return round(1.0 - 6.0 * s / (float(n) * (n ** 2 - 1.0)), 3)


if __name__ == '__main__':
    x = []
    for i in sys.argv[1:]:
        mt = 0
        t = 0
        mist = 0
        m = []
        res = list(read_corpus(open(i, 'r').read().split('\n')))
        #init = read_corpus(open(sys.argv[2], 'r').read())
        ref = list(read_corpus(sys.stdin))
        n = numb_amb_corpus(ref)[0]
        '''args = sys.argv[1:]
        paths = OrderedDict()
        for arg in args:
            paths[arg] = get_rules(open(arg, 'r').read().rstrip().split('\n'))
        for pair in combinations_with_replacement(paths.keys(), 2):
            if len(set(pair)) > 1:
                print spearman(*(paths[k] for k in pair))'''
        '''for s1, s2 in zip(res, ref):
            pr = precision(s1, s2)
            mt += pr[0]
            t += pr[1]'''
        pr = precision(res, ref)
        mt = pr[0]
        t = pr[1]
        print mt, t, round((t - mt) / t, 4) * 100
