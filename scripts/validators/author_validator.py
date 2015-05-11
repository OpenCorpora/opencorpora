# -*- coding: utf-8 -*-

import ConfigParser, MySQLdb
import sys
config = ConfigParser.ConfigParser()
config.read(sys.argv[1])

hostname =  config.get ('mysql', 'host')
dbname =  config.get ('mysql', 'dbname')
username =  config.get ('mysql', 'user')
password =  config.get ('mysql', 'passwd')

db = MySQLdb.connect(hostname, username, password, dbname)

cursor = db.cursor()
cursor.execute('SET NAMES utf8')
cursor.execute("""DELETE FROM tag_errors WHERE error_type = 3""")
cursor.execute("""SELECT book_id  FROM books
                  WHERE book_id NOT IN (459, 2171, 2186, 2270, 2273, 2284, 3374, 3384, 3395, 3463)
                  AND book_id NOT IN
                      (SELECT book_id FROM book_tags WHERE tag_name LIKE 'Автор:%')
                  AND book_id NOT IN
                      (SELECT book_id FROM books WHERE parent_id IN
                          (SELECT book_id FROM book_tags WHERE tag_name LIKE 'Автор:%'))
                  AND book_id NOT IN
                      (SELECT book_id FROM books WHERE parent_id IN
                          (SELECT book_id FROM books WHERE parent_id IN
                              (SELECT book_id FROM book_tags WHERE tag_name LIKE 'Автор:%')))
                  AND book_id NOT IN
                      (SELECT book_id FROM books WHERE parent_id IN
                          (SELECT book_id FROM books WHERE parent_id IN
                              (SELECT book_id FROM books WHERE parent_id IN
                                  (SELECT book_id FROM book_tags WHERE tag_name LIKE 'Автор:%'))))
                  AND book_id NOT IN
                      (SELECT book_id FROM books WHERE parent_id IN
                          (SELECT book_id FROM books WHERE parent_id IN
                              (SELECT book_id FROM books WHERE parent_id IN
                                  (SELECT book_id FROM books WHERE parent_id IN
                                      (SELECT book_id FROM book_tags WHERE tag_name LIKE 'Автор:%')))))
                  AND book_id NOT IN
                      (SELECT book_id FROM books WHERE parent_id IN
                          (SELECT book_id FROM books WHERE parent_id IN
                              (SELECT book_id FROM books WHERE parent_id IN
                                  (SELECT book_id FROM books WHERE parent_id IN
                                      (SELECT book_id FROM books WHERE parent_id IN
                                          (SELECT book_id FROM book_tags WHERE tag_name LIKE 'Автор:%'))))))
                  AND parent_id NOT IN (8, 226, 1675, 2171, 2186, 2270, 2273, 2284)
                  AND parent_id NOT IN
                      (SELECT book_id FROM books WHERE parent_id IN
                          (SELECT book_id FROM books WHERE parent_id IN
                              (SELECT book_id FROM books WHERE parent_id IN
                                  (SELECT book_id FROM books WHERE parent_id IN
                                      (SELECT book_id FROM books WHERE parent_id IN
                                          (SELECT book_id FROM books WHERE parent_id IN
                                              (SELECT book_id FROM books WHERE parent_id IN (806, 1675, 2186))))))))
                  AND parent_id NOT IN
                      (SELECT book_id FROM books WHERE parent_id IN
                          (SELECT book_id FROM books WHERE parent_id IN
                              (SELECT book_id FROM books WHERE parent_id IN
                                  (SELECT book_id FROM books WHERE parent_id IN
                                      (SELECT book_id FROM books WHERE parent_id IN
                                          (SELECT book_id FROM books WHERE parent_id IN (806, 1675, 2186)))))))
                  AND parent_id NOT IN
                      (SELECT book_id FROM books WHERE parent_id IN
                          (SELECT book_id FROM books WHERE parent_id IN
                              (SELECT book_id FROM books WHERE parent_id IN
                                  (SELECT book_id FROM books WHERE parent_id IN
                                      (SELECT book_id FROM books WHERE parent_id IN (806, 1675, 2186))))))
                  AND parent_id NOT IN
                      (SELECT book_id FROM books WHERE parent_id IN
                          (SELECT book_id FROM books WHERE parent_id IN
                              (SELECT book_id FROM books WHERE parent_id IN
                                  (SELECT book_id FROM books WHERE parent_id IN (806, 1675, 2186)))))
                  AND parent_id NOT IN
                      (SELECT book_id FROM books WHERE parent_id IN
                          (SELECT book_id FROM books WHERE parent_id IN
                              (SELECT book_id FROM books WHERE parent_id IN (806, 1675, 2186))))
                  AND parent_id NOT IN
                      (SELECT book_id FROM books WHERE parent_id IN
                          (SELECT book_id FROM books WHERE parent_id IN (806, 1675, 2186)))
                  AND parent_id NOT IN
                      (SELECT book_id FROM books WHERE parent_id IN (184, 806, 1675, 2186, 2284, 3384))
                  AND parent_id > 0""")
data = cursor.fetchall();
for i in data:
    query = """INSERT INTO tag_errors VALUES(%d, '%s', %d)""" % (i[0], '', 3)
    cursor.execute(query)
db.commit()
db.close


