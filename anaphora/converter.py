# coding: utf-8

import sys
import xml.etree.ElementTree as etree


def iter_parse_anaph():
    e = etree.ElementTree(file='anaph_new.xml')
    for d in e.getiterator(tag='document'):
        a = {}
        rexp = {}
        for i in d.iter(tag='chain'):
            chain = i.getchildren()
            a[chain[0].attrib['sh']] = chain[1].attrib['sh']
            for r in chain:
                rexp[r.attrib['sh']] = r.attrib['ln']
        yield d.attrib['id'], a, rexp


def to_np_list(rexp):
    pass

if __name__ == '__main__':
    iter_parse_anaph()

