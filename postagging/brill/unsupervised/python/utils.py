# coding: utf-8

from itertools import combinations
import re
import sys


def read_corpus(inc, ignore_numbers=True):
    global _NULL_TOKEN
    _NULL_TOKEN = Token(('SENT', 'SENT'))
    for sent in inc:
        sent = sent.rstrip().decode('utf-8')
        if not sent:
            continue
        if sent == u'sent' or sent == u'SENT':
            yield _NULL_TOKEN
            continue
        if sent == u'/sent':
            continue
        t = sent.split('\t')
        try:
            token = Token(t)
        except:
            print t
            #raise Exception
            continue
        yield token


def write_corpus(corpus, outstream=sys.stdout):  # corpus is an instance of Corpus()
    for token in corpus:
        try:
            print >> outstream, token.display()
        except:
            print >> outstream, token.display().encode('utf-8')


def context_stats(corpus, ignore_numbers=True,
                  wsize=2, join_context=True,
                  cf=2, fixed=False):

    _NULL_TOKEN = Token(('SENT', 'SENT'))
    result_dict = {}
    s = [_NULL_TOKEN]
    for t in corpus:

        if t.orig_text != 'SENT':
            s.append(t)
            continue
        else:
            s.append(t)
            for i, token in enumerate(s[1:], 1):
                tag_1 = token.getPOStags()
                left = i - wsize
                right = i + wsize + 1

                if left < 0:
                    left = 0
                if right > len(s) - 1:
                    right = len(s) - 1

                context = s[left:right]
                for j, t in enumerate(context, - (i - left)):

                    '''if not j:
                        continue'''

                    try:
                        result_dict[tag_1][0].update(j, t.getPOStags())
                    except:
                        result_dict[tag_1] = [TagStat(), TagStat(), 0]
                        result_dict[tag_1][0].update(j, t.getPOStags())
                    if t.text != 'SENT':
                        result_dict[tag_1][1].update(j, t.text)
                result_dict[tag_1][2] += 1

                if join_context: # переписать для разного количества конт.признаков
                    for t1, t2 in combinations(enumerate(context, - (i - left)), cf):
                        '''if not t1[0]:
                            continue
                        if not t2[0]:
                            continue'''
                        result_dict[tag_1][0].update((t1[0], t2[0]), \
                                                      (t1[1].getPOStags(), t2[1].getPOStags()))
            s = [_NULL_TOKEN]
    else:
        for i, token in enumerate(s[1:], 1):
                tag_1 = token.getPOStags()
                left = i - wsize
                right = i + wsize + 1

                if left < 0:
                    left = 0
                if right > len(s) - 1:
                    right = len(s) - 1

                context = s[left:right]
                for j, t in enumerate(context, - (i - left)):

                    '''if not j:
                        continue'''

                    try:
                        result_dict[tag_1][0].update(j, t.getPOStags())
                    except:
                        result_dict[tag_1] = [TagStat(), TagStat(), 0]
                        result_dict[tag_1][0].update(j, t.getPOStags())
                    if t.text != 'SENT':
                        result_dict[tag_1][1].update(j, t.text)
                result_dict[tag_1][2] += 1

                if join_context:
                    for t1, t2 in combinations(enumerate(context, - (i - left)), cf):
                        if not t1[0]:
                            continue
                        if not t2[0]:
                            continue
                        result_dict[tag_1][0].update((t1[0], t2[0]), \
                                                      (t1[1].getPOStags(), t2[1].getPOStags()))
    stats = {}
    for tag in result_dict.keys():
        stats[tag] = (result_dict[tag][0].stat, result_dict[tag][1].stat, result_dict[tag][2])
    return stats


def numb_amb_corpus(corpus):
    tokens = 0
    sents = 0
    numb_amb = 0.0
    tvars = 0.0
    for token in corpus:
        if token.text == 'SENT':
            sents += 1
            continue
        if token.has_ambig():
            numb_amb += 1
            #print token.getPOStags()
        tvars += len(token.getPOStags().split())
        tokens += 1
    return tokens, numb_amb, tvars, sents


