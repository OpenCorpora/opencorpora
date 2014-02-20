#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import re
import sys
import argparse
    

parser = argparse.ArgumentParser(argument_default=argparse.SUPPRESS, description='Find paragraph distance between heads.')
parser.add_argument('infile1', nargs='?', type = argparse.FileType('r'), help = 'file with a list of pairs')
parser.add_argument('infile2', nargs='?', type = argparse.FileType('r'), help = 'file with a list of groups')
parser.add_argument('infile3', nargs='?', type = argparse.FileType('r'), help = 'file with a list of tokens')
args = parser.parse_args()


pattern = re.compile(r'(\d*)_(\d*)')            # шаблон число_число
first_num = []
second_num = []
tops = {}
token_num = []
positions = {'/p':[]}
d = {}


for line in args.infile1:                       # списки первых и вторых цифр из пар
    match = pattern.search(line)
    if not line.rstrip('\r\n'):
        continue 
    first_num.append(match.group(1))            
    second_num.append(match.group(2))
          
       
for s in args.infile2:                          # словарь вида ИГ - вершина
    s = s.strip().split('\t')
    if s[2] == 'ALL':                           # выбор вершины с наибольшим номером в случае с "ALL"
        tops[s[0]] = max(s[1].split(','))
    else:
        tops[s[0]] = s[2]


for r in args.infile3:                         # список тэгов и номеров токенов
    r = r.strip().split('\t')
    token_num.append(r[0])

for i in range(len(token_num)):
    if token_num[i] == '/p':
        positions['/p'].append(i)
    else:
        positions[token_num[i]] = i
 

print 'par_dist' 
for i in range(len(first_num)):                
    first_top = tops[first_num[i]]              # определение вершины ИГ
    second_top = tops[second_num[i]]            
    f_p = positions[str(first_top)]
    s_p = positions[str(second_top)]

    count = 0

    for j in range(len(positions['/p'])):
        if positions['/p'][j] > s_p :
            break
        if positions['/p'][j] > f_p :
            count += 1
    sys.stdout.write(first_num[i] + '_' + second_num[i] + "\t" + str(count) + '\n')

