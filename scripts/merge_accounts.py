#!/usr/bin/env python2
# -*- coding: utf-8 -*-
import sys
sys.path.append('/corpus/python')
from Annotation import AnnotationEditor

CONFIG_PATH = "/corpus/config.json"


def merge(dbh, id1_, id2_):
    """ id1_ is primary uid """
    id1 = int(id1_)
    id2 = int(id2_)
    dbh.execute("SELECT user_id FROM users WHERE user_id IN ({}, {})".format(id1, id2))
    assert len(dbh.fetchall()) == 2

    for query in (
        "UPDATE anaphora_syntax_annotators SET user_id = {} WHERE user_id = {}",
        "UPDATE anaphora_syntax_groups SET user_id = {} WHERE user_id = {}",
        "UPDATE dict_errata_exceptions SET author_id = {} WHERE author_id = {}",
        "UPDATE dict_revisions_ugc SET user_id = {} WHERE user_id = {}",
        "UPDATE dict_revisions_ugc SET moder_id = {} WHERE moder_id = {}",
        "UPDATE morph_annot_click_log SET user_id = {} WHERE user_id = {}",
        "UPDATE morph_annot_comments SET user_id = {} WHERE user_id = {}",
        "UPDATE morph_annot_instances SET user_id = {} WHERE user_id = {}",
        "UPDATE morph_annot_moderated_samples SET user_id = {} WHERE user_id = {}",
        "UPDATE morph_annot_pools SET author_id = {} WHERE author_id = {}",
        "UPDATE morph_annot_pools SET moderator_id = {} WHERE moderator_id = {}",
        "UPDATE morph_annot_rejected_samples SET user_id = {} WHERE user_id = {}",
        "UPDATE ne_event_log SET user_id = {} WHERE user_id = {}",
        "UPDATE ne_paragraphs SET user_id = {} WHERE user_id = {}",
        "UPDATE ne_paragraph_comments SET user_id = {} WHERE user_id = {}",
        "UPDATE rev_sets SET user_id = {} WHERE user_id = {}",
        "UPDATE sentence_authors SET user_id = {} WHERE user_id = {}",
        "UPDATE sentence_comments SET user_id = {} WHERE user_id = {}",
        "UPDATE sources SET user_id = {} WHERE user_id = {}",
        "UPDATE sources_comments SET user_id = {} WHERE user_id = {}",
        "UPDATE sources_status SET user_id = {} WHERE user_id = {}",
        "UPDATE user_teams SET creator_id = {} WHERE creator_id = {}",
    ):
        dbh.execute(query.format(id1, id2))

    # user stats and rating will autoupdate later
    # user groups must be tweaked manually
    # user achievements EXCEPT THE DOG will autoupdate

    dbh.execute("INSERT INTO user_aliases (primary_uid, alias_uid) VALUES ({}, {})".format(id1, id2))


def main():
    editor = AnnotationEditor(CONFIG_PATH)
    merge(editor.db_cursor, sys.argv[1], sys.argv[2])
    editor.commit()


if __name__ == "__main__":
    main()
