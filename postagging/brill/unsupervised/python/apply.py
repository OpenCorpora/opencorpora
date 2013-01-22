# coding: utf-8

import sys
import re

from utils import read_corpus, write_corpus, Rule, Sentence, Corpus
from rules_stat import apply
from StringIO import StringIO


if __name__ == '__main__':
    rules = []
    for line in open(sys.argv[1], 'r').read().rstrip('\n').split('\n'):
        p = re.compile(u'.+(?= ->)')
        ambtag = p.findall(line)[0]
        p = re.compile(u'(?<=-> )(\w+)', re.UNICODE)
        tag = p.findall(line)[0]
        p = re.compile(u'(?<=\| )(.+)(?=:)')
        pos = p.findall(line)[0]
        p = re.compile(u'(?<=:)(\w+)')
        type = p.findall(line)[0]
        p = re.compile(u'(?<==)(\w+|,|.|:|;)', re.UNICODE)
        c = p.findall(line)[0]
        if pos == '1':
            t = '+'.join((type[0], pos))
        else:
            t = type[0] + pos
        r = Rule('_'.join(ambtag.split()), tag, t, c)
        rules.append(r)
    flag = True
    while flag:
        s = StringIO()
        while True:
            l = sys.stdin.readline()
            if 'sent' in l:
                break
            elif l == '':
                flag = False
                break
            s.write(l)
        if len(s.getvalue()) > 0:
            c = read_corpus(s.getvalue())
            for r in rules:
                apply(r, c)
            write_corpus(c, sys.stdout)
            s = []
