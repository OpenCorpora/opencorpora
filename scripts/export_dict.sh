#!/bin/bash
touch /var/lock/oc_readonly.lock
/corpus/export/dict/export_dict.pl </corpus/lib/config.php | bzip2 -c9 >/corpus/files/export/dict/dict.opcorpora.xml.bz2.new
mv /corpus/files/export/dict/dict.opcorpora.xml.bz2.new /corpus/files/export/dict/dict.opcorpora.xml.bz2
rm /var/lock/oc_readonly.lock
