# coding: utf-8

import sys

from learn_rules.utils import apply_rule, read_corpus, write_corpus, parse_rule


if __name__ == '__main__':
    TYPES = {'tag': 0, 'word': 1}
    rules = []
    inc = read_corpus(sys.stdin)
    for line in open(sys.argv[1], 'r'):
        if not line:
            continue
        r = parse_rule(line)
        rules.append(r)
        inc = list(apply_rule(r, inc))
    write_corpus(inc)
