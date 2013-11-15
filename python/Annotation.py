# -*- coding: utf-8 -*-
import re
import time
import ConfigParser
import MySQLdb
from MySQLdb.cursors import DictCursor 

class AnnotationEditor(object):
    
    def __init__(self, config_path):
        config = ConfigParser.ConfigParser()
        config.read(config_path)

        hostname = config.get('mysql', 'host')
        dbname   = config.get('mysql', 'dbname')
        username = config.get('mysql', 'user')
        password = config.get('mysql', 'passwd')

        self._db_connect = MySQLdb.connect(hostname, username, password, dbname, use_unicode=True, charset="utf8")
        self._db_cursor = self._db_connect.cursor(DictCursor)
        self._db_cursor.execute('START TRANSACTION')

    def create_revset(self, comment=""):
        timestamp = int(time.time())
        self._db_cursor.execute("INSERT INTO rev_sets VALUES(NULL, {0}, 0, '{1}')".format(timestamp, comment))
        self.revset_id = self._db_cursor.lastrowid

    def commit(self):
        self._db_connect.commit()

class ParsingVariant(object):
    
    def __init__(self, xml):
        assert isinstance(xml, str)
        self.xml = xml

    def to_xml(self):
        return self.xml

    def has_all_grams(self, gramset):
        assert hasattr(gramset, '__iter__')
        for g in gramset:
            if self.xml.find('<g v="' + g + '"/>') == -1:
                return False
        return True

    def replace_gramset(self, search, replace):
        assert hasattr(search, '__iter__')
        assert hasattr(replace, '__iter__')

        search_seq = []
        replace_seq = []
        for gr in search:
            search_seq.append('<g v="' + gr + '"/>')
        for gr in replace:
            replace_seq.append('<g v="' + gr + '"/>')

        self.xml = self.xml.replace(''.join(search_seq), ''.join(replace_seq))

class VectorOfParses(object):
    
    def __init__(self, xml):
        assert isinstance(xml, str)
        l = re.findall('<tfr t="([^"]+)">', xml)
        self.token_text = l[0]
        self.parses = []
        variants = re.split('(?:<\/?v>)+', xml)
        for v in variants[1:-1]:
            self.parses.append(ParsingVariant(v))

    def to_xml(self):
        if len(self.parses) == 0:
            return generate_empty_parse(self.token_text)
        out = ['<tfr t="', self.token_text, '">']
        for parse in self.parses:
            out.append('<v>')
            out.append(parse.to_xml())
            out.append('</v>')
        out.append('</tfr>')
        return ''.join(out)

    def delete_parses_with_gramset(self, grams):
        if len(self.parses) == 0:
            return
        if isinstance(grams, str):
            grams = grams,

        new_parses = []
        for parse in self.parses:
            if not parse.has_all_grams(grams):
                new_parses.append(parse)
        self.parses = new_parses

    def replace_gramset(self, search_gram, replace_gram):
        if len(self.parses) == 0:
            return
        if isinstance(search_gram, str):
            search_gram = search_gram,
        if isinstance(replace_gram, str):
            search_gram = replace_gram,

        for parse in self.parses:
            parse.replace_gramset(search_gram, replace_gram)

    @staticmethod
    def generate_empty_parse(token):
        return ''.join(('<tfr t="', token, '"><v><l id="0" t="', token, '"><g v="UNKN"/></l></v></tfr>'))
