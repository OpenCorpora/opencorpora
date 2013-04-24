# coding: utf-8

import sys
import random
from utils import write_corpus, numb_amb_corpus, Corpus


def get_first_sent(corpus, s, n):
    ss = sorted(corpus.sents[s:s+n])
    for i in ss:
        c = [i]
        yield Corpus(c)


def read_corpus(f):
    c = [''.join((x, '/sent')) for x in f.split('/sent')]
    return c

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
    for line in corpus:
        line = line.rstrip().decode('utf-8')
        if not line:
            print
            i += 1
            continue
        if i in nums:
            print line.encode('utf-8')
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
