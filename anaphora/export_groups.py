#!/usr/bin/env python2
# -*- coding: utf-8 -*-
import sys
sys.path.append('/corpus/python')
from Annotation import AnnotationEditor

CONFIG_PATH = "/corpus/config.ini"
STR_NONE = 'NONE'
STR_ALL = 'ALL'


def choose_annotators(dbh, only_moderated):
    moderators = {}
    if only_moderated:
        dbh.execute("""
            SELECT book_id, old_syntax_moder_id
            FROM books
            WHERE syntax_on > 0
        """)
        res = dbh.fetchall()
        for row in res:
            moderators[row['book_id']] = row['old_syntax_moder_id']
    dbh.execute("""
        SELECT user_id, book_id
        FROM anaphora_syntax_annotators
        WHERE status = 2
        ORDER BY book_id, user_id
    """)
    annotators = {}
    for row in dbh.fetchall():
        if row['book_id'] not in annotators:
            if row['book_id'] in moderators:
                annotators[row['book_id']] = moderators[row['book_id']]
            elif not only_moderated:
                annotators[row['book_id']] = row['user_id']
    return annotators

def export_simple_groups(dbh, annotators):
    groups = get_simple_groups(dbh, annotators, include_dummy=True)
    for gid, group in sorted(groups.items()):
        head_str = group['head']
        if group['marks'] == 'bad':
            continue
        elif group['marks'] == 'no head':
            head_str = STR_NONE
        elif group['marks'] == 'all':
            head_str = STR_ALL
        print("{4}\t{0}\t{1}\t{2}\t{3}".format(
            gid, ','.join(map(str, group['tokens'])), head_str, group['type'], group['book_id'])
        )

def get_simple_groups(dbh, annotators, include_dummy=False):
    groups = {}
    q = """
        SELECT group_id, group_type, user_id, head_id, book_id, token_id, marks
        FROM anaphora_syntax_groups g
            JOIN anaphora_syntax_groups_simple gs
                USING (group_id)
            LEFT JOIN tokens tf
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
                'tokens': [row['token_id']],
                'marks': row['marks'],
                'book_id': row['book_id']   # we expect they are all the same
            }
    return groups

def export_complex_groups(dbh, annotators):
    print("COMPLEX")
    groups = get_complex_groups(dbh, annotators)
    for gid, group in sorted(groups.items()):
        head_str = group['head']
        if group['marks'] == 'bad':
            continue
        elif group['marks'] == 'no head':
            head_str = STR_NONE
        elif group['marks'] == 'all':
            head_str = STR_ALL
        print("{4}\t{0}\t{1}\t{2}\t{3}".format(
            gid, ','.join(map(str, sorted(group['tokens']))), head_str, group['type'], group['book_id']
        ))

def get_complex_groups(dbh, annotators):
    simple = get_simple_groups(dbh, annotators, include_dummy=True)

    groups = {}
    dbh.execute("""
        SELECT parent_gid, child_gid, group_type, head_id, user_id, marks
        FROM anaphora_syntax_groups_complex gc
            LEFT JOIN anaphora_syntax_groups g ON (gc.parent_gid = g.group_id)
        ORDER BY parent_gid, child_gid
    """)
    for row in dbh.fetchall():
        if row['parent_gid'] not in groups:
            groups[row['parent_gid']] = {
                'head': row['head_id'],
                'type': row['group_type'],
                'children': [row['child_gid']],
                'user_id' : row['user_id'],
                'tokens': set(),
                'book_id': 0,
                'marks': row['marks']
            }
        else:
            groups[row['parent_gid']]['children'].append(row['child_gid'])

    # remove groups by other annotators
    gids = groups.keys()
    for gid in gids:
        if not check_subgroups(gid, simple, groups):
            del groups[gid]
    # add list of tokens and book id
    for gid in groups:
        update_token_list(groups[gid], simple, groups)
        assign_book_id(groups[gid], simple, groups)
    # add head token id
    for gid in groups:
        groups[gid]['head'] = get_head_token_id(groups[gid]['head'], simple, groups)

    return groups

def check_subgroups(gid, simple_groups, complex_groups):
    if gid in complex_groups:
        for child_id in complex_groups[gid]['children']:
            if not check_subgroups(child_id, simple_groups, complex_groups):
                return False
        return True
    elif gid in simple_groups:
        return True
    else:
        return False

def assign_book_id(group, simple_groups, complex_groups):
    if group['book_id']:
        return
    for child_gid in group['children']:
        if child_gid in simple_groups:
            group['book_id'] = simple_groups[child_gid]['book_id']
            return
        elif child_gid in complex_groups:
            assign_book_id(complex_groups[child_gid], simple_groups, complex_groups)
            group['book_id'] = complex_groups[child_gid]['book_id']
        else:  
            raise KeyError("group #{0} not found".format(child_gid))

def update_token_list(group, simple_groups, complex_groups):
    if len(group['tokens']) > 0:
        return
    for child_gid in group['children']:
        if child_gid in simple_groups:
            group['tokens'].update(simple_groups[child_gid]['tokens'])
        elif child_gid in complex_groups:
            update_token_list(complex_groups[child_gid], simple_groups, complex_groups)
            group['tokens'].update(complex_groups[child_gid]['tokens'])
        else:  
            raise KeyError("group #{0} not found".format(child_gid))

def get_head_token_id(old_id, simple_groups, complex_groups):
    if old_id == 0:
        return 0
    elif old_id in complex_groups:
        return get_head_token_id(complex_groups[old_id]['head'], simple_groups, complex_groups)
    elif old_id in simple_groups:
        return simple_groups[old_id]['head']
    else:
        return 0   # sometimes head groups get deleted

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
