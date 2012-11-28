#coding: utf-8

import sys
import re

from StringIO import StringIO


def get_text_by_tag(lines, tags):
    for line in lines:
        tag_text = dict(zip(tags, [[] for i in range(len(tags))]))
        line = line.decode('utf-8')
        output = StringIO()
        if line.lstrip().startswith('<token'):
            for tag in tags:
                rtag = tag + '="'
                if tag in ('token id', 'l id'):
                    pattern = re.compile(u'(?<=%s)(\d+)\"' % rtag, re.UNICODE)
                if tag == 'v':
                    pattern = re.compile(u'(?<=%s)([^\"]+?)\"' % rtag, re.UNICODE)
                if tag == 'text':
                    #pattern = re.compile(u'(?<=%s)([A-Za-zА-ЯЁа-яё0-9\-\.\!\?\,\"\']+?)\"' % rtag, re.UNICODE)
                    pattern = re.compile(u'(?<=%s)([^\"]+?)\"' % rtag, re.UNICODE)
                m = pattern.findall(line)
                for match in m:
                    postag = re.compile('^[A-Z]{4}$', re.UNICODE)
                    if re.search(postag, match) is not None and tag == 'v':
                        if tag_text[tag] != []:
                            if tag_text[tag][-1][0].isupper and re.search(postag, tag_text[tag][-1]) is None:
                                tag_text[tag][-1] += (' ' + match)
                            else:
                                tag_text[tag].append(match)
                        else:
                            tag_text[tag].append(match)
                    elif tag == 'v':
                        try:
                            tag_text[tag][-1] += (' ' + match)
                        except:
                            tag_text[tag].append(match)
                    else:
                        tag_text[tag].append(match)
                    if tag in ('token id', 'text'):
                        output.write(match + '\t')
            variants = zip(tag_text['l id'], tag_text['v'])
            for var in variants:
                output.write(var[0] + '\t' + var[1] + '\t')
        elif line.lstrip().startswith('<sent'):
            output.write('sent')
        elif line.lstrip().startswith('</sent'):
            output.write('/sent')
        yield output.getvalue().rstrip().encode('utf-8')

if __name__ == '__main__':
    for line in get_text_by_tag(sys.stdin, ('token id', 'text', 'l id', 'v')):
        if line.rstrip() is not '':
            sys.stdout.write(line + '\n')
