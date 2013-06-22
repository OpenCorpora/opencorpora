# coding: utf-8

import random
import sys
from pprint import pprint
from utils import read_corpus, write_corpus, Rule, context_stats, Token

_NULL_TOKEN = Token(('SENT', 'SENT'))

def scores(s, best_rules):
    #scores = {}
    bestscore = 0
    bestrule = []
    a = 0
    top_rules = {}
    for atag in s.keys():
        if len(atag) > 4:
            stat = s[atag]
            vtags = atag.split()
            for y in vtags:
                try:
                    freq = s[y]
                except:
                    s[y] = ({0: {}}, {0: {}}, 0)
                    continue
                for i, cont_type in enumerate(s[y][:2]):
                    for distance in cont_type:
                        for context in cont_type[distance]:
                            fr = -sys.maxint
                            for z in vtags:
                                if y != z:
                                # relative frequency
                                    try:
                                        incontext_z = s[z][i][distance][context]
                                    except:
                                        incontext_z = 0.0
                                    try:
                                        freq_z = s[z][2]
                                        relf = float(s[y][2]) / float(freq_z) * float(incontext_z)
                                    except:
                                        freq_z = 0.0
                                        relf = 0.0
                                    if relf >= fr:
                                        fr = relf
                                    try:
                                        w = incontext_z
                                    except:
                                        w = 0
                            x = s[y][i][distance][context] - float(fr)
                            curr_rule = Rule(*[atag, y, (distance, context), i])
                            #scores[curr_rule] = x
                            if x > bestscore and s[y][i][distance][context] != w:
                                try:
                                    a = stat[i][distance][context]
                                except:
                                    continue
                                bestscore = x
                                bestrule = curr_rule
                                top_rules = {}
                                top_rules[bestrule] = a
                            elif x == bestscore and s[y][i][distance][context] != w:
                                try:
                                    a = stat[i][distance][context]
                                except:
                                    continue
                                bestrule = curr_rule
                                top_rules[bestrule] = a
    return top_rules, bestscore, a


def apply_rule(rule, corpus, ignore_numbers=True, wsize=2):
    s = [_NULL_TOKEN]
    rc = rule.context
    more = False
    if isinstance(rc[0], (set, tuple)):
        more = True
        context = zip(*rc)
    else:
        context = list(rc)
    for t in corpus:

        if t.orig_text != 'SENT':
            s.append(t)
            continue
        else:
            s.append(t)
            for i, token in enumerate(s[1:], 1):
                left = i - wsize
                right = i + wsize + 1

                if left < 0:
                    left = 0
                c = s[left:right]
                if right > len(s) - 1:
                    right = len(s) - 1
                    c = s[left:]

                if token.getPOStags() == rule.tagset:

                    if not more:
                        try:
                            curr_context = list(list(x for x in enumerate([w.getByIndex(rule.ind) for w in c],
                                                              - (i - left)) if x[0] in rc)[0])
                        except:
                            curr_context = []
                    else:
                        try:
                            curr_context = [x for x in enumerate([w.getByIndex(rule.ind) for w in c],
                                                              - (i - left)) if x[0] in rc[0]]
                        except:
                            curr_context = []

                    #print >> sys.stderr, curr_context, context
                    if context == curr_context:
                        #print >> sys.stderr, 0
                        token.disambiguate(rule.tag)
                yield token
            s = [_NULL_TOKEN]
    else:
        for i, token in enumerate(s[1:], 1):
            left = i - wsize
            right = i + wsize + 1

            if left < 0:
                left = 0
            c = s[left:right]
            if right > len(s) - 1:
                right = len(s) - 1
                c = s[left:]

            if token.getPOStags() == rule.tagset:
                if not more:
                    try:
                        curr_context = [x[0] for x in enumerate([w.getByIndex(rule.ind) for w in c],
                                                          - (i - left)) if x[0] in rc]
                        curr_context.append([x[1] for x in enumerate([w.getByIndex(rule.ind) for w in c],
                                                          - (i - left)) if x[0] in rc][0])
                        #print context, curr_context
                    except:
                        curr_context = []
                else:
                    try:
                        curr_context = [x for x in enumerate([w.getByIndex(rule.ind) for w in c],
                                                          - (i - left)) if x[0] in rc[0]]
                    except:
                        curr_context = []
                if context == curr_context:
                    token.disambiguate(rule.tag)
            yield token


def random_choice(corpus):
    for s in corpus:
        for token in s:
            try:
                if token.has_ambig():
                    token.disambiguate(random.choice(token.getPOStags().split('_')))
            except:
                pass


if __name__ == '__main__':
    inc = read_corpus(sys.stdin)
    #s = context_stats(inc, join_context=True)
    #print s
    #rs = scores(s, [])
    #pprint(rs[1:3])
    '''for x in rs[1].keys():
        print x.display()
        print rs[1][x]
    print rs[2]'''
    r = Rule('ADVB NOUN', 'NOUN', (1, 'PNCT'), 0)

    write_corpus(apply_rule(r, inc))
