# coding: utf-8

import re
import sys

_feature_lists = {'case': ('ablt', 'accs', 'acc2', 'datv',
                           'gent', 'loct', 'loc2', 'nomn'),
                  'number': ('sing', 'plur')}


# TODO: переделать представление признаков в правиле
def read_corpus(inc, ignore_numbers=True):
    '''Convert each line of text file into Token()'''
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
            print >> sys.stderr, t
            #raise Exception
            continue
        yield token


def write_corpus(corpus, outstream=sys.stdout):  # corpus is an instance of Corpus()
    for token in corpus:
        try:
            print >> outstream, token.display()
        except:
            print >> outstream, token.display().encode('utf-8')


def numb_amb_corpus(corpus):
    '''Return some numeric information about corpus'''
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


def feature_type(f):
    f = f.split()[0]
    pattern = re.compile('^[A-Z]{4}$', re.UNICODE)
    if pattern.match(f):
        return 'POS'
    for t, fs in _feature_lists.iteritems():
        if f in fs:
            return t
    return 'word'


def apply_rule(rule, corpus, ignore_numbers=True, wsize=2, f=None):
    if not f:
        f = 'POS'
    _NULL_TOKEN = Token(('SENT', 'SENT'))
    s = [_NULL_TOKEN]
    rc = rule.context
    more = False
    if isinstance(rc[0], (set, tuple)):
        more = True
        context = zip(*rc)
    else:
        context = list(rc)
    for t in corpus:

        if t.orig_text != 'SENT':
            s.append(t)
            continue
        else:
            s.append(t)
            for i, token in enumerate(s[1:], 1):
                left = i - wsize
                right = i + wsize + 1

                if left < 0:
                    left = 0
                c = s[left:right]
                if right > len(s) - 1:
                    right = len(s) - 1
                    c = s[left:]

                if token.getFeature(f) == rule.tagset:

                    if not more:
                        try:
                            curr_context = list(list(x for x in enumerate([w.getById(rule.id) for w in c],
                                                              - (i - left)) if x[0] in rc)[0])
                        except:
                            curr_context = []
                    else:
                        try:
                            curr_context = [x for x in enumerate([w.getById(rule.id) for w in c],
                                                              - (i - left)) if x[0] in rc[0]]
                        except:
                            curr_context = []

                    #print >> sys.stderr, curr_context, context
                    if context == curr_context:
                        #print >> sys.stderr, 0
                        token.disambiguate(rule.tag)
                yield token
            s = [_NULL_TOKEN]
    else:
        for i, token in enumerate(s[1:], 1):
            left = i - wsize
            right = i + wsize + 1

            if left < 0:
                left = 0
            c = s[left:right]
            if right > len(s) - 1:
                right = len(s) - 1
                c = s[left:]

            if token.getFeature(f) == rule.tagset:
                if not more:
                    try:
                        curr_context = [x[0] for x in enumerate([w.getById(rule.id) for w in c],
                                                          - (i - left)) if x[0] in rc]
                        curr_context.append([x[1] for x in enumerate([w.getById(rule.id) for w in c],
                                                          - (i - left)) if x[0] in rc][0])
                        #print context, curr_context
                    except:
                        curr_context = []
                else:
                    try:
                        curr_context = [x for x in enumerate([w.getById(rule.id) for w in c],
                                                          - (i - left)) if x[0] in rc[0]]
                    except:
                        curr_context = []
                if context == curr_context:
                    token.disambiguate(rule.tag)
            yield token


def parse_rule(line):
    '''Convert line into Rule()'''
    TYPES = {'tag': 0, 'word': 1, 'other': 2, 'number': 2, 'case': 2}

    line = line.decode('utf-8')
    p = re.compile(u'.+(?= ->)')
    ambtag = p.findall(line)[0]
    p = re.compile(u'(?<=-> )(\w+)', re.UNICODE)
    tag = p.findall(line)[0]
    p = re.compile(u'(-?\d+)(?=:)')
    pos = p.findall(line)
    pos = tuple(int(x) for x in pos)
    if len(pos) < 2:
        pos = pos[0]
    p = re.compile(u'(?<=:)(\w+)')
    type = p.findall(line)[0]
    p = re.compile(u'(?u)(?<==)(\w+|,|.|:|;)[( #)&]')
    c = p.findall(line)

    if len(c) > 1:
        c = tuple(c)
    else:
        c = c[0]

    t = TYPES[type]
    r = Rule(ambtag, tag, (pos, c), t)
    return r


class Rule(object):

    def __init__(self, tagset, tag, context, cnum):
        TYPES = {0: 'tag', 1: 'word', 2: 'other'}
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

    def getFeature(self, f):
        return self.tagset.getFeature(f)

    def getPOStags(self):
        return self.tagset.getPOStag()

    def getNUMBtag(self):
        return self.tagset.getNUMBtag()

    def getCase(self):
        return self.tagset.getCase()

    def getById(self, i):
        if i == 'POS':
            return self.getPOStags()
        if i == 'word':
            return self.text
        return self.getFeature(i)

    def display(self):
        if self.orig_text != 'SENT':
            return '\t'.join((self.id, self.orig_text, self.tagset.display(self.l_id, self.ls))).encode('utf-8')
        else:
            return 'SENT'

    def has_ambig(self, f):
        if len(self.tagset.getFeature(f)) > 4:
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
        return '\t'.join((' '.join(x) for x in zip(l_id, ls, (' '.join(t.orig_text) for t in self.set))))

    def getFeature(self, f):
        fs = []
        for tag in self.set:
            ff = tag.getFeature(f)
            if ff:
                fs.append(ff)
        if len(fs) > 1:
            return ' '.join(sorted(set(fs)))
        else:
            try:
                return fs[0]
            except IndexError:
                pass

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

    def getNUMBtag(self):
        pos = []
        for tag in self.set:
            n = tag.getNUMBtag()
            if n:
                pos.append(n)
        if len(pos) > 1:
            return ' '.join(sorted(set(pos)))
        else:
            try:
                return pos[0]
            except:
                pass

    def getCase(self):
        pos = []
        for tag in self.set:
            n = tag.getCase()
            if n:
                pos.append(n)
        if len(pos) > 1:
            return ' '.join(sorted(set(pos)))
        else:
            try:
                return pos[0]
            except:
                pass

    def disambiguate(self, pos):
        try:
            #if self.hasPOSamb():
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
        self.orig_text = tag.split()

    def isPOStag(self, t):
        pattern = re.compile('^[A-Z]{4}$', re.UNICODE)
        if pattern.match(t):
            return True
        else:
            return False

    def getFeature(self, f):
        if f == 'POS':
            return self.getPOStag()
        for t in self.orig_text:
            if t in _feature_lists[f]:
                return t

    def getNUMBtag(self):
        nums = ('sing', 'plur')
        for t in self.orig_text:
            if t in nums:
                return t

    def getCase(self):
        case = ('nomn', 'gent', 'datv', 'accs',
                'ablt', 'loct', 'acc2', 'gen2')
        for t in self.orig_text:
            if t in case:
                return t

    def getPOStag(self):
        for t in self.orig_text:
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
    #print numb_amb_corpus(read_corpus(sys.stdin))
    r = parse_rule('ADVB NOUN -> ADVB | -1:tag=ADJF #')
    print r.display()
