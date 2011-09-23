#!/bin/bash

ROOT_PATH=${ROOT_PATH:-/corpus}

dump_path=$ROOT_PATH/files/export/annot/annot.opcorpora.xml
export_path=$ROOT_PATH/files/export/ngrams

$ROOT_PATH/export/export_ngram.pl -f $dump_path -n 1 | bzip2 >$export_path/unigrams.bz2
bzcat $export_path/unigrams.bz2 | head -100 >$export_path/unigrams.top100

$ROOT_PATH/export/export_ngram.pl -f $dump_path -l -n 1 | bzip2 >$export_path/unigrams.lc.bz2
bzcat $export_path/unigrams.lc.bz2 | head -100 >$export_path/unigrams.lc.top100

$ROOT_PATH/export/export_ngram.pl -f $dump_path -n 2 | bzip2 >$export_path/bigrams.bz2
bzcat $export_path/bigrams.bz2 | head -100 >$export_path/bigrams.top100

$ROOT_PATH/export/export_ngram.pl -f $dump_path -l -n 2 | bzip2 >$export_path/bigrams.lc.bz2
bzcat $export_path/bigrams.lc.bz2 | head -100 >$export_path/bigrams.lc.top100

rm $dump_path
