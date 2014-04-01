#! /bin/bash

if [ -z "$1" ] || [ -z "$2" ]
 then
   echo "./run_toma.sh PlaintextDirName ResXmlDirName"
   exit 1
 else  

  mkdir -p $2
  for d in $1/*
    do
      XML=$(basename $d)
      echo $XML
      D=`echo ${d//\//\\\/}`
      echo $D 
      sed -i "s/Dir = \".*\"/Dir = \"$D\"/" config.proto
      ./tomita-upd config.proto > $2/$XML.xml
    done
fi
