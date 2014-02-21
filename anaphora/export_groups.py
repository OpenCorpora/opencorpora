#!/usr/bin/env python2
# -*- coding: utf-8 -*-
import sys
sys.path.append('/corpus/python')
from Annotation import AnnotationEditor

CONFIG_PATH = "/corpus/config.ini"


def choose_annotators(dbh, only_moderated):
    moderators = {}
    if only_moderated:
        dbh.execute("""
            SELECT book_id, syntax_moder_id
            FROM books
            WHERE syntax_on > 0
        """)
        res = dbh.fetchall()
        for row in res:
            moderators[row['book_id']] = row['syntax_moder_id']
    dbh.execute("""
        SELECT user_id, book_id
        FROM syntax_annotators
        WHERE status = 2
        ORDER BY book_id, user_id
    """)
    annotators = {}
    for row in dbh.fetchall():
        if row['book_id'] not in annotators:
            if row['book_id'] in moderators:
                annotators[row['book_id']] = moderators[row['book_id']]
            else:
                annotators[row['book_id']] = row['user_id']
    return annotators

def export_simple_groups(dbh, annotators):
    groups = get_simple_groups(dbh, annotators, include_dummy=True)
    for gid, group in sorted(groups.items()):
        print("{0}\t{1}\t{2}\t{3}".format(gid, ','.join(map(str, group['tokens'])), group['head'], group['type']))

def get_simple_groups(dbh, annotators, include_dummy=False):
    groups = {}
    q = """
        SELECT group_id, group_type, user_id, head_id, book_id, token_id
        FROM syntax_groups g
            JOIN syntax_groups_simple gs
                USING (group_id)
            LEFT JOIN text_forms tf
                ON (gs.token_id = tf.tf_id)
            JOIN sentences USING (sent_id)
            JOIN paragraphs USING (par_id)
        """
    if not include_dummy:
        q += " WHERE group_type != 16 "
    q += " ORDER BY group_id, token_id"
    dbh.execute(q)
    for row in dbh.fetchall():
        if row['book_id'] not in annotators or annotators[row['book_id']] != row['user_id']:
            continue
        if row['group_id'] in groups:
            groups[row['group_id']]['tokens'].append(row['token_id'])
        else:
            groups[row['group_id']] = {
                'head': row['head_id'],
                'type': row['group_type'],
                'tokens': [row['token_id']]
            }
    return groups

def export_complex_groups(dbh, annotators):
    print("COMPLEX")
    groups = get_complex_groups(dbh, annotators)
    for gid, group in sorted(groups.items()):
        print("{0}\t{1}\t{2}\t{3}".format(gid, ','.join(map(str, sorted(group['tokens']))), group['head'], group['type']))

def get_complex_groups(dbh, annotators):
    valid_children = set()
    simple = get_simple_groups(dbh, annotators, include_dummy=True)
    for gid in simple:
        valid_children.add(gid)

    groups = {}
    dbh.execute("""
        SELECT parent_gid, child_gid, group_type, head_id
        FROM syntax_groups_complex gc
            LEFT JOIN syntax_groups g ON (gc.parent_gid = g.group_id)
        ORDER BY parent_gid, child_gid
    """)
    for row in dbh.fetchall():
        if row['child_gid'] not in valid_children:
            continue
        if row['parent_gid'] not in groups:
            groups[row['parent_gid']] = {
                'head': row['head_id'],
                'type': row['group_type'],
                'children': [row['child_gid']],
                'tokens': get_tokens_by_group(row['child_gid'], simple, groups)
            }
        else:
            groups[row['parent_gid']]['children'].append(row['child_gid'])
            groups[row['parent_gid']]['tokens'].extend(get_tokens_by_group(row['child_gid'], simple, groups))
    return groups

def get_tokens_by_group(gid, simple_groups, complex_groups):
    if gid in simple_groups:
        return simple_groups[gid]['tokens']
    if gid in complex_groups:
        return complex_groups[gid]['tokens']
    raise KeyError("group #{0} not found".format(gid))

def do_export(dbh, gtype, only_moderated):
    annotators = choose_annotators(dbh, only_moderated)
    if gtype != 'complex':
        export_simple_groups(dbh, annotators)
    if gtype != 'simple':
        export_complex_groups(dbh, annotators)

def main():
    editor = AnnotationEditor(CONFIG_PATH)
    only_moderated = False
    if len(sys.argv) < 2 or sys.argv[1] not in ['simple', 'complex', 'both']:
        sys.stderr.write("""Usage: {0} {{simple|complex|both}} [mod]\n\tmod: export only moderators' groups, otherwise first user's annotation for each text\n""".format(sys.argv[0]))
        sys.exit(1)
    if len(sys.argv) > 2 and sys.argv[2] == 'mod':
        only_moderated = True
    do_export(editor.db_cursor, sys.argv[1], only_moderated)

if __name__ == "__main__":
    main()
