#!/usr/bin/env python2
# -*- coding: utf-8 -*-
import sys
from datetime import datetime
sys.path.append('../python')
from Annotation import AnnotationEditor

CONFIG_PATH = "../config.json"

def main():
    editor = AnnotationEditor(CONFIG_PATH)
    dbh = editor.db_cursor

    # all entity entries
    dbh.execute("""
    SELECT ne_entities.*, user_id, sent_id, GROUP_CONCAT(tag_id ORDER BY tag_id SEPARATOR ' ') AS tags
        FROM ne_entities 
            LEFT JOIN ne_paragraphs USING (annot_id)
            LEFT JOIN tokens ON start_token = tf_id
            LEFT JOIN ne_entity_tags USING (entity_id)
        GROUP BY entity_id
        ORDER BY sent_id, start_token, updated_ts
    """)
    results = dbh.fetchall()
    user_ids = sent_ids = set()
    for row in results:
        user_ids.add(str(row['user_id']))
        sent_ids.add(str(row['sent_id']))

    # collect users separately
    dbh.execute("""
    SELECT user_id, user_name, user_shown_name
        FROM users
        WHERE user_id IN ({0})
    """.format(", ".join(user_ids)))
    users_res = dbh.fetchall()
    users = {}
    for user in users_res:
        if len(user["user_shown_name"]) > 0:
            user_name = user["user_shown_name"]
        else:
            user_name = user["user_name"]
        users[user["user_id"]] = user_name

    # collect all tokens from required sentences
    dbh.execute("""
    SELECT *
        FROM tokens
        WHERE sent_id IN ({0})
        ORDER BY sent_id, pos
    """.format(", ".join(sent_ids)))
    sent_res = dbh.fetchall()
    sentences = {}
    for sent in sent_res:
        if sent["sent_id"] not in sentences:
            sentences[sent["sent_id"]] = []
        sentences[sent["sent_id"]].append(sent)

    # collect tag names
    dbh.execute("SELECT * FROM ne_tags")
    tags_res = dbh.fetchall()
    tags = {}
    for tag in tags_res:
        tags[tag["tag_id"]] = tag["tag_name"]

    # output
    for row in results:
        out = ""
        out += str(row["entity_id"]) + "\t"
        out += str(row["sent_id"]) + "\t"
        out += datetime.fromtimestamp(row["updated_ts"]).strftime("%b %d, %H:%M") + "\t"
        out += users[row["user_id"]] + "\t"
        for tag in row["tags"].split():
            out += tags[int(tag)] + " "
        out += "\t"
        tokens_all = tokens = ""
        ne_len = 0
        for tkn in sentences[row["sent_id"]]:
            txt = tkn["tf_text"] + " "
            # these are ne tokens
            if tkn["tf_id"] == row["start_token"] or (ne_len > 0 and ne_len < row["length"]):
                tokens += txt
                ne_len+=1
            # all tokens for context
            tokens_all += txt
        out += tokens + "\t"
        out += tokens_all + "\t"
        print out.encode('UTF-8')

    if 'debug' not in sys.argv:
        editor.commit()

if __name__ == "__main__":
    main()
