#!/usr/bin/env python
# -*- coding: utf-8 -*-
import sys
import time
import ConfigParser
import MySQLdb
from MySQLdb.cursors import DictCursor 

class BadgeAssigner:
    """Holds db connection"""
    
    def __init__(self, dbh):
        self.dbh = dbh
        self.badgeDependency = {
            1: [2, 3],
            2: [3]
        }
        self.numOfGroups = 10

    def checkNumOfSamples(self, user_id, expected_num, max_divergence=None, max_error_rate=None):
        """Divergence and error rate should be indicated in per cent"""

        if max_divergence is not None:
            total = None
            diverged = None
            self.dbh.execute("SELECT param_id, param_value FROM user_stats WHERE user_id={0} AND param_id IN (33, 34) LIMIT 2".format(user_id))
            res = self.dbh.fetchall()
            for r in res:
                if r['param_id'] == 33:
                    total = r['param_value']
                else:
                    diverged = r['param_value']

            return (total >= expected_num and float(diverged)/total <= float(max_divergence)/100)
        else:
            self.dbh.execute("SELECT COUNT(*) AS cnt FROM morph_annot_instances WHERE user_id = {0} AND answer > 0".format(user_id))
            r = self.dbh.fetchone()
            return (r['cnt'] >= expected_num)
    
    def checkBadges(self):
        """Choose users to check"""

        ts = int(time.time()) / 60
        group = ts % self.numOfGroups;

        self.dbh.execute("SELECT user_id FROM users WHERE user_id % {0} = 0".format(group))
        res = self.dbh.fetchall()

        for r in res:
            self.checkUserBadges(r['user_id'])

    def checkUserBadges(self, user_id):
        """Check if any users have earned new badges
        We presume that the dependent badges always follow (by id) the badges they depend upon"""
        
        badges_to_skip = set()
        self.dbh.execute("SELECT badge_id FROM user_badges_types WHERE badge_id NOT IN (SELECT badge_id FROM user_badges WHERE user_id = {0}) ORDER BY badge_id".format(user_id))
        res = self.dbh.fetchall()
        for r in res:
            if r['badge_id'] not in badges_to_skip:
                checked = self.checkOneBadge(user_id, r['badge_id'])
                if checked is True:
                    self.addBadge(user_id, r['badge_id'])
                else:
                    try:
                        badges_to_skip |= set(self.badgeDependency[r['badge_id']])
                    except KeyError:
                        pass
    
    def checkOneBadge(self, user_id, badge_type):
        # the simplest badges for pure quantity
        if badge_type == 1:
            return self.checkNumOfSamples(user_id, 20)
        elif badge_type == 2:
            return self.checkNumOfSamples(user_id, 100)
        elif badge_type == 3:
            return self.checkNumOfSamples(user_id, 500)

    def addBadge(self, user_id, badge_type):
        #self.dbh.execute("INSERT INTO user_badges VALUES({0}, {1}, 0)".format(user_id, badge_type))
        print("User #{0} will get the badge #{1}".format(user_id, badge_type))

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
    ba = BadgeAssigner(dbh)
    ba.checkBadges()
    #db.commit()

if __name__ == "__main__":
    main()
