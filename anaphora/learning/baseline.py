# coding: utf-8

# python baseline.py test.pairs ref.pairs output

import sys
from itertools import groupby

import random


def baseline_random(X_test, y_test, output=sys.stdout):
    for anph, antc in groupby(X_test, key=lambda x: x.split('__')[1]):
        antc = list(antc)
        k = random.randint(0, len(antc) - 1)
        for i, pair in enumerate(antc):
            if i == k:
                print >> output, pair + '\t1'
                continue
            print >> output, pair + '\t0'


def baseline_near(X_test, y_test):
    for anph, antc in groupby(X_test, key=lambda x: x.split('__')[1]):
        k = len(antc) - 1
        for i, pair in enumerate(antc):
            if i == k:
                print pair + '\t1'
                continue
            print pair + '\t0'


def baseline_random_probs(X_test, y_test):
    pass

if __name__ == '__main__':
    test = []
    ref = []

    with open(sys.argv[1]) as t:
        for line in t:
            test.append(line.rstrip('\r\n'))
    with open(sys.argv[2]) as r:
        for line in r:
            ref.append(line.rstrip('\r\n'))
    with open(sys.argv[3], 'w') as pred:
        baseline_random(test, ref, output=pred)
