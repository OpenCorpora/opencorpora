# -*- coding: utf-8 -*-
import ConfigParser
import MySQLdb
import sys
import re

path = sys.argv[1]
cp = ConfigParser.ConfigParser()
#var = raw_input("Enter the path of the config file: ")
cp.read(path)
list = cp.items('mysql')
db = MySQLdb.connect( host = list[1][1], user = list[3][1], passwd = list[0][1], db = list[2][1],use_unicode=True)
c=db.cursor()
c.execute("SET NAMES utf8")
c.execute("SELECT *  FROM book_tags")
data = c.fetchall()
n = len(c.description)
list = []
c.execute("DELETE FROM tag_errors WHERE error_type=1")
for element in data:
    i = 1
    while i< n :
        pattern = 'Год:\d+'
        match = re.search(pattern, element[i])
        if match is None:
            pass
        else:
            year = element[i].split(":")[1]
            if int(year) < 1900:
                el = []
  #              print "book_id  " + str(element[0])
   #             print element[i] + "\n"
             
                sql = """INSERT INTO tag_errors(book_id, tag_name, error_type) VALUES(%d, '%s', %d)""" % (element[0], element[i], 1)
                
                c.execute(sql)
            
            else: 
                   pass 
        i = i + 1
db.commit()
c.execute("SELECT * FROM tag_errors WHERE error_type=1")        
d = c.fetchall()
#print d

#print c.description 
db.close()
