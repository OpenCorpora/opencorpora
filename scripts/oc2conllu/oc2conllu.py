#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys
import argparse
import bz2
import xmltodict
import random

from russian_tagsets import converters


SKIP_DOCS = [
    '4063' # "Слово о полку Игореве"
]
SPACE_CHARS = [ ' ', '\t', '\u00a0', '\u200e', '\u200f' ]
all_docs = []
to_ud = converters.converter('opencorpora-int', 'ud20')


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('-i', '--input',  help='OpenCorpora corpus dump file')
    parser.add_argument('-o', '--output', help='Output file prefix', type=str, default='-')
    parser.add_argument('-s', '--seed',   help='Random seed', type=int, default=0)
    parser.add_argument('-p', '--parts',  help='Output parts', type=str, default=':100')
    args = parser.parse_args()

    random.seed(args.seed, version=2)
    parts = {}
    for p in args.parts.split(','):
        k, v = p.split(':')
        if k in parts:
            raise Exception('Can\'t parse --parts=\'%s\' argument. \'%d\' is stated multiple times.'
                             % (args.parts, k))
        parts[k] = float(v)

    if len(parts.keys()) > 1 and args.output == '-':
        raise Exception('Can\'t split output into multiple parts to STDOUT.')

    with bz2.open(args.input, "r") as f:
        d = xmltodict.parse(f, item_depth=2, item_callback=handle_text)

    add_counters(all_docs)
    ids_per_part = split_to_parts(all_docs, parts)
    conllu_docs = serialize_to_conllu(all_docs)

    if args.output == '-':
        for label in ids_per_part.keys():
            for id in ids_per_part[label]:
                print('\n'.join(conllu_docs[id]), end='\n')
    else:
        for label in ids_per_part.keys():
            fn = args.output + label + '.conllu'
            with open(fn, 'w', encoding='utf-8') as f:
                for id in ids_per_part[label]:
                    f.write('\n'.join(conllu_docs[id]) + '\n')


def serialize_to_conllu(docs):
    conllu_docs = {}

    for d in docs:
        if d['id'] in SKIP_DOCS:
            continue

        lines = []
        lines.append('# newdoc id = %s' % d['id'])

        for p in d['paragraphs']:
            lines.append('# newpar id = %s' % p['id'])

            for s in p['sentences']:
                lines.append('# sent_id = %s' % s['id'])
                lines.append('# text = %s' % s['source'])

                token_id = 1
                for i in range(len(s['tokens'])):
                    t = s['tokens'][i]

                    if i < len(s['tokens']) - 1:
                        if t['pos'] + len(t['form']) == s['tokens'][i+1]['pos']:
                            spaceAfter = 'SpaceAfter=No'
                            t['misc'].append(spaceAfter)

                    fields = [
                              str(token_id),
                              t['form'],
                              t['lemma'],
                              list2field(t['xpos']),
                              t['upos'],
                              list2field(t['feats']),
                              t['head'],
                              t['rel'],
                              t['deprel'],
                              list2field(t['misc'])
                              ]
                    lines.append('\t'.join(fields))
                    token_id += 1

                lines.append('')

        conllu_docs[d['id']] = lines

    return conllu_docs


def list2field(l):
    if len(l) == 0:
        return '_'
    return '|'.join(l)


def handle_text(_, item):
    doc_id = _[1][1]['id']

    if doc_id in SKIP_DOCS:
        return True

    doc = convert_to_conllu(item)

    if doc is None:
        return True

    doc['id'] = int(_[1][1]['id'])
    doc['name'] = _[1][1]['name']

    #sys.stderr.write('%s ...\r' % (_[1][1]['id']))

    if len(doc['paragraphs']) > 0:
        all_docs.append(doc)

    return True


def convert_to_conllu(item):
    paragraphs = []

    if item['paragraphs'] is None:
        return None

    for p in force_list(item['paragraphs']['paragraph']):
        sentences = []

        for s in force_list(p['sentence']):
            src = s['source']
            tokens = []
            pos = 0

            for t in force_list(s['tokens']['token']):
                form = verify_token(t['@id'], t['@text'])
                if len(form) == 0:
                    continue

                start = pos
                matched = 0
                while pos < len(src) and matched < len(form):
                    if src[pos] in SPACE_CHARS:
                        pos += 1
                        if matched == 0:
                            start = pos
                    elif src[pos] == form[matched]:
                        pos += 1
                        matched += 1
                    else:
                        raise Exception('Tokenization bug: %s / %s\n' % (t['@id'], s['@id']))

                if pos > len(src) or matched != len(form):
                    raise

                oc_tags = convert_grams_to_xpos(t)
                upos, ufeats = convert_grams_to_ud(t, oc_tags)
                lemma_str, lemma_id = get_lemma(t)

                token = {
                    'id': t['@id'],
                    'form': form,
                    'textform': src[start:pos],
                    'pos': start,
                    'lemma': lemma_str,
                    'xpos': oc_tags,
                    'upos': upos,
                    'feats': ufeats,
                    'head': '_',
                    'rel': '_',
                    'deprel': '_',
                    'misc': [ 'TokenId=%s' % t['@id'] ]
                }
                if lemma_id > 0:
                    token['misc'].append('LemmaId=%d' % lemma_id)

                #pos = pos + len(token['textform'])

                tokens.append(token)

            sentences.append({
                'id': s['@id'],
                'source': src,
                'tokens': tokens
            })

        if len(sentences) > 0:
            paragraphs.append({
                'id': p['@id'],
                'sentences': sentences
            })
        else:
            sys.stderr.write('Empty paragraph %s\n' % p['@id'])

    return {
        'paragraphs': paragraphs
    }


