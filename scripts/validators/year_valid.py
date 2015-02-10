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
string = ""

c.execute("DELETE FROM tag_errors WHERE error_type IN (1, 2)")
for element in data:
    list = []
    c.execute("SELECT * from book_tags WHERE book_id=%d" % (element[0]))
    f = c.fetchall()
    for el  in f:
        list = list + [el[1]]
       #// print list 
    m = 0   
    for l in list:
        pattern = 'Год:\d+'
        match = re.search(pattern, l)
        if match is None:
             pass
        
        else:
             m = 1
    
             
    if  m == 0:
         #print element[0]  
         c.execute("DELETE FROM tag_errors WHERE error_type=1 AND book_id=%d" % (element[0]))
         sql = """INSERT INTO tag_errors(book_id, tag_name, error_type) VALUES (%d, '%s', %d)""" % (element[0], 'no year_tag', 1)
         c.execute(sql)
for element in data:
    i = 1
    
    while i< n :
        pattern = 'Год:\d+'
        pattern1 = 'Дата:.*'
        match = re.search(pattern, element[i])
        match1 = re.search(pattern1, element[i])
        if match is None:
            pass     
        if match1 is None:
            pass
        if match is not None:
            year = element[i].split(":")[1]
            try:
                if int(year) < 1800 or int(year)>2015:
                    sql = """INSERT INTO tag_errors(book_id, tag_name, error_type) VALUES(%d, '%s', %d)""" % (element[0], element[i], 1)
                
                    c.execute(sql)
                else: 
                       pass 
            except:
                sql =  """INSERT INTO tag_errors(book_id, tag_name, error_type) VALUES(%d, '%s', %d)""" % (element[0], element[i], 1)   
                c.execute(sql)
           
        if match1 is not None:
            date = element[i].split(":")[1]
            try:
                pat = '(\d{2})'
                day = date.split("/")[0]
                month = date.split("/")[1]

                mat = re.search(pat, day)
                mat1 = re.search(pat, month)

                sql = """INSERT INTO tag_errors(book_id, tag_name, error_type) VALUES (%d, '%s', %d)""" % (element[0], element[i], 2)
                if mat is None or mat1 is None:
                    c.execute(sql)
                
                if int(month)>12 or int(day)>31:
                    c.execute(sql)

                if int(month) in [ 4, 6, 9, 11] and int(day) == 31:
                    c.execute(sql)

                if int(month) == 2 and int(day) > 28:
                    c.execute(sql)  

                else:
                    pass
            except:
                pass
        i = i + 1
                
db.commit()
c.execute("SELECT * from tag_errors")
d = c.fetchall()
#print d

#print c.description 
db.close()
