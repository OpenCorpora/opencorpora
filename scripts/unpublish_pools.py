#!/usr/bin/env python
# -*- coding: utf-8 -*-
import sys
import time
import ConfigParser, MySQLdb
from MySQLdb.cursors import DictCursor 

POOL_STATUS_PUBLISHED = 3
POOL_STATUS_UNPUBLISHED = 4

def check_pools(dbh):
    dbh.execute("""
        SELECT pool_id
        FROM morph_annot_pools
        WHERE status = {0}
        AND pool_id NOT IN (
            SELECT DISTINCT pool_id
            FROM morph_annot_instances
            LEFT JOIN morph_annot_samples USING (sample_id)
            LEFT JOIN morph_annot_pools USING (pool_id)
            WHERE status = {0}
            AND answer = 0
        )
    """.format(POOL_STATUS_PUBLISHED))

    for pool in dbh.fetchall():
        set_pool_status(dbh, pool['pool_id'], POOL_STATUS_UNPUBLISHED)
def set_pool_status(dbh, pool_id, status):
    dbh.execute("UPDATE morph_annot_pools SET status={0}, updated_ts={2} WHERE pool_id={1} LIMIT 1".format(status, pool_id, int(time.time())))
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

    check_pools(dbh)
    db.commit()

if __name__ == "__main__":
    main()
