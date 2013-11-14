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

    @staticmethod
    def xml2vars(xml):
        lemma = re.findall('<tfr t="([^"]+)">', xml)
        variants = re.split('(?:<\/?v>)+', xml)
        return lemma[0], variants[1:-1]

    @staticmethod
    def vars2xml(lemma, variants):
        if len(variants) == 0:
            return generate_empty_parse(lemma)
        out = ['<tfr t="', lemma, '">']
        for var in variants:
            out.append('<v>')
            out.append(var)
            out.append('</v>')
        out.append('</tfr>')
        return ''.join(out)

    @staticmethod
    def generate_empty_parse(token):
        return ''.join(('<tfr t="', token, '"><v><l id="0" t="', token, '"><g v="UNKN"/></l></v></tfr>'))
