#!/bin/bash
ROOT_PATH=${ROOT_PATH:-/corpus}

dump_path=$ROOT_PATH/files/export/annot/annot.opcorpora.xml
export_path=$ROOT_PATH/files/export/ngrams

if [ ! -f $dump_path ]; then
    echo "Dump not found"
    exit 1
fi

function process() {
    if [ -z "$1" ]; then
        echo "No argument specified"
        return 1
    fi

    cat $1 | head -100 >$1.top100
    bzip2 -c9 $1 > $1.bz2.new
    mv $1.bz2.new $1.bz2
    zip -q9 $1.zip.new $1
    mv $1.zip.new $1.zip
    rm -f $1
}

$ROOT_PATH/export/export_ngram.pl -f $dump_path -i -n 1 >$export_path/unigrams
process $export_path/unigrams

$ROOT_PATH/export/export_ngram.pl -f $dump_path -i -l -n 1 >$export_path/unigrams.lc
process $export_path/unigrams.lc

$ROOT_PATH/export/export_ngram.pl -f $dump_path -i -c -n 1 >$export_path/unigrams.cyr
process $export_path/unigrams.cyr

$ROOT_PATH/export/export_ngram.pl -f $dump_path -i -c -l -n 1 >$export_path/unigrams.cyr.lc
process $export_path/unigrams.cyr.lc

$ROOT_PATH/export/export_ngram.pl -f $dump_path -i -n 2 >$export_path/bigrams
process $export_path/bigrams

$ROOT_PATH/export/export_ngram.pl -f $dump_path -i -l -n 2 >$export_path/bigrams.lc
process $export_path/bigrams.lc

$ROOT_PATH/export/export_ngram.pl -f $dump_path -i -c -n 2 >$export_path/bigrams.cyrA
process $export_path/bigrams.cyrA

$ROOT_PATH/export/export_ngram.pl -f $dump_path -i -C -n 2 >$export_path/bigrams.cyrB
process $export_path/bigrams.cyrB

$ROOT_PATH/export/export_ngram.pl -f $dump_path -i -c -l -n 2 >$export_path/bigrams.cyrA.lc
process $export_path/bigrams.cyrA.lc

$ROOT_PATH/export/export_ngram.pl -f $dump_path -i -C -l -n 2 >$export_path/bigrams.cyrB.lc
process $export_path/bigrams.cyrB.lc

$ROOT_PATH/export/export_ngram.pl -f $dump_path -i -n 3 >$export_path/trigrams
process $export_path/trigrams

$ROOT_PATH/export/export_ngram.pl -f $dump_path -i -l -n 3 >$export_path/trigrams.lc
process $export_path/trigrams.lc

$ROOT_PATH/export/export_ngram.pl -f $dump_path -i -c -n 3 >$export_path/trigrams.cyrA
process $export_path/trigrams.cyrA

$ROOT_PATH/export/export_ngram.pl -f $dump_path -i -C -n 3 >$export_path/trigrams.cyrB
process $export_path/trigrams.cyrB

$ROOT_PATH/export/export_ngram.pl -f $dump_path -i -c -l -n 3 >$export_path/trigrams.cyrA.lc
process $export_path/trigrams.cyrA.lc

$ROOT_PATH/export/export_ngram.pl -f $dump_path -i -C -l -n 3 >$export_path/trigrams.cyrB.lc
process $export_path/trigrams.cyrB.lc

rm -f $dump_path
