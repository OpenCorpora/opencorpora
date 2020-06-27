# -*- coding: utf-8 -*-
import re
from lxml import etree

class PossibleGroupFinder(object):
    
    def __init__(self, xml=None, db_config=None):
        if not xml and not db_config:
            raise Exception("Path to neither xml dump nor config.json provided")
        
        if xml:
            self._finder = XMLGroupFinder(xml)
        else:
            self._finder = DBGroupFinder(db_config)

    def find(self, pattern):
        return self._finder.find(pattern)


class XMLGroupFinder(object):

    class Finder(object):
        
        def __init__(self, pattern):
            self._reset()
            self.found = []

            self.pattern = []
            assert isinstance(pattern, (tuple, list))
            if len(pattern) < 2:
                raise Exception("Too short pattern provided")
            for el in pattern:
                self.pattern.append(re.compile(el, re.I))

        def _reset(self):
            self.context = []
            self.sent_id = None
            self.sent_fulltext = ''
            self.in_source = False

        def start(self, tag, attr):
            if tag == 'token':
                self.context.append((int(attr['id']), attr['text'].encode('utf-8')))
            elif tag == 'sentence':
                self.sent_id = int(attr['id'])
            elif tag == 'source':
                self.in_source = True

        def data(self, data):
            if self.in_source:
                self.sent_fulltext += data.encode('utf-8')

        def end(self, tag):
            if tag == 'sentence':
                self._reset()
            elif tag == 'token':
                self.check_current_context()
            elif tag == "source":
                self.in_source = False

        def close(self):
            pass

        def check_current_context(self):
            length = len(self.pattern)
            if len(self.context) < length:
                return
            for i, el in enumerate(self.pattern):
                if not re.match(el, self.context[i - length][1]):
                    return
            # context matched
            self.found.append(PossibleGroup(
                [x[0] for x in self.context[-length:]],
                [x[1] for x in self.context[-length:]],
                self.sent_id,
                self.sent_fulltext
            ))

    
    def __init__(self, xml_path):
        self.xml_path = xml_path

    def find(self, pattern):
        """ returns a list of tuples of token ids """
        finder = self.Finder(pattern)
        parser = etree.XMLParser(target = finder)
        etree.parse(self.xml_path, parser)
        return finder.found


class PossibleGroup(object):
    
    def __init__(self, ids, tokens, sentence_id, sentence_fulltext):
        self.ids = tuple(ids)
        self.tokens = tokens
        self.sentence_id = sentence_id
        self.sentence_fulltext = sentence_fulltext

    def __repr__(self):
        return 'PossibleGroup([{0}], "{1}", sentence_id = {2}, sentence_fulltext = "{3}")'.format(
            ','.join(map(str, self.ids)),
            ' '.join(self.tokens),
            self.sentence_id,
            self.sentence_fulltext
        )
