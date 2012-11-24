#coding: utf-8

import sys
from StringIO import StringIO
import re
from sets import Set


CONTEXT = ('w-1', 't-1', 'w+1', 't+1')


def split_into_sent(text):
    return text.split('</sent>')


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


def get_list_words_pos(corpus):
    result_dict = {}
    for sent in split_into_sent(corpus):
        tokens = process_table(sent)
        tokens.insert(0, 'sent')
        tokens.append('/sent')
        word_2, tag_2 = 'sent', 'sent'
        word_1, tag_1 = tokens[1][0], tokens[1][1]
        for token in tokens[1:-1]:
            tag = token[1]
            word = token[0]
            if tag_1 in result_dict.keys():
                tag_entry = result_dict[tag_1]
                if tag_2 in tag_entry['t-1'].keys():
                    tag_entry['t-1'][tag_2] += 1
                else:
                    tag_entry['t-1'][tag_2] = 1
                if word_2 in tag_entry['w-1'].keys():
                    tag_entry['w-1'][word_2] += 1
                else:
                    tag_entry['w-1'][word_2] = 1
                if tag in tag_entry['t+1'].keys():
                    tag_entry['t+1'][tag] += 1
                else:
                    tag_entry['t+1'][tag] = 1
                if word in tag_entry['w+1'].keys():
                    tag_entry['w+1'][word] += 1
                else:
                    tag_entry['w+1'][word] = 1
                if 'freq' in tag_entry.keys():
                    tag_entry['freq'] += 1
                else:
                    tag_entry['freq'] = 1
            else:
                result_dict[tag_1] = dict(zip(('t-1', 'w-1', 't+1', 'w+1', 'freq'), \
                                              ({tag_2: 1}, {word_2: 1}, {tag: 1}, {word: 1}, 1)))
            tag_2, tag_1, word_2, word_1 = tag_1, tag, word_1, word
    return result_dict


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

