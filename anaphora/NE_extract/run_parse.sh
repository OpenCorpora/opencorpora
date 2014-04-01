#! /bin/bash

if [ -z "$1" ] || [ -z "$2" ]
 then
   echo "./run_parse.sh MorphDirName XmlDirName ResDirName"
   exit 1
 else  
  
  mkdir -p $3
  rm $3/*
  for d in $1/*
     do
       XML=$(basename $d)
       perl parse_xmlfacts.pl -m $d -x $2/$XML.xml > $3/$XML.tsv 
       sort -u -k2,2 -k3,3 -k4,4 $3/$XML.tsv | sort -n > $3/$XML_sorted.tsv
       mv $3/$XML_sorted.tsv $3/$XML.tsv
       cat $3/$XML.tsv >> $3/groups.tsv
     done

  sort -u -k2,2 -k3,3 -k4,4 $3/groups.tsv | sort -n > $3/groups_sorted.tsv
  mv $3/groups_sorted.tsv $3/groups.tsv
  grep -P "\t17" $3/groups.tsv > $3/pronouns.tsv
fi
