#!/usr/bin/python
#coding=utf-8

import urllib
import re
import cgi

URL_PREFIX = "http://www.chaskor.ru/"


def tag_Detect(articleUrl):
    sock = urllib.urlopen(articleUrl)
    htmlSource = sock.read()
    htmlSource = htmlSource.decode('utf-8')

    d={u'января':'01',
    u'февраля':'02',
    u'марта':'03',
    u'апреля':'04',
    u'мая':'05',
    u'июня':'06',
    u'июля':'07',
    u'августа':'08',
    u'сентября':'09',
    u'октября':'10',
    u'ноября':'11',
    u'декабря':'12'}

    mainSubject_tag=re.search('<a class="active".*?</a>', htmlSource ).group(0)
    mainSubject=u"Тема:ЧасКор:"+re.search('>(.*)</a>', mainSubject_tag ).group(1)
    subSubject=mainSubject + "/" + re.search('<a href="/news/">.+<b>(.*)</b></a>.*</span>', htmlSource).group(1)
    year=u"Год:" + re.search('<a href="/news/.*([0-9]{4}).*</span>', htmlSource).group(1)
    day= re.search('<a href="/news.*, (\\d?\\d) .*([0-9]{4}).*</span>', htmlSource).group(1)
    if len(day) == 1:
        day= "0" + day
    month = re.search('<a href="/news.*, \\d?\\d (.*) ([0-9]{4}).*</span>', htmlSource).group(1)
    date= u"Дата:" + day + "/" + d[month]
    articleName=re.search('<h4><b>(.*)</b></h4>', htmlSource).group(1)

    if articleUrl[-1].isdigit():
        number=re.search('_(\\d+)$', articleUrl).group(1)
        title = number.zfill(5) + " " + articleName
    else:
        title = articleName
    sock.close()

    return {'date':date, 'year':year, 'mainSubject': mainSubject,
            'subSubject': subSubject, 'title': title}


print "Content-Type: text/xml"
print
article_url = cgi.parse()['url'][0]
url = URL_PREFIX + article_url
tags=tag_Detect(url)
result = u"""<?xml version="1.0" encoding="UTF-8" ?>
<result xmlns="http://opencorpora.org/chaskorNewsParsingResult">
    <year>%s</year>
    <date>%s</date>
    <mainSubject>%s</mainSubject>
    <subSubject>%s</subSubject>
    <title>%s</title>
    <url>%s</url>
</result>""" % \
        (tags["year"], tags["date"], tags["mainSubject"],
         tags["subSubject"], tags["title"], url)
print result.encode('utf-8')

