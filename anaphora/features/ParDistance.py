#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import re
    
pattern = re.compile(r'(\d*)_(\d*)')                # шаблон число_число
first_num = []
second_num = []
tops = {}
token_num = []

with open('ana_test.pairs') as fd:  # списки первых и вторых цифр из пар
    for line in fd: 
        match = pattern.search(line)
        first_num.append(match.group(1))            
        second_num.append(match.group(2))           
       
with open('ana_test.groups') as d:  # словарь вида ИГ - вершина
    for s in d:
        s = s.strip().split('\t')
        if s[2] == 'ALL':                            # выбор вершины с наибольшим номером в случае с "ALL"
            tops[s[0]] = max(s[1].split(','))
        else:
            tops[s[0]] = s[2]

with open('ana_test.tab') as doc:   # список тэгов и номеров токенов
    for r in doc:
        r = r.strip().split('\t')
        token_num.append(r[0])
        
results = open('ParDistance.txt', 'w')
for i in range(len(first_num)):                      # от нуля до количества пар
    first_top = tops[first_num[i]]                   # определение вершины ИГ
    second_top = tops[second_num[i]]              
    start = token_num.index(first_top)               # индекс номеров вершин в списке токенов 
    end = token_num.index(second_top)
    par = 0
    for l in range(start, end):
        if '/p' in token_num[l]:
            par += 1
    results.write(first_num[i] + '_' + second_num[i] + "  " + str(par) + '\n')
results.close()
