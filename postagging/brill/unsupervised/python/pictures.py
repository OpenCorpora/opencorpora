# coding: utf-8

from matplotlib import pyplot
import sys

from utils import numb_amb_corpus as n
from utils import read_corpus
import os


def tokens_rules(tokens, rules):
    pyplot.plot(tokens, rules, 'ro')
    pyplot.show()


def tokens(files):
    def t(f):
        f = read_corpus(f)
        return n(f)[0]
    return [t(open(f, 'r').read()) for f in files]


if __name__ == '__main__':
    tokens = os.listdir(sys.argv[1])
    rules = os.listdir(sys.argv[2])
    t = []
    r = []
    for f1, f2 in zip(tokens, rules):
        f1 = 'tokens\%s' % f1
        f2 = 'rules\%s' % f2
        t += [int(x.lstrip().split()[0]) for x in open(f1, 'r').read().rstrip().split('\n')]
        r += [int(x.lstrip().split()[0]) for x in open(f2, 'r').read().rstrip().split('\n')]
    tokens_rules(t, r)
