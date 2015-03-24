#!/bin/bash

ROOT_PATH=${ROOT_PATH:-/corpus}

touch /var/lock/oc_readonly.lock
newpath=$ROOT_PATH/files/export/dict/dict.opcorpora
$ROOT_PATH/export/dict/export_dict.pl $1 <$ROOT_PATH/config.ini >$newpath.xml
rm /var/lock/oc_readonly.lock
if [ `ls -l $newpath.xml | awk '{print $5}'` -gt 100 ]; then
    bzip2 -cq9 $newpath.xml >$newpath.xml.bz2.new
    mv $newpath.xml.bz2.new $newpath.xml.bz2
    zip -jq9 $newpath.xml.zip.new $newpath.xml
    mv $newpath.xml.zip.new $newpath.xml.zip
    rm $newpath.xml

    touch /var/lock/oc_readonly.lock
    $ROOT_PATH/export/dict/export_dict.pl $1 -p <$ROOT_PATH/config.ini >$newpath.txt
    rm /var/lock/oc_readonly.lock
    bzip2 -cq9 $newpath.txt >$newpath.txt.bz2.new
    mv $newpath.txt.bz2.new $newpath.txt.bz2
    zip -jq9 $newpath.txt.zip.new $newpath.txt
    mv $newpath.txt.zip.new $newpath.txt.zip
    rm $newpath.txt
fi
