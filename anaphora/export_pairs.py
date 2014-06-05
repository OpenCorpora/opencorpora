#!/usr/bin/env python2
# -*- coding: utf-8 -*-
import sys
sys.path.append('/corpus/python')
from Annotation import AnnotationEditor

CONFIG_PATH = "/corpus/config.ini"

def do_export(dbh):
    dbh.execute("""
        SELECT token_id, group_id, book_id
        FROM anaphora
            LEFT JOIN tokens ON (anaphora.token_id = tokens.tf_id)
            JOIN sentences USING (sent_id)
            JOIN paragraphs USING (par_id)
        ORDER BY book_id, group_id, token_id
    """)
    
    for row in dbh.fetchall():
        print("{2}\t{0}\t{1}".format(row['token_id'], row['group_id'], row['book_id']))

def main():
    editor = AnnotationEditor(CONFIG_PATH)
    do_export(editor.db_cursor)

if __name__ == "__main__":
    main()
