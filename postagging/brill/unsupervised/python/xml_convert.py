# coding: utf-8

import sys
import re

from cStringIO import StringIO

#TODO: жанр <tag>Тип:
def get_text_by_tag(lines, tags):
    for line in lines:
        tag_text = dict(zip(tags, [[] for i in range(len(tags))]))
        #line = line.decode('utf-8')
        if line.lstrip().startswith('<token'):
            for tag in tags:
                rtag = tag + '="'
                if tag in ('token id', 'l id'):
                    pattern = re.compile(u'(?<=%s)(\d+)\"' % rtag, re.UNICODE)
                if tag == 'v':
                    pattern = re.compile(u'(?<=%s)([^\"]+?)\"' % rtag, re.UNICODE)
                if tag == 'text':
                    pattern = re.compile(u'(?<=%s)([^\"]+?)\"' % rtag, re.UNICODE)
                if tag == 't':
                    pattern = re.compile(u'(?<=%s)([^\"]+?)\"><g' % rtag, re.UNICODE)
                m = pattern.findall(line)
                for match in m:
                    postag = re.compile('^[A-Z]{4}$', re.UNICODE)
                    if re.search(postag, match) is not None and tag == 'v':
                        tag_text[tag].append(match)
                    elif tag == 'v':
                        try:
                            tag_text[tag][-1] += (' ' + match)
                        except:
                            tag_text[tag].append(match)
                    else:
                        tag_text[tag].append(match)
                    if tag in ('token id', 'text'):
                        sys.stdout.write(match + '\t')
            variants = zip(tag_text['l id'], tag_text['t'], tag_text['v'])
            for i in range(len(variants[:])):
                var = variants.pop(0)
                var = ' '.join(var)
                variants.append(var)
            sys.stdout.write('\t'.join(variants))
            print
        elif line.lstrip().startswith('<sent'):
            print 'sent'
        elif line.lstrip().startswith('</sent'):
            print '/sent'

def get_text_id_by_type(lines, type_tag):
    t = re.compile(u'<tag>Тип:%s' % type_tag, re.U)
    parent = re.compile(u'<text id=\"(?P<id>\d+)\" parent=\"(?P<p>\d+)', re.U)
    f = re.compile(u'</text>', re.U)
    found = False
    started = False
    nums = ["0"]
    for line in lines:
        line = line.strip()
        if not line:
            continue
        if line.decode('utf-8').startswith(u'<tag>Тип:%s' % type_tag):
            nums.append(id)
            found = True
            continue
        m = parent.match(line)
        if m:
            if m.group('p') in nums:
                id = m.group('id')
                if found and m.group('p') != "0":
                    nums.append(id)
                    started = True
        if started:
            yield line
        if f.match(line):
            started = False
    #return nums

def get_texts_by_id(lines, ids):
    p = re.compile(u'<text id=\"(?P<id>\d+)\" parent=\"(?P<p>\d+)', re.U)
    f = re.compile(u'</text>', re.U)
    started = False
    for line in lines:
         line = line.strip()
         m = p.match(line)
         if m and m.group('id') in ids:
             started = True
         if started:
             print line
         if f.match(line):
             started = False    
            

if __name__ == '__main__':
    get_text_by_tag(get_text_id_by_type(sys.stdin, u'Юридические тексты'), ('token id', 'text', 'l id', 't', 'v'))
    #get_text_id_by_type(sys.stdin, u'Юридические тексты')

