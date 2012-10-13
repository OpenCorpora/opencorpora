#!/bin/bash

ROOT_PATH=${ROOT_PATH:-/corpus}
touch /var/lock/oc_readonly.lock

export_path=$ROOT_PATH/files/export/annot/annot.opcorpora

$ROOT_PATH/export/annot/export_annot.pl $ROOT_PATH/config.ini >$export_path.xml
$ROOT_PATH/export/annot/export_annot.pl $ROOT_PATH/config.ini no_ambig >$export_path.no_ambig.xml

for f in $export_path $export_path.no_ambig; do
    bzip2 -c9 $f.xml >$f.xml.bz2.new
    mv $f.xml.bz2.new $f.xml.bz2
    zip -q9 $f.xml.zip.new $f.xml
    mv $f.xml.zip.new $f.xml.zip
done

rm /var/lock/oc_readonly.lock
