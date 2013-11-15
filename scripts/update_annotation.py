#!/usr/bin/env python2
# -*- coding: utf-8 -*-
import sys
sys.path.append('/corpus/python')
from Annotation import AnnotationEditor, VectorOfParses, ParsingVariant

CONFIG_PATH = "/corpus/config.ini"
CHANGESET_COMMENT = "Update tokens from dictionary"

DICT_REVISION = 390046
FILTER_OUT = None
CHANGE_LEMMA = None
GRAM_CHANGE = None

#FILTER_OUT = ("masc", )
GRAM_CHANGE = (("neut",), ("masc", ))
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
def change_lemma(variants, chg):
    out_variants = []
    for var in variants:
        out_variants.append(ParsingVariant(var.xml.replace('t="' + chg[0] + '"', 't="' + chg[1] + '"')))
    return out_variants
def update_token(dbh, token_id, revset_id, rev_text):
    dbh.execute("UPDATE tf_revisions SET is_last = 0 WHERE tf_id = {0} AND is_last = 1".format(token_id))
    dbh.execute("INSERT INTO tf_revisions VALUES(NULL, {0}, {1}, '{2}', 1)".format(revset_id, token_id, rev_text))
def delete_pending(dbh, revision):
    dbh.execute("DELETE FROM updated_tokens WHERE dict_revision = {0}".format(revision))
def update_annotation(editor):
    editor.create_revset(CHANGESET_COMMENT)
    for token_id, rev_text in get_tokens(editor._db_cursor, DICT_REVISION):
        rev_text = rev_text.encode('utf-8')
        if 'debug' in sys.argv:
            print("before:")
            print(rev_text)
        new_rev_text = rev_text
        if FILTER_OUT:
            parses = VectorOfParses(new_rev_text)
            parses.delete_parses_with_gramset(FILTER_OUT)
            new_rev_text = parses.to_xml()
        if GRAM_CHANGE:
            parses = VectorOfParses(new_rev_text)
            parses.replace_gramset(GRAM_CHANGE[0], GRAM_CHANGE[1])
            new_rev_text = parses.to_xml()
        if CHANGE_LEMMA:
            parses = VectorOfParses(new_rev_text)
            parses.parses = change_lemma(parses.parses, CHANGE_LEMMA)
            new_rev_text = parses.to_xml()
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
