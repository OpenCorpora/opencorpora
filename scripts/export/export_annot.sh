#!/bin/bash

ROOT_PATH=${ROOT_PATH:-/corpus}
touch /var/lock/oc_readonly.lock

EXPORT_DIR=$ROOT_PATH/files/export/annot
EXPORT_PATH=$EXPORT_DIR/annot.opcorpora

$ROOT_PATH/export/annot/export_annot.pl $ROOT_PATH/config.ini >$EXPORT_PATH.xml
$ROOT_PATH/export/annot/export_annot.pl $ROOT_PATH/config.ini no_ambig >$EXPORT_PATH.no_ambig.xml
$ROOT_PATH/export/annot/export_annot.pl $ROOT_PATH/config.ini no_ambig no_unkn >$EXPORT_PATH.no_ambig_strict.xml

for f in $EXPORT_PATH $EXPORT_PATH.no_ambig $EXPORT_PATH.no_ambig_strict; do
    bzip2 -c9 $f.xml >$f.xml.bz2.new
    mv $f.xml.bz2.new $f.xml.bz2
    zip -jq9 $f.xml.zip.new $f.xml
    mv $f.xml.zip.new $f.xml.zip
done

rm /var/lock/oc_readonly.lock

# export one text per file
python3 $ROOT_PATH/export/annot/split2files.py $EXPORT_PATH.xml $EXPORT_DIR/byfile
cd $EXPORT_DIR/byfile
tar -cj -f $EXPORT_PATH.xml.byfile.bz2.new *.xml && mv $EXPORT_PATH.xml.byfile.bz2{.new,}
zip -jq9 $EXPORT_PATH.xml.byfile.zip.new *.xml && mv $EXPORT_PATH.xml.byfile.zip{.new,}
cd ..
rm -rf $EXPORT_DIR/byfile
