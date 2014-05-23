#!/usr/bin/env python2
# -*- coding: utf-8 -*-
import sys
sys.path.append('/corpus/python')
from Annotation import AnnotationEditor

CONFIG_PATH = "/corpus/config.ini"

def do_export(dbh):
    dbh.execute("""
        SELECT token_id, group_id
        FROM anaphora
        ORDER BY group_id, token_id
    """)
    
    for row in dbh.fetchall():
        print("{0}_{1}".format(row['token_id'], row['group_id']))

def main():
    editor = AnnotationEditor(CONFIG_PATH)
    do_export(editor.db_cursor)

if __name__ == "__main__":
    main()
