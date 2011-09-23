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
    zip -9 $1.zip.new $1
    mv $1.zip.new $1.zip
    rm -f $1
}

$ROOT_PATH/export/export_ngram.pl -f $dump_path -n 1 >$export_path/unigrams
process $export_path/unigrams

$ROOT_PATH/export/export_ngram.pl -f $dump_path -l -n 1 >$export_path/unigrams.lc
process $export_path/unigrams.lc

$ROOT_PATH/export/export_ngram.pl -f $dump_path -n 2 >$export_path/bigrams
process $export_path/bigrams

$ROOT_PATH/export/export_ngram.pl -f $dump_path -l -n 2 >$export_path/bigrams.lc
process $export_path/bigrams.lc
