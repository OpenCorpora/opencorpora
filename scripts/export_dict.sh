#!/bin/bash
touch /var/lock/oc_readonly.lock
newpath=/corpus/files/export/dict/dict.opcorpora
/corpus/export/dict/export_dict.pl $1 </corpus/lib/config.ini >$newpath.xml
rm /var/lock/oc_readonly.lock
if [ `ls -l $newpath.xml | awk '{print $5}'` -gt 100 ]; then
    bzip2 -c9 $newpath.xml >$newpath.xml.bz2.new
    mv $newpath.xml.bz2.new $newpath.xml.bz2
    zip -9 $newpath.xml.zip.new $newpath.xml
    mv $newpath.xml.zip.new $newpath.xml.zip
    rm $newpath.xml

    touch /var/lock/oc_readonly.lock
    /corpus/export/dict/export_dict.pl $1 --PLAINTEXT </corpus/lib/config.ini >$newpath.txt
    rm /var/lock/oc_readonly.lock
    bzip2 -c9 $newpath.txt >$newpath.txt.bz2.new
    mv $newpath.txt.bz2.new $newpath.txt.bz2
    zip -9 $newpath.txt.zip.new $newpath.txt
    mv $newpath.txt.zip.new $newpath.txt.zip
    rm $newpath.txt
else
    echo 'Generated dictionary export is too small, no overwriting' >&2
fi
