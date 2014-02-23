# coding: utf-8

from pprint import pprint
import sys
import xml.etree.ElementTree as etree


def group_head(tok_ids):
    annots = [x[1][1] for x in tok_ids]
    #print annots
    ncount = ['NOUN' in '\t'.join(x) for x in annots].count(True)
    group = [x[1][0] for x in tok_ids]
    if len(tok_ids) == 1:
        return tok_ids[0][0]
    elif ncount == 1:
        for x in tok_ids:
            if 'NOUN' in '\t'.join(x[1][1]):
                #print x[0], x[1][0]
                return x[0]
    elif u'и' in group:
        return 'ALL'
    elif ncount < 1:
        for x in tok_ids:
            if 'NPRO' in '\t'.join(x[1][1]):
                #print x[0], x[1][0]
                return x[0]
            if 'UNKN' in '\t'.join(x[1][1]):
                return x[0]
            if 'Apro' in '\t'.join(x[1][1]):
                return x[0]
    else:
        for x in tok_ids:
            if 'NOUN' in '\t'.join(x[1][1]):
                #print x[0], x[1][0]
                return x[0]


def iter_parse_anaph(docs):
    e = etree.ElementTree(file='LearningSet/anaph_new.xml')
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
    textf = open('LearningSet/AnaphFiles/%s' % docf, 'r')
    text = textf.read().decode('utf-8').replace('\t', ' ')
    #text = text.replace('\r', ' ')
    text = text.replace('\n', ' ')
    tok_ids = []
    tokens = {}
    start = 0
    end = 1
    with open('LearningSet/AnaphFiles/%s.tab' % docf, 'r') as tok_text:
        for line in tok_text:
            if line == 'end\n' or line.endswith('sent\n'):
                continue
            line = line.decode('utf-8').split('\t')
            token_id, token = line[:2]
            annot = line[2:]
            token_id = group_id(docid, int(token_id))
            tokens[token_id] = [token, annot]
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
        h = group_head([(x, tokens[x]) for x in j])
        print group_id(docid, i) + '\t' + ','.join(j).encode('utf-8') + '\t' + h.encode('utf-8')
        groups[pos] = group_id(docid, i)
        if pos in a.keys():
            print >> sys.stderr, groups[a[pos]] + '__' + group_id(docid, i)


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
    groups = {}
    for line in gfile:
        line = line.rstrip('\n').split('\t')
        groups[line[0]] = line[1:]
    return groups


def parse_pairs(pfile):
    pairs = {}
    for line in pfile:
        line = line.rstrip('\n').split('\t')
        if not line[0]:
            continue
        antc, anph = line[0].split('_')
        pairs[antc] = anph
    return pairs


def to_xml(docid):
    #et = etree.ElementTree()
    mf = open('%s.tab' % docid)
    gf = open('%s.groups' % docid)
    pf = open('%s.pairs' % docid)
    root = etree.Element('documents')
    d = etree.SubElement(root, 'document', {'id': docid})
    groups = parse_groups(gf)
    pairs = parse_pairs(pf)
    with open(docid, 'r') as f:
        docid = 'OFC/2.txt'
        #os.path.basename(path)
        text, tok_ids, tokens = text_tokenized(docid.split('.')[0], docid)
    for antc, anph in pairs.iteritems():
        chain = etree.SubElement(d, 'chain')
        i = tok_ids.index(
            group_id('2', int(groups[antc][0].split(',')[0])))
        ii = len(' '.join([tokens[group_id(docid, int(x))][0] for x in groups[antc][0].split(',')]))
        j = tok_ids.index(
            group_id('2', int(groups[anph][0].split(',')[0])))
        jj = len(' '.join([tokens[group_id(docid, int(x))][0] for x in groups[anph][0].split(',')]))
        ac = etree.SubElement(chain, 'item', {'sh': str(i), 'len': str(ii)})
        c1 = etree.SubElement(ac, 'cont', {})
        an = etree.SubElement(chain, 'item', {'sh': str(j), 'len': str(jj)})
        c2 = etree.SubElement(an, 'cont', {})
    print etree.dump(root)


if __name__ == '__main__':
    docs = {}
    for i, j, m, k in iter_parse_anaph(docs):
        to_np_list(i, j, m, k)
    #to_xml('ana_test')
    #for i in iter_parse_anaph(docs):
    #    pass
    #split_files('pairs')
