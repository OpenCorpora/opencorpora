#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys
import argparse
import itertools

parser = argparse.ArgumentParser(argument_default=argparse.SUPPRESS, description='Make pairs "group id - pronoun id".')
parser.add_argument('infile1', nargs='?', type = argparse.FileType('r'), help = 'file with a list of groups')
parser.add_argument('infile2', nargs='?', type = argparse.FileType('r'), help = 'file with a list of pronouns')
args = parser.parse_args()


def getid(fullid):
    return int(fullid.split('_')[1])


def gettextid(fullid):
    return int(fullid.split('_')[0])

groups = {}
pron = {}


for s in args.infile1:                 # dict group_id : max token 
    if not s.rstrip('\r\n'):
        continue
    s = s.strip().split('\t')
    groups[s[0]] = s[1].split(',')[-1]


for line in args.infile2:              # dict pron_id : token number
    if not line.rstrip('\r\n'):
        continue
    line = line.strip().split('\t')
    pron[line[0]] = line[1]


group_keys = sorted(groups.keys())
pronoun_keys = sorted(pron.keys())


result = itertools.product(group_keys, pronoun_keys)
prev = 0
for i in result:
    g = getid(groups[i[0]])
    tg = gettextid(groups[i[0]])
    tp = gettextid(pron[i[1]])
    if tg == tp and g < getid(pron[i[1]]) \
        and g > prev:
        sys.stdout.write('{0}__{1}'.format(str(i[0]), str(i[1]) + '\n'))
        prev = getid(pron[i[1]])
    else:
        continue
