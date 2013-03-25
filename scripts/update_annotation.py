#!/usr/bin/env python
# -*- coding: utf-8 -*-
import sys
import time
import ConfigParser, MySQLdb
from MySQLdb.cursors import DictCursor 

CONFIG_PATH = "/corpus/config.ini"
CHANGESET_COMMENT = "Update tokens from dictionary"

DICT_REVISION = 388991
#TO_REPLACE = '<v><l id="95962" t="жанров"><g v="ADJS"/><g v="Qual"/><g v="masc"/><g v="sing"/></l></v>'
TO_REPLACE = '<g v="Qual"/>'
REPLACE_BY = ''

def create_revset(dbh):
    timestamp = int(time.time())
    dbh.execute("INSERT INTO rev_sets VALUES(NULL, {0}, 0, '{1}')".format(timestamp, CHANGESET_COMMENT))
    return dbh.lastrowid
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
def update_token(dbh, token_id, revset_id, rev_text):
    dbh.execute("UPDATE tf_revisions SET is_last = 0 WHERE tf_id = {0} AND is_last = 1".format(token_id))
    dbh.execute("INSERT INTO tf_revisions VALUES(NULL, {0}, {1}, '{2}', 1)".format(revset_id, token_id, rev_text))
def delete_pending(dbh, revision):
    dbh.execute("DELETE FROM updated_tokens WHERE dict_revision = {0}".format(revision))
def update_annotation(dbh):
    revset_id = create_revset(dbh)
    for token_id, rev_text in get_tokens(dbh, DICT_REVISION):
        rev_text = rev_text.encode('utf-8')
        new_rev_text = rev_text.replace(TO_REPLACE, REPLACE_BY)
        if rev_text == new_rev_text:
            continue
        update_token(dbh, token_id, revset_id, new_rev_text)
    delete_pending(dbh, DICT_REVISION)

def main():
    config = ConfigParser.ConfigParser()
    config.read(CONFIG_PATH)

    hostname = config.get('mysql', 'host')
    dbname   = config.get('mysql', 'dbname')
    username = config.get('mysql', 'user')
    password = config.get('mysql', 'passwd')

    db = MySQLdb.connect(hostname, username, password, dbname, use_unicode=True, charset="utf8")
    dbh = db.cursor(DictCursor)
    dbh.execute('START TRANSACTION')

    update_annotation(dbh)

    db.commit()

if __name__ == "__main__":
    main()
