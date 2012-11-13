#coding: utf-8

from xml.dom.minidom import parse
from StringIO import StringIO


def get_sents(xml_file):
    doc = parse(xml_file)
    sentences = doc.getElementsByTagName('sentence')
    return sentences


def get_tokens(sentence):
    tokens = sentence.getElementsByTagName('token')
    return tokens


def get_word_pos(token):
    try:
        word = token.getAttribute('text')
        pos = StringIO()
        variants = token.getElementsByTagName('v')
        for variant in variants:
            for tag in variant.getElementsByTagName('g'):
                if tag.getAttribute('v').isupper() \
                 and tag.getAttribute('v') not in pos.getvalue():
                    pos.write(tag.getAttribute('v') + '_')
                    break
        pos_tag = pos.getvalue()
        return word, pos_tag.rstrip('_')
    except:
        return 'sent', 'sent'
