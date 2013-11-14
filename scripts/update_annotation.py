#!/usr/bin/env python2
# -*- coding: utf-8 -*-
import sys
import re
import time
import ConfigParser, MySQLdb
from MySQLdb.cursors import DictCursor 
sys.path.append('/corpus/python')
from Annotation import AnnotationEditor

CONFIG_PATH = "/corpus/config.ini"
CHANGESET_COMMENT = "Update tokens from dictionary"

DICT_REVISION = 389447
FILTER_OUT = None
CHANGE_LEMMA = None
GRAM_CHANGE = None

FILTER_OUT = ("plur", )
GRAM_CHANGE = (("Fixd",), ("Fixd", "Sgtm"))
#CHANGE_LEMMA = ("Википедия", "википедия")

def get_tokens(dbh, revision):
    dbh.execute("""
        SELECT token_id, rev_text
        FROM updated_tokens t
        LEFT JOIN tf_revisions r
            ON (t.token_id = r.tf_id)
        WHERE dict_revision = {0}
        AND is_last = 1
    """.format(revision))
    results = dbh.fetchall()
    out = []
    for row in results:
        out.append((row['token_id'], row['rev_text']))
    return out
def filter_out_gram(variants, gram):
    out_variants = []
    for var in variants:
        found = False
        for g in gram:
            if var.find('<g v="' + g + '"/>') > -1:
                found = True
                break
        if not found:
            out_variants.append(var)
    return out_variants
def change_gram(variants, chg):
    out_variants = []
    search_seq = []
    replace_seq = []

    for gr in chg[0]:
        search_seq.append('<g v="' + gr + '"/>')
    for gr in chg[1]:
        replace_seq.append('<g v="' + gr + '"/>')

    for var in variants:
        out_variants.append(var.replace(''.join(search_seq), ''.join(replace_seq)))
    return out_variants
def change_lemma(variants, chg):
    out_variants = []
    for var in variants:
        out_variants.append(var.replace('t="' + chg[0] + '"', 't="' + chg[1] + '"'))
    return out_variants
def update_token(dbh, token_id, revset_id, rev_text):
    dbh.execute("UPDATE tf_revisions SET is_last = 0 WHERE tf_id = {0} AND is_last = 1".format(token_id))
    dbh.execute("INSERT INTO tf_revisions VALUES(NULL, {0}, {1}, '{2}', 1)".format(revset_id, token_id, rev_text))
def delete_pending(dbh, revision):
    dbh.execute("DELETE FROM updated_tokens WHERE dict_revision = {0}".format(revision))
def update_annotation(editor):
    editor.create_revset()
    for token_id, rev_text in get_tokens(editor._db_cursor, DICT_REVISION):
        rev_text = rev_text.encode('utf-8')
        if 'debug' in sys.argv:
            print("before:")
            print(rev_text)
        new_rev_text = rev_text
        if FILTER_OUT:
            token, vs = editor.xml2vars(new_rev_text)
            new_rev_text = editor.vars2xml(token, filter_out_gram(vs, FILTER_OUT))
        if GRAM_CHANGE:
            token, vs = editor.xml2vars(new_rev_text)
            new_rev_text = editor.vars2xml(token, change_gram(vs, GRAM_CHANGE))
        if CHANGE_LEMMA:
            token, vs = editor.xml2vars(new_rev_text)
            new_rev_text = editor.vars2xml(token, change_lemma(vs, CHANGE_LEMMA))
        if 'debug' in sys.argv:
            print("after:")
            print(new_rev_text)
            print
        if rev_text == new_rev_text:
            continue
        update_token(editor._db_cursor, token_id, editor.revset_id, new_rev_text)
    delete_pending(editor._db_cursor, DICT_REVISION)

def main():
    editor = AnnotationEditor(CONFIG_PATH)
    update_annotation(editor)

    if 'debug' not in sys.argv:
        editor.commit()

if __name__ == "__main__":
    main()
