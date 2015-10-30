#!/usr/bin/env python
import sys
import time
import ConfigParser, MySQLdb
from MySQLdb.cursors import DictCursor 

def update_rating(dbh, weights):
    dbh.execute("UPDATE users SET user_rating10 = 0")

    dbh.execute("""
        SELECT user_id, SUM(
            CASE complexity
                WHEN 0 THEN {0}
                WHEN 1 THEN {1}
                WHEN 2 THEN {2}
                WHEN 3 THEN {3}
                WHEN 4 THEN {4}
            END
        ) AS rating
        FROM users
        LEFT JOIN morph_annot_instances USING(user_id)
        LEFT JOIN morph_annot_samples USING(sample_id)
        LEFT JOIN morph_annot_pools p USING(pool_id)
        LEFT JOIN morph_annot_pool_types t ON (p.pool_type = t.type_id)
        GROUP BY user_id
        ORDER BY rating DESC
    """.format(*weights))

    users = dbh.fetchall()
    for user in users:
        if user['rating'] > 0:
            dbh.execute("UPDATE users SET user_rating10 = {0} WHERE user_id = {1} LIMIT 1".format(int(user['rating']), user['user_id']))

def main():
    config = ConfigParser.ConfigParser()
    config.read(sys.argv[1])

    hostname = config.get('mysql', 'host')
    dbname   = config.get('mysql', 'dbname')
    username = config.get('mysql', 'user')
    password = config.get('mysql', 'passwd')

    weights = map(float, config.get('misc', 'morph_annot_rating_weights').split(','))
    assert len(weights) == 5

    db = MySQLdb.connect(hostname, username, password, dbname, use_unicode=True, charset="utf8")
    dbh = db.cursor(DictCursor)
    dbh.execute('START TRANSACTION')
    update_rating(dbh, weights)

    db.commit()

if __name__ == "__main__":
    main()
