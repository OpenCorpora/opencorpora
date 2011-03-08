#!/bin/bash
touch /var/lock/oc_readonly.lock
newpath=/corpus/files/export/dict/dict.opcorpora.xml
/corpus/export/dict/export_dict.pl $1 </corpus/lib/config.php >$newpath
if [ `ls -l $newpath | awk '{print $5}'` -gt 100 ]; then
    bzip2 -c9 $newpath >$newpath.bz2.new
    mv $newpath.bz2.new $newpath.bz2
    zip -9 $newpath.zip.new $newpath
    mv $newpath.zip.new $newpath.zip
    rm $newpath
else
    echo 'Generated dictionary export is too small, no overwriting' >&2
fi
rm /var/lock/oc_readonly.lock
