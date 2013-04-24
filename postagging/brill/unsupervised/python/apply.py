# coding: utf-8

import sys
import re

from utils import read_corpus, write_corpus, Rule
from rules_stat import apply_rule


if __name__ == '__main__':
    TYPES = {'tag': 0, 'word': 1}
    rules = []
    for line in open(sys.argv[1], 'r').read().rstrip('\n').split('\n'):
        line = line.decode('utf-8')
        p = re.compile(u'.+(?= ->)')
        ambtag = p.findall(line)[0]
        p = re.compile(u'(?<=-> )(\w+)', re.UNICODE)
        tag = p.findall(line)[0]
        p = re.compile(u'(-?\d+)(?=:)')
        pos = p.findall(line)
        pos = tuple(int(x) for x in pos)
        if len(pos) < 2:
            pos = pos[0]
        p = re.compile(u'(?<=:)(\w+)')
        type = p.findall(line)[0]
        p = re.compile(u'(?u)(?<==)(\w+|,|.|:|;)[( #)&]')
        c = p.findall(line)
        if len(c) > 1:
            c = tuple(c)
        else:
            c = c[0]
        #print ambtag, tag, pos, type, c
        t = TYPES[type]
        r = Rule(ambtag, tag, (pos, c), t)
        #print ambtag
        rules.append(r)
    inc = read_corpus(sys.stdin)
    for r in rules:
        #print >> sys.stderr, r.display()
        inc = list(apply_rule(r, inc))
    write_corpus(inc)
