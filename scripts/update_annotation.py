#!/usr/bin/env python2
# -*- coding: utf-8 -*-
import sys
sys.path.append('/corpus/python')
from Annotation import AnnotationEditor

CONFIG_PATH = "/corpus/config.ini"
CHANGESET_COMMENT = "Update tokens from dictionary"

DICT_REVISION = 390730
FILTER_OUT = None
CHANGE_LEMMA = None
GRAM_CHANGE = None
DELETE_LEMMA = None

#FILTER_OUT = ("neut", )
#GRAM_CHANGE = (("PRTF", "impf", "tran", "past", "pssv"), ("ADJF", "Subx"))
#CHANGE_LEMMA = ("королева", "королёва", "Surn")
DELETE_LEMMA = 78304

def get_tokens(dbh, revision):
    dbh.execute("""
        SELECT DISTINCT token_id
        FROM updated_tokens t
        WHERE dict_revision = {0}
    """.format(revision))
    results = dbh.fetchall()
    out = []
    for row in results:
        out.append(row['token_id'])
    return out
def delete_pending(dbh, revision):
    dbh.execute("DELETE FROM updated_tokens WHERE dict_revision = {0}".format(revision))
def update_annotation(editor):
    editor.create_revset(CHANGESET_COMMENT)
    for token_id in get_tokens(editor.db_cursor, DICT_REVISION):
        ann = editor.get_token_by_id(token_id)
        old_rev_text = ann.to_xml()
        if 'debug' in sys.argv:
            print("before:")
            print(old_rev_text)
        if FILTER_OUT:
            ann.delete_parses_with_gramset(FILTER_OUT)
        if GRAM_CHANGE:
            ann.replace_gramset(GRAM_CHANGE[0], GRAM_CHANGE[1])
        if CHANGE_LEMMA:
            ann.replace_lemma(CHANGE_LEMMA[0], CHANGE_LEMMA[1], CHANGE_LEMMA[2])
        if DELETE_LEMMA:
            ann.delete_parses_with_lemma_id(DELETE_LEMMA)
        new_rev_text = ann.to_xml()
        if 'debug' in sys.argv:
            print("after:")
            print(new_rev_text)
            print
        if old_rev_text == new_rev_text:
            continue
        ann.save()
    delete_pending(editor.db_cursor, DICT_REVISION)

def main():
    editor = AnnotationEditor(CONFIG_PATH)
    update_annotation(editor)

    if 'debug' not in sys.argv:
        editor.commit()

if __name__ == "__main__":
    main()
