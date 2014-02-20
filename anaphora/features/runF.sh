#! /bin/bash

perl lineal.pl -m $1 -g $2 -p $3 > lineal.tmp
python morph.py $2 $1 < $3 | cut -f 2- > morph.tmp
perl synt.pl > synt.tmp
python ParDistance.py $3 $2 $1 | cut -f 2 > ParDistance.tmp
paste lineal.tmp morph.tmp synt.tmp ParDistance.tmp
rm *.tmp
