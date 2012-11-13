#coding: utf-8

import sys
import re

from StringIO import StringIO


def get_text_by_tag(lines, tags):
    for line in lines:
        line = line.decode('utf-8')
        output = StringIO()
        if line.lstrip().startswith('<token'):
            for tag in tags:
                chunks = StringIO()
                tag = tag + '="'
                pattern = re.compile(u'(?<=%s)([A-Za-zА-ЯЁа-яё0-9\-\.\!\?\,\"\']+?)\"' % tag, re.UNICODE)
                m = pattern.findall(line)
                for match in m:
                    lat_pattern = re.compile('^[A-Z]{4}$', re.UNICODE)
                    if re.search(lat_pattern, match) is not None and tag == 'v="':
                        chunks.write('\t' + match + ' ')
                    elif tag == 'v="' and match[0].isupper():
                        pass
                    else:
                        chunks.write(match + ' ')
                output.write(chunks.getvalue().rstrip() + '\t')
        elif line.lstrip().startswith('<sent'):
            output.write('<sent>')
        elif line.lstrip().startswith('</sent'):
            output.write('</sent>')
        yield output.getvalue().rstrip().encode('utf-8')

if __name__ == '__main__':
    for line in get_text_by_tag(sys.stdin, ('token id', 'text', 'v')):
        if line.rstrip() is not '':
            sys.stdout.write(line + '\n')
