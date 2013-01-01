#coding: utf-8

import sys
import os
from cStringIO import StringIO
import re
from time import clock


CONTEXT = ('w-1', 't-1', 'w+1', 't+1')
NUMB_AMB = 0
NUMB_TOKENS = 0


def read_corpus(inc):
    ss = []
    '''for line in inc.split('\n'):
        if line == 'sent':
            tokens = []
        elif line == '/sent':
            s = Sentence(tokens)
            ss.append(s)
        else:
            line = line.decode('utf-8')
            t = Token(line.split('\t'))
            tokens.append(t)
    c = Corpus(ss)
    return c
'''    
    for sent in inc.split('/sent')[:]:
        tokens = []
        sent = sent.lstrip('sent\n').rstrip('\n').split('\n')
        if sent == ['']:
            continue
        if isinstance(sent, list) and len(sent) > 1:
            for ltoken in sent:
                ltoken = ltoken.decode('utf-8').rstrip()
                t = Token(ltoken.split('\t'))
                tokens.append(t)
        else:
            ltoken = sent[0].decode('utf-8')
            try:
                t = Token(ltoken.split('\t'))
            except:
                #print sent
                raise Exception
                break
            #print ltoken.split('\t')[3::2]
            tokens.append(t)
        s = Sentence(tokens)
        ss.append(s)
    c = Corpus(ss)
    return c


def write_corpus(corpus, outstream):  # corpus is an instance of Corpus()
    for sent in corpus:
        outstream.write('sent\n')
        for token in sent:
            try:
                outstream.write(token.display() + '\n')
            except:
                outstream.write(token.display().encode('utf-8') + '\n')
        outstream.write('/sent\n')


def split_into_sent(text):
    return text.split('/sent')[:-1]


def get_pos_tags(line):
    postag = re.compile('^[A-Z]{4}$', re.UNICODE)
    line = line.split('\t')
    grammar = line[2:]
    pos_tags = []
    for item in grammar:
        for i in item.split(' '):
            if re.match(postag, i):
                if i not in pos_tags:
                    pos_tags.append(i)
    pos_tags = sorted(pos_tags)
    if len(pos_tags) > 1:
        return '_'.join(pos_tags).lstrip('_').rstrip('\r')
    elif len(pos_tags) == 0:
        print line
        raise Exception
    else:
        return pos_tags[0]


def get_list_amb(corpus):
    for sent in split_into_sent(corpus):
        tokens = process_table(sent)
        tokens.insert(0, 'sent')
        tokens.append('/sent')
        word_2, tag_2 = 'sent', 'sent'
        word_1, tag_1 = tokens[0][0], tokens[0][1]
        for token in tokens[1:-1]:
            word = token[0]
            tag = token[1]
            if len(tag_1.split('_')) > 1:
                print '\t'.join((word_2.decode('utf-8'), tag_2, \
                                 word_1.decode('utf-8'), tag_1, word.decode('utf-8'), tag))
            tag_2, tag_1, word_2, word_1 = tag_1, tag, word_1, word


def context_stats(corpus, ignore_numbers=True):
    result_dict = {}
    for tokens in corpus:
        word_2, tag_2 = 'sent', 'sent'
        try:
            if tokens[0].getPOStags() is not None:
                tag_1 = tokens[0].getPOStags()
            else:
                continue
        except:
            continue
        if ignore_numbers and tokens[0].text.isdigit():
            word_1 = '_N_'
        else:
            word_1 = tokens[0].text
        for token in tokens[1:]:
            try:
                if token.getPOStags() is not None:
                    tag = token.getPOStags()
                else:
                    tag = 'sent'
            except:
                tag = 'sent'
            if ignore_numbers and token.text.isdigit():
                word = '_N_'
            else:
                try:
                    word = token.text
                except:
                    word = 'sent'
            try:
                for t, c in zip(CONTEXT, [word_2, tag_2, word, tag]):
                    result_dict[tag_1].update(t, c)
                result_dict[tag_1].upfreq()
            except:
                tag_entry = TagStat()
                for t, c in zip(CONTEXT, [word_2, tag_2, word, tag]):
                    tag_entry.update(t, c)
                tag_entry.upfreq()
                result_dict[tag_1] = tag_entry
            tag_2, tag_1, word_2, word_1 = tag_1, tag, word_1, word
    stats = {}
    for tag in result_dict.keys():
        stats[tag] = result_dict[tag].stat
    return stats


def get_list_words_pos(corpus, ignore_numbers=True):
    result_dict = {}
    for sent in split_into_sent(corpus):
        tokens = process_table(sent)
        tokens.insert(0, 'sent')
        tokens.append('/sent')
        word_2, tag_2 = 'sent', 'sent'
        word_1, tag_1 = tokens[0][0], tokens[0][1]
        for token in tokens[1:-1]:
            tag = token[1]
            if ignore_numbers and token[0].isdigit():
                word = '_N_'
            else:
                word = token[0]
            if tag_1 in result_dict.keys():
                tag_entry = result_dict[tag_1]
                try:
                    tag_entry['t-1'][tag_2] += 1
                except:
                    tag_entry['t-1'][tag_2] = 1
                try:
                    tag_entry['w-1'][word_2] += 1
                except:
                    tag_entry['w-1'][word_2] = 1
                try:
                    tag_entry['t+1'][tag] += 1
                except:
                    tag_entry['t+1'][tag] = 1
                try:
                    tag_entry['w+1'][word] += 1
                except:
                    tag_entry['w+1'][word] = 1
                try:
                    tag_entry['freq'] += 1
                except:
                    tag_entry['freq'] = 1
            else:
                result_dict[tag_1] = dict(zip(('t-1', 'w-1', 't+1', 'w+1', 'freq'), \
                                              ({tag_2: 1}, {word_2: 1}, {tag: 1}, {word: 1}, 1)))
            tag_2, tag_1, word_2, word_1 = tag_1, tag, word_1, word
    return result_dict


