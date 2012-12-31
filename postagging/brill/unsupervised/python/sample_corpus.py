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
    c = ['\n'.join((x, '/sent')) for x in f.split('/sent')]
    return c

def rand_sent(corpus, n, c):
    used = []
    for i in range(c):
        nums = set(range(len(corpus))) - set(used)
        sample = random.sample(nums, n)
        used += sample
        yield sample

def get_random_sentences(corpus, nums):
    c = []
    for i in nums:
        c.append(corpus[i])
    return c


def get_corpora(corpus, c, n):
    """
    Get c instances of Corpus() made up of n sentences.
    Returns iterator!
    """
    randomnums = rand_sent(corpus, n, c)
    i = 0
    for nums in randomnums:
        #yield (i, get_random_sentences(corpus, randomnums))
        yield (i, get_random_sentences(corpus, nums))
        i += 1


if __name__ == '__main__':
    args = sys.argv[1:]
    n = 1
    c = 1
    for i in range(len(args)):
        if args[i] == '-n':
            n = int(args[i + 1])
        if args[i] == '-c':
            c = int(args[i + 1])
    '''for i in rand_sent(range(245), 15, 4):
        print i'''
    inc = sys.stdin.read()
    inc = read_corpus(inc)
    #print write_corpus(inc, sys.stdout)
    for sample in get_corpora(inc, c, n):
        #outc = write_corpus(sample[1], sys.stdout)
        with open('rand%s.tab' % (sample[0] + 10), 'w') as out:
            out.write('\n'.join(sample[1]))
        #corp = open('rand%s.tab' % sample[0], 'r')
        #print numb_amb_corpus(corp.read())
