#!/usr/bin/env python
# -*- coding: utf-8 -*-
import sys
import re
import ConfigParser, MySQLdb
from MySQLdb.cursors import DictCursor 

# definitions

POOL_STATUS_IN_PROGRESS = 7
POOL_STATUS_READY       = 8
CHANGESET_COMMENT       = "Merge data from annotation pool #{0}"

def make_new_changeset(dbh, pool_id):
    #TODO: time = ?
    dbh.execute("INSERT INTO rev_sets VALUES(NULL, {0}, 0, {1})".format(time, CHANGESET_COMMENT.format(pool_id)))
    #TODO: return mysql_insert_id somehow
def set_pool_status(dbh, pool_id, status):
    dbh.execute("UPDATE morph_annot_pools SET status={0} WHERE pool_id={1} LIMIT 1".format(status, pool_id))
def get_moderated_pool(dbh):
    dbh.execute("SELECT pool_id FROM morph_annot_pools WHERE status=6 LIMIT 1")
    pool = dbh.fetchone()
    return pool['pool_id']
def get_samples_and_answers(dbh, pool_id):
    dbh.execute("""SELECT sample_id, answer, status FROM morph_annot_moderated_samples WHERE sample_id IN
                (SELECT sample_id FROM morph_annot_samples WHERE pool_id={0})""".format(pool_id))
    while True:
        sample = dbh.fetchone()
        if sample is None: break
        yield sample
def xml2vars(xml):
    lemma = re.findall('<tfr t="([^"]+)">', xml)
    variants = re.split('(?:<\/?v>)+', xml)
    return lemma, variants[1:-1]
def vars2xml(variants):
    pass
def update_vars(variants, gram_str):
    pass
def get_xml_by_sample_id(dbh, sample_id):
    pass
def update_sample(dbh, sample_id, xml, changeset_id):
    pass
def get_pool_grammemes(dbh, pool_id):
    dbh.execute("SELECT grammemes FROM morph_annot_pools WHERE pool_id={0} LIMIT 1".format(pool_id))
    row = dbh.fetchone()
    return re.split('@', row['grammemes'])
def process_pool(dbh, pool_id):
    changeset_id = make_new_changeset(dbh, pool_id)
    set_pool_status(dbh, pool_id, POOL_STATUS_IN_PROGRESS)
    pool_grammemes = get_pool_grammemes(dbh, pool_id)
    for sample in get_samples_and_answers(dbh, pool_id):
        # do different things depending on status
        # do nothing if marked as misprint or as undisambiguatable :) homonymy
        if sample['status'] in (3, 4):
            continue
        
        grammemes_ok_str = pool_grammemes[sample['answer']-1]
        old_xml = get_xml_by_sample_id(dbh, sample['sample_id'])
        token, old_vars = xml2vars(old_xml)
        
        # generate empty parse if marked as 'no correct parses'
        if sample['status'] == 2:
            new_xml = generate_empty_parse(token)
        else:
            new_xml = vars2xml(update_vars(old_vars, grammemes_ok_str))

        update_sample(dbh, sample['sample_id'], new_xml, changeset_id)
    set_pool_status(dbh, pool_id, POOL_STATUS_READY)
def main():
    config = ConfigParser.ConfigParser()
    config.read(sys.argv[1])

    hostname = config.get('mysql', 'host')
    dbname   = config.get('mysql', 'dbname')
    username = config.get('mysql', 'user')
    password = config.get('mysql', 'passwd')

    db = MySQLdb.connect(hostname, username, password, dbname, use_unicode=True)
    dbh = db.cursor(DictCursor)
    dbh.execute('SET NAMES utf8')
    dbh.execute('START TRANSACTION')

    pool_id = get_moderated_pool(dbh)
    if pool_id is not None:
        print pool_id
        process_pool(dbh, pool_id)
    #db.commit()

if __name__ == "__main__":
    main()
