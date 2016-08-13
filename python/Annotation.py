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
        self._revset_id = None

    def create_revset(self, comment=""):
        timestamp = int(time.time())
        self.db_cursor.execute("INSERT INTO rev_sets VALUES(NULL, {0}, 0, '{1}')".format(timestamp, comment))
        self._revset_id = self.db_cursor.lastrowid

    def get_revset_id(self, comment=""):
        if not self._revset_id:
            self.create_revset(comment)
        return self._revset_id

    def get_insert_id(self):
        return self.db_cursor.lastrowid

    def sql(self, sql, ret=False):
        self.db_cursor.execute(sql)
        if ret:
            return self.db_cursor.fetchall()

    def commit(self):
        self._db_connect.commit()

    def get_token_by_id(self, token_id):
        """ Get the current annotation of the token """
        self.db_cursor.execute("""
            SELECT rev_text FROM tf_revisions WHERE tf_id={0} AND is_last = 1
        """.format(token_id))
        row = self.db_cursor.fetchone()
        return AnnotatedToken(token_id, row['rev_text'].encode('utf-8'), editor=self)

    def find_lexeme_by_lemma(self, lemma, grammemes=None):
        if grammemes is None:
            grammemes = tuple()
        elif isinstance(grammemes, str):
            grammemes = grammemes,

        self.db_cursor.execute("""
            SELECT lemma_id AS lid, lemma_text AS ltext
            FROM dict_lemmata
            WHERE lemma_text = '{0}'
            AND deleted = 0
        """.format(lemma))
        rows = self.db_cursor.fetchall()
        lexemes = []
        #TODO: the is_last field is lacking
       
        for row in rows:
            self.db_cursor.execute("""
                SELECT rev_text
                FROM dict_revisions
                WHERE lemma_id = {0}
                AND is_last = 1
                LIMIT 1
            """.format(row['lid']))
            lrow = self.db_cursor.fetchone()
            l = Lexeme(row['ltext'].encode('utf-8'), row['lid'], lrow['rev_text'].encode('utf-8'), editor=self)
            if l.has_all_gram(grammemes):
                lexemes.append(l)
        return lexemes
    
    #finds a lexeme by its lemma and using a regular expression for its grammemes
    def find_lexeme_by_lemma_gr_regex(self, lemma, grammemes):
       
        self.db_cursor.execute("""
            SELECT lemma_id AS lid, lemma_text AS ltext
            FROM dict_lemmata
            WHERE lemma_text = '{0}'
            AND deleted = 0
        """.format(lemma))
        rows = self.db_cursor.fetchall()
        lexemes = []
 
        for row in rows:
            self.db_cursor.execute("""
                SELECT rev_text
                FROM dict_revisions
                WHERE lemma_id = {0}
                AND rev_text like '{1}'
                AND is_last = 1
                LIMIT 1
            """.format(row['lid'], grammemes))
            lrow = self.db_cursor.fetchone()
            l = Lexeme(row['ltext'].encode('utf-8'), row['lid'], lrow['rev_text'].encode('utf-8'), editor=self)
            lexemes.append(l)
        return lexemes
    
    #tried to combine all the conditions in a single query but it doesn't work faster
    def find_lexeme_by_lemma_gr_regex_single_query(self, lemma, grammemes):
        lexemes = []
        
        self.db_cursor.execute("""
            SELECT lem.lemma_id AS lid, lem.lemma_text AS ltext, rev.rev_text AS rev_text
            FROM dict_lemmata lem join dict_revisions rev on lem.lemma_id = rev.lemma_id
            WHERE lem.lemma_text = '{0}'
            AND rev.is_last = 1
            AND rev.rev_text like '{1}'
            AND lem.deleted = 0
        """.format(lemma, grammemes))
        rows = self.db_cursor.fetchall()
        
        for row in rows:
            l = Lexeme(row['ltext'].encode('utf-8'), row['lid'], row['rev_text'].encode('utf-8'), editor=self)
            lexemes.append(l)
            
        return lexemes
    
    #finds a lexeme with regular expressions for its lemma and grammemes
    def find_lexeme_by_lemma_regex_gr_regex(self, lemma, grammemes):
        #TODO: the is_last field is lacking
        req = """
            SELECT lem.lemma_id AS lid, lem.lemma_text AS ltext, revs.rev_text AS rev_text
            FROM dict_lemmata lem join dict_revisions revs on lem.lemma_id = revs.lemma_id
            WHERE lem.lemma_text like '{0}'
            AND lem.deleted = 0
            AND revs.rev_text like '{1}'
            AND revs.is_last = 1
        """.format(lemma, grammemes)
        
        self.db_cursor.execute(req)

        
        rows = self.db_cursor.fetchall()
        lexemes = []
        
        for row in rows:
            l = Lexeme(row['ltext'].encode('utf-8'), row['lid'], row['rev_text'].encode('utf-8'), editor=self)
            lexemes.append(l)
        return lexemes
    
    def add_link(self, from_id, to_id, link_type, revset_id = None, comment = "", is_dry_run = False):
        if not self.is_correct_id(from_id) or not self.is_correct_id(to_id):
            raise Exception('Negative ids specified: %s %s' % (from_id, to_id))
        if not revset_id:
            revset_id = self.get_revset_id(comment + '#add_link')
        
        insert_dict_links = "INSERT INTO dict_links VALUES(NULL, {0}, {1}, {2})".format(from_id, to_id, link_type)
        
        if not is_dry_run: 
            self.db_cursor.execute(insert_dict_links)
        else:
            print(insert_dict_links)
        
        insert_dict_links_revisions = "INSERT INTO dict_links_revisions VALUES(NULL, {0}, {1}, {2}, {3}, 1)".format(revset_id, from_id, to_id, link_type)
        
        if not is_dry_run:  
            self.db_cursor.execute(insert_dict_links_revisions)
        else:
            print(insert_dict_links_revisions)
        
        self.db_cursor.execute(insert_dict_links_revisions)  
        
        
        if not is_dry_run:
            self.commit()
        
    def del_link(self, link_id, revset_id = None, comment = "", is_dry_run = False):
        existing_link = self.find_link_by_id(link_id)
        if existing_link is None:
            raise Exception('No such link found: %s' % (link_id))
        if not revset_id:
            revset_id = self.get_revset_id(comment + '#del_link')
        
        insert_dict_links_revisions = "INSERT INTO dict_links_revisions VALUES(NULL, {0}, {1}, {2}, {3}, 0)".\
                                format(revset_id, existing_link['lemma1_id'], 
                                       existing_link['lemma2_id'],
                                       existing_link['link_type'])
        
        if not is_dry_run:
            self.db_cursor.execute(insert_dict_links_revisions)
        else:
            print(insert_dict_links_revisions)  
            
        delete_links = "DELETE FROM dict_links WHERE link_id={0} LIMIT 1".format(link_id)
        
        if not is_dry_run:
            self.db_cursor.execute(delete_links)  
        else:
            print(delete_links)    
        
        if not is_dry_run:         
            self.commit()
        
        
    def find_link_by_id(self, link_id):
        self.db_cursor.execute("SELECT * FROM dict_links WHERE link_id={0} LIMIT 1".format(link_id))
        return self.db_cursor.fetchone()
        
        
    def is_correct_id(self, id_to_check):
        return id_to_check > 0


