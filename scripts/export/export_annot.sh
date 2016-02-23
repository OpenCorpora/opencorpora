#!/bin/bash

ROOT_PATH=${ROOT_PATH:-/corpus}
RO_FLAG=$ROOT_PATH/readonly.tmp
touch $RO_FLAG

EXPORT_DIR=$ROOT_PATH/files/export/annot
EXPORT_PATH=$EXPORT_DIR/annot.opcorpora
SCRIPT_DIR=$ROOT_PATH/export/annot

$SCRIPT_DIR/export_annot.pl $ROOT_PATH/config.ini >$EXPORT_PATH.xml
$SCRIPT_DIR/export_annot.pl $ROOT_PATH/config.ini no_ambig >$EXPORT_PATH.no_ambig.xml
$SCRIPT_DIR/export_annot.pl $ROOT_PATH/config.ini no_ambig no_unkn >$EXPORT_PATH.no_ambig_strict.xml

rm $RO_FLAG

# export one text per file
python3 $SCRIPT_DIR/split2files.py $EXPORT_PATH.xml $EXPORT_DIR/byfile
cd $EXPORT_DIR/byfile
tar -cj -f $EXPORT_PATH.xml.byfile.bz2.new *.xml && mv $EXPORT_PATH.xml.byfile.bz2{.new,}
zip -jq9 $EXPORT_PATH.xml.byfile.zip.new *.xml && mv $EXPORT_PATH.xml.byfile.zip{.new,}
cd ..
rm -rf $EXPORT_DIR/byfile

# take info from non-moderated pools
TMPFILE=`mktemp`
TMPFILE_GR=`mktemp`
python3 $SCRIPT_DIR/generate_grammeme_ordered_list.py -y $EXPORT_DIR/../dict/dict.opcorpora.xml.zip $TMPFILE_GR >/dev/null
python3 $SCRIPT_DIR/generate_no_homonymy.py -y \
        $EXPORT_PATH.xml \
        $EXPORT_DIR/../pools/pools.zip \
        $TMPFILE \
        $TMPFILE_GR >/dev/null
python3 $SCRIPT_DIR/remove_ambiguous_sentences.py -y $TMPFILE $EXPORT_PATH.no_ambig.nonmod.xml >/dev/null
rm $TMPFILE_GR
rm $TMPFILE
rm $EXPORT_PATH.xml_removed_temp.xml

# make archives
for f in $EXPORT_PATH $EXPORT_PATH.no_ambig $EXPORT_PATH.no_ambig_strict EXPORT_PATH.no_ambig.nonmod; do
    bzip2 -c9 $f.xml >$f.xml.bz2.new
    mv $f.xml.bz2.new $f.xml.bz2
    zip -jq9 $f.xml.zip.new $f.xml
    mv $f.xml.zip.new $f.xml.zip
done
