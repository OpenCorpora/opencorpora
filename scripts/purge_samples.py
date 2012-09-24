#!/usr/bin/env python
# -*- coding: utf-8 -*-
import sys
import ConfigParser, MySQLdb
from MySQLdb.cursors import DictCursor 

def purge_samples(dbh):
    dbh.execute("DELETE FROM morph_annot_candidate_samples WHERE deleted = 1")
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
    purge_samples(dbh)
    db.commit()

if __name__ == "__main__":
    main()