class Rule(object):

    def __init__(self, tagset, tag, context, cnum):
        TYPES = {0: 'tag', 1: 'word'}
        # TEMPLATE: self.context = [{0: None}, {0: None}]
        self.tagset = tagset
        self.context = context
        self.tag = tag
        self.id = TYPES[cnum]
        self.ind = cnum

    def display(self):
        context_info = []
        if type(self.context[0]) == int:
            self.c = '%s:%s=%s' % (str(self.context[0]), self.id, self.context[1])
        else:
            for c in zip(*self.context):
                context_info.append('%s:%s=%s' % (str(c[0]), self.id, c[1]))
            self.c = '&'.join(context_info)
        return '%s -> %s | %s #' % (self.tagset, self.tag, self.c)


class Context(dict):

    def __init__(self, *args):
        dict.__init__(self, zip((0, 1), *args))
        self.tags = self.get(0)
        self.words = self.get(1)

    def display(self):
        print 'tags: %s & words: %s' % (str(self.tags), str(self.words))


class Token(tuple):

    def __init__(self, token, ignore_numbers=True):
        self.id = token[0]
        self.orig_text = token[1]
        self.text = self.orig_text
        if ignore_numbers and self.orig_text.isdigit():
            self.text = '_N_'
        self.l_id = [i.split(' ')[0] for i in token[2:]]
        self.ls = [i.split(' ')[1] for i in token[2:]]
        if self.id == 'SENT':
            self.tagset = TagSet(['SENT'])
        else:
            self.tagset = TagSet([' '.join(t.split(' ')[2:]) for t in token[2:]])

    def gettext(self):
        return self.orig_text

    def gettagset(self):
        return self.tagset

    def getPOStags(self):
        return self.tagset.getPOStag()

    def getByIndex(self, i):
        if i == 0:
            return self.getPOStags()
        return self.text

    def display(self):
        if self.orig_text != 'SENT':
            return '\t'.join((self.id, self.orig_text, self.tagset.display(self.l_id, self.ls))).encode('utf-8')
        else:
            return 'SENT'

    def has_ambig(self):
        if len(self.tagset.getPOStag()) > 4:
            return True
        else:
            return False

    def disambiguate(self, pos):
        self.tagset.disambiguate(pos)


class TagSet(set):

    def __init__(self, tags):
        self.set = []
        for tag in tags:
            self.set.append(Tag(tag))

    def display(self, l_id, ls):
        return '\t'.join((' '.join(x) for x in zip(l_id, ls, (t.orig_text for t in self.set))))

    def getPOStag(self):
        pos = []
        for tag in self.set:
            pos.append(tag.getPOStag())
        if len(pos) > 1:
            return ' '.join(sorted(set(pos)))
        else:
            try:
                return pos[0]
            except:
                pass

    def disambiguate(self, pos):
        try:
            if self.hasPOSamb():
                for tag in self.set[:]:
                    if pos in tag.orig_text:
                        pass
                    else:
                        self.set.remove(tag)
        except:
            pass

    def hasPOSamb(self):
        if len(self.getPOStag()) > 4:
            return True
        else:
            return False


class Tag(object):

    def __init__(self, tag):
        self.orig_text = tag

    def isPOStag(self, t):
        pattern = re.compile('^[A-Z]{4}$', re.UNICODE)
        if pattern.match(t):
            return True
        else:
            return False

    def getPOStag(self):
        for t in self.orig_text.split(' '):
            if self.isPOStag(t):
                return t


class TagStat(dict):

    def __init__(self):
        self.stat = {}

    def update(self, t, context):
        try:
            self.stat[t][context] += 1
        except:
            try:
                self.stat[t][context] = 1
            except:
                self.stat[t] = {context: 1}

    def upfreq(self):
        self.stat['freq'] += 1


def tokens(files):
    def t(f):
        f = read_corpus(f)[0]
        return (numb_amb_corpus(f)[0] - numb_amb_corpus(f)[1]) / numb_amb_corpus(f)[0]
    return [t(open(f, 'r').read()) for f in files]

if __name__ == '__main__':
    print numb_amb_corpus(read_corpus(sys.stdin))
    #print Rule('ADJF_NOUN', 'NOUN', ((-1, 1), ('PNCT', 'ADJF')), 0).display()
    #s = context_stats(read_corpus(sys.stdin), join_context=True)
    #for k in s:
        #print k, s[k]
        #print '\t'.join((str(k), str(s[k])))
