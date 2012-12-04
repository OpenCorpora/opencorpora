#!/usr/bin/env bash

TMPDIR=`dirname $0`/tmp
EXPORT_DIR=/corpus/files/export/pools

if [ ! -e $TMPDIR ]; then
    mkdir $TMPDIR
fi

perl ../pools.pl /corpus/config.ini | grep -E "[45679]$" > $TMPDIR/pools.txt

for id in $( cat $TMPDIR/pools.txt | gawk '{ print $1 }' )
do
  wget -q "http://localhost/pools.php?act=samples&pool_id=$id&tabs&mod_ans" --output-document=$TMPDIR/pool_$id.tab 
done

cd $TMPDIR
zip -q9 $EXPORT_DIR/pools.zip pool*.t*
tar -cjf $EXPORT_DIR/pools.tar.bz2 pool*.tab pools.txt --remove-files
cd ..

rm -rf $TMPDIR
