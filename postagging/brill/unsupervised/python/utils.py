#coding: utf-8

import sys
from StringIO import StringIO
import re
from sets import Set


CONTEXT = ('w-1', 't-1', 'w+1', 't+1')


def split_into_sent(text):
    for sent in text.split('</sent>'):
        yield process_table(sent)


def get_pos_tags(line):
    line = line.split('\t')
    grammar = line[2:]
    pos_tags = []
    pos_tags_str = StringIO()
    for item in grammar:
        pos = item.split(' ')[0]
        if pos not in pos_tags:
            pos_tags.append(pos)
    pos_tags.sort()
    for tag in pos_tags:
            pos_tags_str.write(tag + '_')
    return pos_tags_str.getvalue().lstrip('_').rstrip('_')


def process_table(sentence):
    to_return_list = []
    for line in sentence.split('\n'):
        line.decode('utf-8')
        if 'sent' not in line:
            pos_tags_str = get_pos_tags(line)
            try:
                to_return_list.append((line[1], pos_tags_str))
            except:
                pass
        else:
            pass
    return to_return_list


class Rule(object):

    def __init__(self, tagset, tag, context_type, context):
        self.tagset = tagset
        self.context = context
        self.tag = tag
        for item in zip(CONTEXT, ('previous word', 'previous tag',
                                   'next word', 'next tag')):
            if context_type == item[0]:
                self.context_type = item[1]

    def display(self):
        return 'Change tag from %s to %s if %s is %s' % (self.tagset, self.tag, self.context_type, self.context)


class Corpus(Set):

    def __init__(self, sentences):
        pass


class Sentence(Set):

    def __init__(self):
        pass


class Token(Set):

    def __init__(self, text, tagset):
        self.text = text
        self.tagset = tagset


class TagSet(Set):

    def __init__(self, tags):
        self.set = tags.split(' ')

    def getPOStag(self):
        for tag in self.set:
            if tag.isPOStag():
                return tag
            break


class Tag(object):

    def __init__(self, tag):
        self.text = tag

    def isPOStag(self):
        pattern = re.compile('^[A-Z]{4}$', re.UNICODE)
        if pattern.search(self.text) is not None:
            return True
        else:
            return False


class TagStat(dict):

    def __init__(self):
        pass


if __name__ == '__main__':
    pass