class ParsingVariant(object):
    
    def __init__(self, xml):
        assert isinstance(xml, str)
        self.xml = xml
        self.lemma_id = int(re.search('<l id="([0-9]+)"', self.xml).group(1))

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

    def replace_lemma(self, search, replace):
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
            return self.generate_empty_parse(self.token_text)
        out = ['<tfr t="', self.token_text, '">']
        for parse in self.parses:
            out.append('<v>')
            out.append(parse.to_xml())
            out.append('</v>')
        out.append('</tfr>')
        return ''.join(out)

    def delete_parses_with_lemma_id(self, lemma_id):
        assert isinstance(lemma_id, int)
        new_parses = []
        for parse in self.parses:
            if parse.lemma_id != lemma_id:
                new_parses.append(parse)
        self.parses = new_parses

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

    def replace_lemma(self, search, replace, gram_restriction=None):
        if gram_restriction and isinstance(gram_restriction, str):
            gram_restriction = gram_restriction,
        for parse in self.parses:
            if not gram_restriction or parse.has_all_grams(gram_restriction):
                parse.replace_lemma(search, replace)

    def save(self, comment=""):
        self._editor.sql("""
            UPDATE tf_revisions SET is_last = 0 WHERE tf_id = {0} AND is_last = 1
        """.format(self._id))
        self._editor.sql("""
            INSERT INTO tf_revisions VALUES(NULL, {0}, {1}, '{2}', 1)
        """.format(self._editor.get_revset_id(comment), self._id, self.to_xml()))

    @staticmethod
    def generate_empty_parse(token):
        return ''.join(('<tfr t="', token, '"><v><l id="0" t="', token, '"><g v="UNKN"/></l></v></tfr>'))