def after_iter():
    return NUMB_AMB, NUMB_TOKENS


def process_table(sentence):
    to_return_list = []
    for line in sentence.split('\n'):
        line.decode('utf-8')
        if 'sent' not in line:
            if re.match(r'^\s*$', line):
                pass
            else:
                pos_tags_str = get_pos_tags(line)
                try:
                    line = line.split('\t')
                    to_return_list.append((line[1], pos_tags_str))
                except:
                    pass
    return to_return_list


def numb_amb_tokens(tokens):
    n = 0
    posamb = 0
    amb = 0
    for token in tokens[:]:
        try:
            tvars = len(token[1].split('_'))
            if tvars > 1:
                posamb += 1
            amb += tvars
            n += 1
        except:
            print token
    return posamb, n, amb


def numb_amb_corpus(corpus, numb_amb=0, numb_tokens=0, counter=numb_amb_tokens):
    tvars = 0
    for sent in split_into_sent(corpus):
        tokens = process_table(sent)
        if counter is not None and tokens != []:
            counts = counter(tokens)
            numb_amb += counts[0]
            numb_tokens += counts[1]
            tvars += counts[2]
    return numb_tokens, numb_amb, tvars


class Rule(object):

    def __init__(self, tagset, tag, context_type, context):
        self.tagset = tagset
        self.context = context
        self.tag = tag
        self.context_type = context_type
        for item in zip(CONTEXT, ('previous word', 'previous tag',
                                   'next word', 'next tag')):
            if context_type == item[0]:
                self.context_type = item[1]

    def display(self):
        return 'Change tag from %s to %s if %s is %s' % (self.tagset, self.tag, self.context_type, self.context)


class Corpus(set):

    def __init__(self, sentences):
        for i in range(len(sentences)):
            sent = Sentence(sentences.pop(0))
            sentences.append(sent)
        self.sents = sentences
        self.update(sentences)


class Sentence(tuple):

    def __init__(self, tokens):
        self._data = tokens
        tt = []
        tt.append('sent')
        try:
            for i in range(len(tokens)):
                token = tokens.pop(0)
                t = Token(token)
                tt.append(t)
        except:
            pass
        tt.append('sent')
        self.tokens = tt


class Token(tuple):

    def __init__(self, token):
        self.id = token[0]
        self.text = token[1]
        self.l_id = [i.split(' ')[0] for i in token[2:]]
        self.tagset = TagSet([' '.join(t.split(' ')[2:]) for t in token[2:]])

    def gettext(self):
        return self.text

    def gettagset(self):
        return self.tagset

    def getPOStags(self):
        return self.tagset.getPOStag()

    def display(self):
        return '\t'.join((self.id, self.text, self.tagset.display(self.l_id)))

    def has_ambig(self):
        if len(self.gettagset().getPOStag()) > 4:
            return True
        else:
            return False


class TagSet(set):

    def __init__(self, tags):
        self.set = []
        for tag in tags:
            self.set.append(Tag(tag))

    def display(self, l_id):
        return '\t'.join((' '.join(x) for x in zip(l_id, (t.text for t in self.set))))

    def getPOStag(self):
        pos = []
        for tag in self.set:
            pos.append(tag.getPOStag())
        if len(pos) > 1:
            return '_'.join(sorted(set(pos)))
            '''try:
                return '_'.join(sorted(set(pos)))
            except:
                return '_'
            '''
        else:
            return pos[0]

    def hasPOSamb(self):
        if len(self.getPOStag()) > 4:
            return True
        else:
            return False


class Tag(object):

    def __init__(self, tag):
        self.text = tag

    def isPOStag(self, t):
        pattern = re.compile('^[A-Z]{4}$', re.UNICODE)
        if pattern.match(t):
            return True
        else:
            return False

    def getPOStag(self):
        for t in self.text.split(' '):
            if self.isPOStag(t):
                return t


class TagStat(dict):

    def __init__(self):
        self.stat = dict(zip(list(CONTEXT) + ['freq'], ([{} for i in range(4)] + [0])))

    def update(self, ctype, context):
        for t in self.stat.keys():
            if t == ctype:
                try:
                    self.stat[t][context] += 1
                except:
                    self.stat[t][context] = 1

    def upfreq(self):
        self.stat['freq'] += 1


if __name__ == '__main__':
    inc = sys.stdin.read()
    s = clock()
    get_list_words_pos(inc)
    print clock() - s
    s = clock()
    outc = read_corpus(inc)
    s = clock()
    context_stats(outc)
    print clock() - s
    s = clock()
    get_list_words_pos(inc)
    print clock() - s
