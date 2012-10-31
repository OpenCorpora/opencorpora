#!/usr/bin/env python
# -*- coding: utf-8 -*-
import sys
import re
import time
import ConfigParser, MySQLdb
from MySQLdb.cursors import DictCursor 

# definitions

POOL_STATUS_IN_PROGRESS = 8
POOL_STATUS_READY       = 9
CHANGESET_COMMENT       = "Merge data from annotation pool #{0}"

GRAMMEMES_CONJUNCTION   = 1
GRAMMEMES_DISJUNCTION   = 2

def make_new_changeset(dbh, pool_id):
    timestamp = int(time.time())
    dbh.execute("INSERT INTO rev_sets VALUES(NULL, {0}, 0, '{1}')".format(timestamp, CHANGESET_COMMENT.format(pool_id)))
    return dbh.lastrowid
def set_pool_status(dbh, pool_id, status):
    dbh.execute("UPDATE morph_annot_pools SET status={0} WHERE pool_id={1} LIMIT 1".format(status, pool_id))
def get_moderated_pool(dbh):
    dbh.execute("SELECT pool_id, revision FROM morph_annot_pools WHERE status=7 LIMIT 1")
    pool = dbh.fetchone()
    if pool is not None:
        return pool['pool_id'], pool['revision']
    return None, None
def get_samples_and_answers(dbh, pool_id):
    dbh.execute("""SELECT sample_id, answer, status FROM morph_annot_moderated_samples WHERE sample_id IN
                (SELECT sample_id FROM morph_annot_samples WHERE pool_id={0})""".format(pool_id))
    results = dbh.fetchall()
    return results
def xml2vars(xml):
    lemma = re.findall('<tfr t="([^"]+)">', xml)
    variants = re.split('(?:<\/?v>)+', xml)
    return lemma[0], variants[1:-1]
def vars2xml(lemma, variants):
    out = ['<tfr t="', lemma, '">']
    for var in variants:
        out.append('<v>')
        out.append(var)
        out.append('</v>')
    out.append('</tfr>')
    return ''.join(out)
def update_vars(variants, gram_str):
    if '|' in gram_str and '&' in gram_str:
        return

    if '&' in gram_str:
        bind_type = GRAMMEMES_CONJUNCTION
        grammemes = gram_str.split('&')
    else:
        bind_type = GRAMMEMES_DISJUNCTION
        grammemes = gram_str.split('|')
    
    return filter_variants(variants, grammemes, bind_type)
def filter_variants(variants, grammemes, bind_type):
    out_variants = []
    for var in variants:
        flag_conj = True
        flag_disj = False
        for gram in grammemes:
            if var.find('<g v="' + gram + '"/>') > -1:
                if bind_type == GRAMMEMES_DISJUNCTION:
                    flag_disj = True
                    break
            else:
                if bind_type == GRAMMEMES_CONJUNCTION:
                    flag_conj = False
                    break
        if bind_type == GRAMMEMES_CONJUNCTION and flag_conj is True:
            out_variants.append(var)
        if bind_type == GRAMMEMES_DISJUNCTION and flag_disj is True:
            out_variants.append(var)

    return out_variants
def get_xml_by_sample_id(dbh, sample_id):
    dbh.execute("SELECT rev_id, rev_text FROM tf_revisions WHERE tf_id=(SELECT tf_id FROM morph_annot_samples WHERE sample_id={0} LIMIT 1) AND is_last = 1 LIMIT 1".format(sample_id))
    xml = dbh.fetchone()
    return xml['rev_text'], xml['rev_id']
def update_sample(dbh, sample_id, xml, changeset_id):
    dbh.execute("SELECT tf_id FROM morph_annot_samples WHERE sample_id={0} LIMIT 1".format(sample_id))
    res = dbh.fetchone()
    dbh.execute("UPDATE tf_revisions SET is_last=0 WHERE tf_id={0}".format(res['tf_id']))
    dbh.execute("INSERT INTO tf_revisions VALUES(NULL, {0}, {1}, '{2}', 1)".format(changeset_id, res['tf_id'], xml))
def generate_empty_parse(token):
    return ''.join(('<tfr t="', token, '"><v><l id="0" t="', token, '"><g v="UNKN"/></l></v></tfr>'))
def get_pool_grammemes(dbh, pool_id):
    dbh.execute("SELECT grammemes FROM morph_annot_pool_types WHERE type_id = (SELECT pool_type FROM morph_annot_pools WHERE pool_id={0} LIMIT 1) LIMIT 1".format(pool_id))
    row = dbh.fetchone()
    return re.split('@', row['grammemes'])
def process_pool(dbh, pool_id, revision):
    changeset_id = make_new_changeset(dbh, pool_id)
    set_pool_status(dbh, pool_id, POOL_STATUS_IN_PROGRESS)
    pool_grammemes = get_pool_grammemes(dbh, pool_id)
    for sample in get_samples_and_answers(dbh, pool_id):
        # do different things depending on status
        # do nothing if marked as misprint or as undisambiguatable :) homonymy
        if sample['status'] in (3, 4):
            continue
        
        old_xml, rev_id = get_xml_by_sample_id(dbh, sample['sample_id'])
        # do nothing if token has changed since pool creation
        if rev_id > revision:
            continue

        token, old_vars = xml2vars(old_xml)
        
        # generate empty parse if marked as 'no correct parses'
        if sample['status'] == 2:
            new_xml = generate_empty_parse(token)
        else:
            try:
                grammemes_ok_str = pool_grammemes[sample['answer']-1]
                new_xml = vars2xml(token, update_vars(old_vars, grammemes_ok_str))
            except IndexError:
                sys.stderr.write('Something went bad with pool #{0}, sample #{1}, exiting'.format(pool_id, sample['sample_id']))
                sys.exit()

        update_sample(dbh, sample['sample_id'], new_xml.encode('utf-8'), changeset_id)
    set_pool_status(dbh, pool_id, POOL_STATUS_READY)
def main():
    config = ConfigParser.ConfigParser()
    config.read(sys.argv[1])

    hostname = config.get('mysql', 'host')
    dbname   = config.get('mysql', 'dbname')
    username = config.get('mysql', 'user')
    password = config.get('mysql', 'passwd')

    db = MySQLdb.connect(hostname, username, password, dbname, use_unicode=True, charset="utf8")
    dbh = db.cursor(DictCursor)
    dbh.execute('START TRANSACTION')

    pool_id, revision = get_moderated_pool(dbh)
    if pool_id is not None:
        process_pool(dbh, pool_id, revision)
    db.commit()

if __name__ == "__main__":
    main()
