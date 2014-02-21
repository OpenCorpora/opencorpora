# coding: utf-8

# python scorer.py *.ref.pairs *.groups < *.pairs

import sys

heads = {}
with open(sys.argv[2]) as gfile:
    for line in gfile:
        line = line.rstrip('\n').split('\t')
        heads[line[0]] = line[2]

pairs = {}
with open(sys.argv[1]) as ref:
    for line in ref:
        antc, anph = line.rstrip('\n').split('\t')
        pairs[anph] = antc

corr = 0.0
all = 0
for line in sys.stdin:
    pair, cl = line.rstrip('\n').split('\t')
    if cl == '1':
        antc, anph = pair.split('_')
        if heads[antc] == heads[pairs[anph]]:
            corr += 1
        all += 1

print "Precision on class 1 is {:.2%}".format(corr / all)