def assert_attribute_in_dict(a, d):
    if a not in d:
        raise Exception('Attribute \'%s\' doesn\'t exist in dict: \'%s\'' % (str(a), str(d)))


def get_non_homonymic_lemma(token):
    assert_attribute_in_dict('tfr', token)
    assert_attribute_in_dict('v', token['tfr'])
    if isinstance(token['tfr']['v'], list) and len(token['tfr']['v']) != 1:
        raise Exception('Token is homonymous: %s\n' % (str(token)))
    assert_attribute_in_dict('l', token['tfr']['v'])
    return token['tfr']['v']['l']


def get_lemma(token):
    l = get_non_homonymic_lemma(token)
    assert_attribute_in_dict('@t', l)
    return l['@t'], int(l['@id'])


def convert_grams_to_xpos(token):
    l = get_non_homonymic_lemma(token)
    assert_attribute_in_dict('g', l)
    v = []
    if isinstance(l['g'], list):
        for item in l['g']:
            assert_attribute_in_dict('@v', item)
            v.append(item['@v'])
    else:
        v.append(l['g']['@v'])
    return v


def convert_grams_to_ud(token, tags):
    ud_str = to_ud(','.join(tags))
    upostag, ufeats_str = ud_str.split(' ')
    if upostag is None or upostag == '_':
        raise Exception('Can\'t convert gramset \'%s\' to UD PoST' % ud_str)
    ufeats = ufeats_str.split('|')
    return upostag, ufeats


def find_token_pos(source, text, prev):
    pos, length = prev, 0

    hyp = ' '.join(list(text))

    while source[pos:pos + len(hyp)] != hyp and pos < len(source):
        pos += 1

    if pos < len(source):
        length = len(hyp)
        return pos, length

    return None, None


def verify_token(id, token):
    if len(token) == 0:
        sys.stderr.write('Empty token %s: \"%s\"\n' % (id, token))
    for ch in SPACE_CHARS:
        if ch in token:
            sys.stderr.write('Space symbol inside token %s: \"%s\"\n' % (id, token))
    while len(token) > 0 and token[-1] in SPACE_CHARS:
        token = token[:-1]
    while len(token) > 0 and token[0] in SPACE_CHARS:
        token = token[1:]
    if len(token) == 0:
        sys.stderr.write('Empty token %s: \"%s\"\n' % (id, token))
    return token


def force_list(obj):
    if isinstance(obj, list):
        return obj
    else:
        return [obj]


def split_to_parts(docs, parts):
    ids = [ d['id'] for d in docs ]
    random.shuffle(ids)

    doc_by_id = {}
    for d in docs:
        doc_by_id[d['id']] = d

    total_tokens = sum([ d['counters']['tokens'] for d in docs ])
    s = sum([ parts[k] for k in parts.keys() ])

    tokens_per_part = {}
    for k in parts.keys():
        f = parts[k] / s
        tokens_per_part[k] = f * total_tokens

    ids_per_part = {}
    part_no = 0
    for id in ids:
        k = list(parts.keys())[part_no]

        if k not in ids_per_part:
            ids_per_part[k] = []
            counter = 0

        ids_per_part[k].append(id)
        counter += doc_by_id[id]['counters']['tokens']

        if counter >= tokens_per_part[k]:
            part_no += 1

    for k in ids_per_part:
        ids_per_part[k].sort()

    return ids_per_part


def add_counters(docs):
    for d in docs:
        d['counters'] = {
            'tokens': 0,
            'sentences': 0,
            'paragraphs': 0
        }
        for p in d['paragraphs']:
            for s in p['sentences']:
                for t in s['tokens']:
                    d['counters']['tokens'] += 1
                d['counters']['sentences'] += 1
            d['counters']['paragraphs'] += 1


if __name__ == "__main__":
    main()
