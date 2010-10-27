#!/bin/bash
touch /var/lock/oc_readonly.lock
newpath=/corpus/files/export/dict/dict.opcorpora.xml.bz2.new
/corpus/export/dict/export_dict.pl $1 </corpus/lib/config.php | bzip2 -c9 >$newpath
if [ `ls -l $newpath | awk '{print $5}'` -gt 100 ]; then
    mv $newpath /corpus/files/export/dict/dict.opcorpora.xml.bz2
else
    echo 'Generated dictionary export is too small, no overwriting' >&2
fi
rm /var/lock/oc_readonly.lock
