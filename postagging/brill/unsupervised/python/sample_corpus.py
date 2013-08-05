# coding: utf-8

import sys
import random
import argparse


def rand_sent(l, n, c):
    used = []
    for i in range(c):
        nums = set(range(1, l)) - set(used)
        sample = random.sample(nums, n)
        used += sample
        yield sample


def get_random_sentences(corpus, nums, out):
    i = 0
    with open(out, 'w') as out:
        for line in corpus:
            #print i
            line = line.rstrip().decode('utf-8')
            if not line:
                continue
            if line.startswith('sent'):
                i += 1
            if i in nums:
                print >> out, line.encode('utf-8')


def get_corpora(corpus, l, c, n, out):
    randomnums = rand_sent(l, n, c)
    for i, nums in enumerate(randomnums):
        i_out = '%s%d.tab' % (out, i)
        get_random_sentences(corpus, nums, i_out)


if __name__ == '__main__':
    p = argparse.ArgumentParser()
    p.add_argument('-l', '--limit', type=int, default=1000000,
                   help='Specify the number of sentences from which random corpora should be extracted')
    p.add_argument('-n', type=int, default=1,
                   help='Size of corpus to be extracted')
    p.add_argument('-c', type=int, default=1,
                   help='Number of random corpora')
    p.add_argument('-p', default='rand', help='Output file prefix')
    args = p.parse_args()
    get_corpora(sys.stdin, args.limit, args.c, args.n, args.p)
