# coding: utf-8

# python scorer.py *.ref.pairs *.groups < *.pairs

import sys

_groups = 'learning.groups'
_ref = 'learning.pairs'


def load_pred(p):
    for line in p:
        line = line.rstrip('\n').split('\t')
        yield line


def load_heads(h):
    heads = {}
    hlist = []
    with open(sys.argv[2]) as gfile:
        for line in gfile:
            line = line.rstrip('\n').split('\t')
            heads[line[0]] = line[2]
            hlist.append(line[2])
    return heads, hlist


def score(pred, groups=_groups, ref=_ref):
    heads, hl = load_heads(groups)
    pairs = load_pairs(ref)

    corr = 0.0
    all = 0
    for i, line in enumerate(pred):
        pair, cl = line
        if cl == '1':
            antc, anph = pair.split('__')
            if hl[i] == heads[pairs[anph]]:
                corr += 1
            all += 1
    return corr / all


def load_pairs(r):
    pairs = {}
    with open(r) as ref:
        for line in ref:
            antc, anph = line.rstrip('\n').split('\t')
            pairs[anph] = antc
    pairs = {}

if __name__ == '__main__':
    print "Precision on class 1 is {:.2%}".format(score(load_pred(sys.stdin),
                                                        groups=sys.argv[1], ref=sys.argv[2]))
