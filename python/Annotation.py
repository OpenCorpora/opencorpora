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
        self.db_cursor = self._db_connect.cursor(DictCursor)
        self.db_cursor.execute('START TRANSACTION')
        self.revset_id = None

    def create_revset(self, comment=""):
        timestamp = int(time.time())
        self.db_cursor.execute("INSERT INTO rev_sets VALUES(NULL, {0}, 0, '{1}')".format(timestamp, comment))
        self.revset_id = self.db_cursor.lastrowid

    def get_revset_id(self, comment=""):
        if not self.revset_id:
            self.create_revset(comment)
        return self.revset_id

    def sql(self, sql):
        self.db_cursor.execute(sql)

    def commit(self):
        self._db_connect.commit()

    def get_token_by_id(self, token_id):
        """ Get the current annotation of the token """
        self.db_cursor.execute("""
            SELECT rev_text FROM text_revisions WHERE tf_id={0} AND is_last = 1
        """.format(token_id))
        row = self.db_cursor.fetchone()
        token = AnnotatedToken(row['rev_text'].encode('utf-8'), token_id, editor=self)
        return token

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

    def replace_lemma(self, find, search, replace):
        assert isinstance(search, str)
        assert isinstance(replace, str)

        self.xml = self.xml.replace('t="' + search + '"', 't="' + replace + '"')

class AnnotatedToken(object):
    
    def __init__(self, tid, xml, editor=None):
        assert isinstance(xml, str)
        l = re.findall('<tfr t="([^"]+)">', xml)
        self.token_text = l[0]
        self.parses = []
        self._id = tid
        variants = re.split('(?:<\/?v>)+', xml)
        for v in variants[1:-1]:
            self.parses.append(ParsingVariant(v))
        self._editor = editor

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

    def replace_lemma(self, search, replace):
        for parse in self.parses:
            parse.replace_lemma(search, replace)

    def save(self):
        self.editor.sql("""
            UPDATE tf_revisions SET is_last = 0 WHERE tf_id = {0} AND is_last = 1
        """.format(self._id))
        self.editor.sql("""
            INSERT INTO tf_revisions VALUES(NULL, {0}, {1}, '{2}', 1)
        """.format(self.editor.get_revset_id(), self._id, self.to_xml()))

    @staticmethod
    def generate_empty_parse(token):
        return ''.join(('<tfr t="', token, '"><v><l id="0" t="', token, '"><g v="UNKN"/></l></v></tfr>'))
