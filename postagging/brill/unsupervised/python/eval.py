#coding: utf-8

import sys
import os

from utils import read_corpus, write_corpus, Rule
from rules_stat import apply_rule


def precision(atagged, mtagged):
    mistagged = 0
    pairs = zip(atagged, mtagged)
    for p in pairs:
        tokens = zip(p[0], p[1])
        for t in tokens:
            if t[0].getPOStags() != t[1].getPOStags():
                mistagged += 1
    return mistagged


if __name__ == '__main__':
    r = open('./1/rules.txt', 'r')
    inc = sys.stdin.read()
    for line in r.read().split('\n')[:-1:2]:
        line = line.split()
        if len(line) > 3:
            #print line
            rule = Rule(line[3], line[5], ' '.join(line[7:9]), line[10])
            #print rule.display()
            inc = apply_rule(rule, inc[:])
            with open('my_annot.opcorpora.no_ambig.tab', 'w') as out:
                out.write(inc)
                out.flush()
                os.fsync(out)
        else:
            break