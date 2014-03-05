#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys
import argparse
import itertools
     
parser = argparse.ArgumentParser(argument_default=argparse.SUPPRESS, description='Make pairs "group id - pronoun id".')
parser.add_argument('infile1', nargs='?', type = argparse.FileType('r'), help = 'file with a list of groups')
parser.add_argument('infile2', nargs='?', type = argparse.FileType('r'), help = 'file with a list of pronouns')
args = parser.parse_args()
     
     
groups = {}
pron = {}
     
     
for s in args.infile1:                 # dict group_id : max token
    if not s.rstrip('\r\n'):
        continue                    
    s = s.strip().split('\t')
    groups[int(s[0])] = int(max(s[1].split(',')))
 
 
for line in args.infile2:              # dict pron_id : token number
    if not line.rstrip('\r\n'):
        continue
    line = line.strip().split('\t')
    pron[int(line[0])] = int(line[1])
 
 
group_keys = sorted(groups.keys())
pronoun_keys = sorted(pron.keys())
 
 
result = itertools.product(group_keys, pronoun_keys)
for i in result:
    if groups[i[0]] < pron[i[1]]:
        sys.stdout.write('{0}__{1}'.format(str(i[0]), str(i[1]) + '\n'))
    if groups[i[0]] > pron[i[1]]:
        continue

