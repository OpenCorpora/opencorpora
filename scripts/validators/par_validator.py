# -*- coding: utf-8 -*-
import sys
import ConfigParser, MySQLdb

config = ConfigParser.ConfigParser()
config.read(sys.argv[1])

hostname = config.get ('mysql', 'host')
dbname = config.get ('mysql', 'dbname')
username = config.get ('mysql', 'user')
password = config.get ('mysql', 'passwd')

db = MySQLdb.connect (hostname, username, password, dbname, use_unicode=True)

cursor = db.cursor()
cursor.execute('SET NAMES utf8')
cursor.execute("""DELETE FROM tag_errors WHERE error_type=5""")
cursor.execute("""SELECT book_id FROM books WHERE (book_id in (SELECT distinct parent_id FROM books)) and (book_id IN (SELECT distinct book_id FROM paragraphs))""")

data = cursor.fetchall()
for i in data:
	query = """INSERT INTO tag_errors VALUES (%d, '%s', %d)""" % (i[0], '', 5)
	cursor.execute(query)
db.commit()
db.close
