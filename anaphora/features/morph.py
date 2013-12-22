# coding: utf-8

from collections import defaultdict, Counter
import sys

from utils import read_corpus


def parse_pairs(lines):
    # TODO: может быть неоднозначное соответствие
    """Returns NP pairs"""
    antc, anph = defaultdict(list), {}
    for l in lines:
        l = l.rstrip('\r\n')
        if not l:
            continue
        l = [int(x) for x in l.split('\t')[0].split('_')]
        antc[l[0]].append(l[1])
        anph[l[1]] = l[0]
    return antc, anph


def parse_NPs(lines, pairs):
    """Returns dict (token, (groups, heads))"""
    token_nps = defaultdict(dict)
    head_np = defaultdict(list)
    np_head = {}
    for l in lines:
        l = l.rstrip('\r\n')
        if not l:
            continue
        np, token, head = l.split()
        if head == 'ALL':
            head = 0
        np = int(np)
        head = int(head)
        tokens = token.split(',')
        for t in tokens:
            token_nps[t][np] = head
        head_np[head].append(np)
        np_head[np] = head
    return dict(head_np), np_head


def _in_pair(tid, head_nps, pairs):
    """Returns corresponding NP index in pair, NPs"""
    for np in head_nps[tid]:
        if np in pairs[0].keys():
            return 0, np, pairs[0][np]  # antecedent
        if np in pairs[1].keys():
            return 1, pairs[1][np], np  # anaphora


def isNomn(token):
    c = token.getCase()
    if not c:
        return None
    if len(c) > 1:
        print 'Warning: more than one annotation for one token!'
    return int('nomn' in c)


def agreement(anph, antc, f='case'):  # anph, antc - признаки
    return anph == antc


def numOfPOS(pos, token, n=0):
    post = token.getPOStags()
    if pos in post:
        if len(post) > 1:
            print >> sys.stderr, 'Warning: more than one annotation for one token!'
        n += 1


def add_pos_count(token, c):
    pos = token.getPOStags()
    if 'VERB' in pos:
        c['V'] += 1
    elif 'NOUN' in pos:
        c['N'] += 1
    elif 'CONJ' in pos or ('ADJF' in pos and 'Apro' in token.tagset):
        c['C'] += 1
    elif reduce(lambda x, y: x in pos or y in pos, ['PRTF', 'PRTS', 'GRND']):
        c['o'] += 1


def numOfNouns(token, n=0):
    post = token.getPOStags()
    if 'NOUN' in post:
        if isNomn(token):
            n += 1


def gnc(token):
    if token.getPOStags() != 'UNKN':
        return ' '.join(['_'.join(t) for t in zip(token.getGender(),
                        token.getNUMBtag(), token.getCase())])


def isNpro(token):
    if 'NPRO' in token.getPOStags():
        return 1
    return 0


_funcs = (isNomn, isNpro, gnc, )


def main():
    head_funcs = {}
    pairs = parse_pairs(sys.stdin)
    head_np, np_head = parse_NPs(open(sys.argv[1]), pairs)
    id_for_sent = []
    c = Counter()
    for token in read_corpus(open(sys.argv[2])):
        if token.id == 'SENT':
            for i in id_for_sent:
                head_funcs[i]['verbs'] = c['V']
                head_funcs[i]['oids'] = c['o']
                head_funcs[i]['conjs'] = c['C']
                head_funcs[i]['nouns'] = c['N']
            id_for_sent = []
            c.clear()
            continue
        add_pos_count(token, c)
        if token.id in head_np.keys():
            in_pair = _in_pair(token.id, head_np, pairs)
            if in_pair:
                id_for_sent.append(token.id)
                hf = {}
                hf['isNomn'], hf['isNpro'], hf['gnc'] = [hhf(token) for hhf in _funcs]
                hf['text'] = token.orig_text.encode('utf-8')
                hf['id'] = token.id
                hf['POS'] = token.getPOStags()[0]
                hf['gnc'] = gnc(token)
                hf['gender'], hf['number'], hf['case'] = gnc(token).split('_')
                head_funcs[token.id] = hf
                if in_pair[0]:
                    antc = np_head[in_pair[1]]
                    anph = np_head[in_pair[2]]
                    #for p_anph in in_pair[2]:
                    #    anph = np_head[p_anph]
                    hf['c_agr'] = agreement(head_funcs[antc]['case'], head_funcs[anph]['case'])
                    hf['g_agr'] = agreement(head_funcs[antc]['gender'], head_funcs[anph]['gender'])
                    hf['n_agr'] = agreement(head_funcs[antc]['number'], head_funcs[anph]['number'])
                    hf['agreement'] = hf['g_agr'] and hf['c_agr'] and hf['n_agr']
                    hf['gn_agr'] = hf['g_agr'] and hf['n_agr']
                    hf['match'] = head_funcs[antc]['text'] == head_funcs[anph]['text']
                    head_funcs[token.id] = hf
    func = 'POS number gender case isNomn isNpro g_agr n_agr c_agr agreement verbs oids conjs nouns'.split()
    for i, f in enumerate(pairs[0].keys()):
        hhf = head_funcs[np_head[f]]
        for a in pairs[0][f]:
            ana = head_funcs[np_head[a]]
            if not i:
                print 'id' + '\t' + '\t'.join(str(x[0]) for x in 
                                              sorted(filter(lambda x: x[0] in func, hhf.items() + ana.items()), key=lambda x: x[0]))
            print str(f) + '_' + str(a) + '\t' + \
            '\t'.join(str(x[1]) for x in sorted(filter(lambda x: x[0] in func, hhf.items() + ana.items()), key=lambda x: x[0]))

if __name__ == '__main__':
    #head_funcs = defaultdict(namedtuple)
    main()
