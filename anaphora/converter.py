# coding: utf-8

import sys
import xml.etree.ElementTree as etree


def iter_parse_anaph(docs):
    e = etree.ElementTree(file='anaph_new.xml')
    for d in e.getiterator(tag='document'):
        a = {}
        rexp = {}
        for i in d.iter(tag='chain'):
            chain = i.getchildren()
            a[int(chain[1].attrib['sh'])] = int(chain[0].attrib['sh'])
            for r in chain:
                rexp[int(r.attrib['sh'])] = int(r.attrib['ln'])
        docs[d.attrib['id']] = d.attrib['file']
        yield d.attrib['id'], d.attrib['file'], a, rexp


def group_id(docid, n):
    return '%04d_%04d' % (int(docid), n)


def text_tokenized(docid, docf):
    textf = open('AnaphFiles/AnaphFiles/%s' % docf, 'r')
    text = textf.read().decode('utf-8').replace('\t', ' ')
    #text = text.replace('\r', ' ')
    text = text.replace('\n', ' ')
    tok_ids = []
    tokens = {}
    start = 0
    end = 1
    with open('AnaphFiles/AnaphFiles/%s.tab' % docf, 'r') as tok_text:
        for line in tok_text:
            if line == 'end\n' or line.endswith('sent\n'):
                continue
            token_id, token = line.decode('utf-8').split('\t')[:2]
            token_id = group_id(docid, int(token_id))
            tokens[token_id] = token
            while start < len(text) and text[start] == ' ':
                tok_ids.append(' ')
                start += 1
            end = start + len(token)
            if not text[start:end] == token:
                #print >> sys.stderr, text[start-5:end+5]
                #print >> sys.stderr, token
                #print >> sys.stderr, docf
                return
            tok_ids += [token_id] * len(token)
            start = end
    return text, tok_ids, tokens


def to_np_list(docid, docf, a, rexp):
    groups = {}
    text, tok_ids, tokens = text_tokenized(docid, docf)
    for i, r in enumerate(sorted(rexp.iteritems(), key=lambda x: x[0])):
        pos, length = r
        if ' ' in tok_ids[pos:pos+length]:
            j = tok_ids[pos:pos+length]
            while ' ' in j:
                j.remove(' ')
            #print pos, length, ' '.join((tokens[x] for x in sorted(set(j)))).encode('utf-8'), text[pos:pos+length].encode('utf-8')
        else:
            j = tok_ids[pos:pos+length]
            #pos, length, ' '.join((tokens[x] for x in sorted(set(j)))).encode('utf-8')
        j = sorted(set(j))
        print group_id(docid, i) + '\t' + ','.join(j)
        groups[pos] = group_id(docid, i)
        if pos in a.keys():
            print >> sys.stderr, groups[a[pos]] + '\t' + group_id(docid, i)


def split_files(k):
    with open('learning.%s' % k, 'r') as g:
        for line in g:
            line = line.rstrip('\n').split('\t')
            text, group = line[0].split('_')
            tokens = []
            for t in line[1].split(','):
                if not t:
                    continue
                tokens.append(t.split('_')[1].lstrip('0'))
            with open('AnaphFiles/AnaphFiles/%s.%s' % (docs[text.lstrip('0')], k), 'a') as f:
                print >> f, group.lstrip('0') + '\t' + ','.join(tokens)


def parse_groups(gfile):
    pass


def parse_pairs(pfile):
    pass


def to_xml(docid):
    et = ElementTree()
    mf = open('%s.tab' % docid)
    gf = open('%s.groups' % docid)
    pf = open('%s.pairs' % docid)
    groups = parse_groups(gf)
    pairs = parse_pairs(pf)
    with open(docid, 'r') as f:
        text, tok_ids, tokens = text_tokenized(docid.split('.')[0], docid)
    for antc, anph in pairs:
        pass


if __name__ == '__main__':
    #for i, j, m, k in iter_parse_anaph():
    #    to_np_list(i, j, m, k)
    docs = {}
    for i in iter_parse_anaph(docs):
        pass
    split_files('pairs')
