# coding: utf-8

from itertools import combinations
import random
import sys
from utils import read_corpus, Rule, Token, TagStat
from utils import feature_type

_NULL_TOKEN = Token(('SENT', 'SENT'))


def context_stats(corpus, ignore_numbers=True,
                  wsize=2, join_context=False,
                  cf=2, fixed=False, f=None):
    # feature: дополнительные признаки

    if not f:
        f = 'POS'
    _NULL_TOKEN = Token(('SENT', 'SENT'))
    result_dict = {}
    s = [_NULL_TOKEN]
    for line in corpus:

        if line.orig_text != 'SENT':
            s.append(line)
            continue
        else:
            s.append(line)
            for i, token in enumerate(s[1:], 1):
                tag_1 = token.getFeature(f)
                if not tag_1:
                    continue
                left = i - wsize
                right = i + wsize + 1

                if left < 0:
                    left = 0
                if right > len(s) - 1:
                    right = len(s) - 1

                context = s[left:right]
                for j, t in enumerate(context, - (i - left)):
                    #comment these two lines to include word as a feature for itself
                    if not j:
                        continue

                    try:
                        result_dict[tag_1][0].update(j, t.getPOStags())
                        #print result_dict[tag_1][0].stat
                    except KeyError:
                        result_dict[tag_1] = [TagStat(), TagStat(), TagStat(), 0]
                        result_dict[tag_1][0].update(j, t.getPOStags())
                    if t.text != 'SENT':
                        result_dict[tag_1][1].update(j, t.text)
                        if t.getFeature(f) and f != 'POS':
                            result_dict[tag_1][2].update(j, t.getFeature(f))

                if join_context: # переписать для разного количества конт.признаков
                    for t1, t2 in combinations(enumerate(context, - (i - left)), cf):
                        #comment these four lines to include word as a feature for itself
                        if not t1[0]:
                            continue
                        if not t2[0]:
                            continue
                        result_dict[tag_1][0].update((t1[0], t2[0]), \
                                                      (t1[1].getPOStags(), t2[1].getPOStags()))
                        if t.text != 'SENT':
                            result_dict[tag_1][1].update((t1[0], t2[0]), t1[1].text,
                                                         t2[1].text)
                            if t1[1].getFeature(f) and t2[1].getFeature(f) and f != 'POS':
                                result_dict[tag_1][2].update((t1[0], t2[0]), t1[1].getFeature(f),
                                                         t2[1].getFeature(f))
                try:
                    result_dict[tag_1][3] += 1
                except KeyError:
                    result_dict[tag_1] = [TagStat(), TagStat(), TagStat(), 0]
                    result_dict[tag_1][3] += 1

            s = [_NULL_TOKEN]
    else:
        for i, token in enumerate(s[1:], 1):
                tag_1 = token.getFeature(f)
                if not tag_1:
                    continue
                left = i - wsize
                right = i + wsize + 1

                if left < 0:
                    left = 0
                if right > len(s) - 1:
                    right = len(s) - 1

                context = s[left:right]
                for j, t in enumerate(context, - (i - left)):
                    #comment these two lines to include word as a feature for itself
                    if not j:
                        continue

                    try:
                        result_dict[tag_1][0].update(j, t.getPOStags())
                    except:
                        result_dict[tag_1] = [TagStat(), TagStat(), TagStat(), 0]
                        result_dict[tag_1][0].update(j, t.getPOStags())
                    if t.text != 'SENT':
                        result_dict[tag_1][1].update(j, t.text)
                        if t.getFeature(f) and f != 'POS':
                            result_dict[tag_1][2].update(j, t.getFeature(f))

                    if join_context:
                        for t1, t2 in combinations(enumerate(context, - (i - left)), cf):
                            #comment these four lines to include word as a feature for itself
                            if not t1[0]:
                                continue
                            if not t2[0]:
                                continue
                            result_dict[tag_1][0].update((t1[0], t2[0]), \
                                                          (t1[1].getPOStags(), t2[1].getPOStags()))
                            if t.text != 'SENT':
                                result_dict[tag_1][1].update((t1[0], t2[0]), t1[1].text,
                                                         t2[1].text)
                            if t1[1].getFeature(f) and t2[1].getFeature(f) and f != 'POS':
                                result_dict[tag_1][2].update((t1[0], t2[0]), t1[1].getFeature(f),
                                                         t2[1].getFeature(f))
                try:
                    result_dict[tag_1][3] += 1
                except:
                    result_dict[tag_1] = [TagStat(), TagStat(), TagStat(), 0]
                    result_dict[tag_1][3] += 1
    stats = {}
    for tag in result_dict.keys():
        stats[tag] = (result_dict[tag][0].stat, result_dict[tag][1].stat,
                       result_dict[tag][2].stat, result_dict[tag][3])
    return stats


def scores(s, best_rules, f='POS'):
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
                except KeyError:
                    s[y] = ({0: {}}, {0: {}}, {0: {}}, 0)
                    continue
                for i, cont_type in enumerate(s[y][:3]):
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
                                        freq_z = s[z][3]
                                        relf = float(s[y][3]) / float(freq_z) * float(incontext_z)
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
                            curr_rule.id = feature_type(context)
                            if x > bestscore and s[y][i][distance][context] != w:
                                try:
                                    a = stat[i][distance][context]
                                except KeyError:
                                    continue
                                bestscore = x
                                bestrule = curr_rule
                                top_rules = {}
                                top_rules[bestrule] = a
                            elif x == bestscore and s[y][i][distance][context] != w:
                                try:
                                    a = stat[i][distance][context]
                                except KeyError:
                                    continue
                                bestrule = curr_rule
                                top_rules[bestrule] = a
    return top_rules, bestscore, a


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
