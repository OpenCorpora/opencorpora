# coding: utf-8

import sys
import random
from utils import read_corpus, write_corpus, numb_amb_corpus, Corpus


def get_first_sent(corpus, s, n):
    ss = sorted(corpus.sents[s:s+n])
    for i in ss:
        c = [i]
        yield Corpus(c)


def rand_sent(corpus, n):
    return set(random.sample(xrange(len(corpus)), n))

def get_random_sentences(corpus, nums):
    c = []
    for i in nums:
        c.append(corpus.sents[i])
    return Corpus(c)


def get_corpora(corpus, c, n):
    """
    Get c instances of Corpus() made up of n sentences.
    Returns iterator!
    """
    corpora = []
    for i in range(c):
        randomnums = rand_sent(corpus, n)
        corpora.append(randomnums)
        if i == 0:
            yield (i, get_random_sentences(corpus, randomnums))
        else:
            while True:
                flag = True
                randomnums = rand_sent(corpus, n)
                for cp in corpora:
                    if randomnums.isdisjoint(cp):
                        flag = False
                if flag:
                    yield (i, get_random_sentences(corpus, randomnums))
                    break


if __name__ == '__main__':
    args = sys.argv[1:]
    n = 1
    c = 1
    for i in range(len(args)):
        if args[i] == '-n':
            n = int(args[i + 1])
        if args[i] == '-c':
            c = int(args[i + 1])
    inc = sys.stdin.read()
    inc = read_corpus(inc)
    #print write_corpus(inc, sys.stdout)
    for sample in get_corpora(inc, c, n):
        #outc = write_corpus(sample[1], sys.stdout)
        outc = write_corpus(sample[1], open('rand%s.tab' % (sample[0] + 10), 'w'))
        #corp = open('rand%s.tab' % sample[0], 'r')
        #print numb_amb_corpus(corp.read())
    '''for i in get_first_sent(inc, c, n):
        print write_corpus(i, sys.stdout)'''

