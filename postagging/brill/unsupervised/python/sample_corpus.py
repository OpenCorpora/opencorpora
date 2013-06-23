# coding: utf-8

import sys
import random
from utils import write_corpus, numb_amb_corpus

def rand_sent(l, n, c):
    used = []
    for i in range(c):
        nums = set(range(l)) - set(used)
        sample = random.sample(nums, n)
        used += sample
        yield sample

def get_random_sentences(corpus, nums):
    c = []
    i = 0
    s = False
    for line in corpus:
        line = line.rstrip().decode('utf-8')
        if not line or 'sent' in line:
            #print
            i += 1
            s = True
            continue
        if i in nums:
            if s:
                print '/sent'
                print 'sent'
                s = False
            print line.rstrip().encode('utf-8')
    return c


def get_corpora(corpus, l, c, n):    
    randomnums = rand_sent(l, n, c)
    i = 0
    for nums in randomnums:
        #yield (i, get_random_sentences(corpus, randomnums))
        get_random_sentences(corpus, nums)



if __name__ == '__main__':
    args = sys.argv[1:]
    l = int(args[0])
    n = 1
    c = 1
    for i in range(len(args)):
        if args[i] == '-n':
            n = int(args[i + 1])
        if args[i] == '-c':
            c = int(args[i + 1])        
    get_corpora(sys.stdin, l, c, n)