class Lexeme(object):
    
    def __init__(self, lemma, id_=0, xml=None, editor=None):
        self.lemma = {
            'text': lemma,
            'gram': []
        }
        self.forms = []
        self.updated_forms = set()
        self._id = id_
        if xml:
            self._parse_rev_xml(xml, lemma)
        self._editor = editor

    def _parse_rev_xml(self, xml, lemma):
        lemma_info = re.findall('<l t="([^"]+)">(.+)<\/l>', xml)
        assert len(lemma_info) == 1
        assert lemma.replace('ё', 'е') == lemma_info[0][0].replace('ё', 'е')
        for gram in re.findall('<g v="([^"]+)"/?>', lemma_info[0][1]):
            self.lemma['gram'].append(gram)
        for form_xml in re.findall('<f .+?\/f>', xml):
            ftext = re.findall('<f t="([^"]+)">', form_xml)[0]
            self.forms.append({'text': ftext, 'gram': []})
            for gram in re.findall('<g v="([^"]+)"/?>', form_xml):
                self.forms[-1]['gram'].append(gram)

    def get_id(self):
        return self._id

    def to_xml(self):
        xml = ['<dr>', '<l t="', self.lemma['text'], '">']
        for gr in self.lemma['gram']:
            xml.append('<g v="' + gr + '"/>')
        xml.append('</l>')
        for form in self.forms:
            xml.append('<f t="' + form['text'] + '">')
            for gr in form['gram']:
                xml.append('<g v="' + gr + '"/>')
            xml.append('</f>')
        xml.append('</dr>')
        return ''.join(xml)

    def has_form(self, form_text, grammemes=None):
        if grammemes is None:
            grammemes = tuple()
        elif isinstance(grammemes, str):
            grammemes = grammemes,
        
        form_text = form_text.lower()

        for form in self.forms:
            if form['text'] != form_text:
                continue
            gram_ok = True
            for gr in grammemes:
                try:
                    form['gram'].index(gr)
                except ValueError:
                    gram_ok = False
            if gram_ok:
                return True
        return False

    def has_all_gram(self, grammemes):
        for gr in grammemes:
            try:
                self.lemma['gram'].index(gr)
            except ValueError:
                return False
        return True

    def add_form(self, form_text, grammemes):
        assert isinstance(form_text, str)
        assert hasattr(grammemes, '__iter__')
        self.forms.append({'text': form_text, 'gram': grammemes})
        self.updated_forms.add(form_text)

    def add_lemma_gram(self, grammemes):
        if isinstance(grammemes, str):
            grammemes = grammemes,

        for gram in grammemes:
            if gram not in self.lemma['gram']:
                self.lemma['gram'].append(gram)

        self.updated_forms.update(set([x['text'] for x in self.forms]))

    def remove_lemma_gram(self, grammemes):
        if isinstance(grammemes, str):
            grammemes = grammemes,

        self.lemma['gram'] = [x for x in self.lemma['gram'] if x not in grammemes]
        self.updated_forms.update(set([x['text'] for x in self.forms]))

    def update_forms(self, rev_id):
        for form in self.updated_forms:
            self._editor.sql("""
                INSERT INTO updated_forms VALUES('{0}', {1})
            """.format(form, rev_id))

    def save(self, comment=""):
        # if the word is totally new (= has no id), add it
        if not self._id:
            self._editor.sql("""
                INSERT INTO dict_lemmata VALUES(NULL, '{0}', 0)
            """.format(self.lemma['text']))
            self._id = self._editor.get_insert_id()

        self._editor.sql("""
            UPDATE dict_revisions SET is_last=0 WHERE lemma_id = ?
        """.format(self._id))
        self._editor.sql("""
            INSERT INTO dict_revisions VALUES(NULL, {0}, {1}, '{2}', 0, 0, 1)
        """.format(self._editor.get_revset_id(comment), self._id, self.to_xml()))
        self.update_forms(self._editor.get_insert_id())
