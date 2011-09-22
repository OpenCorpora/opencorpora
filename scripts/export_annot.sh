#!/bin/bash
touch /var/lock/oc_readonly.lock
export_path=/corpus/files/export/annot/annot.opcorpora
/corpus/export/annot/export_annot.pl /corpus/lib/config.ini >$export_path.xml
bzip2 -c9 $export_path.xml >$export_path.xml.bz2.new
mv $export_path.xml.bz2.new $export_path.xml.bz2
zip -9 $export_path.xml.zip.new $export_path.xml
mv $export_path.xml.zip.new $export_path.xml.zip
rm /var/lock/oc_readonly.lock
