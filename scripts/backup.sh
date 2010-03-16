#!/bin/bash
cd ~/corpus
if [ ! -d ./backup/`date +%Y%m` ]; then
	mkdir ./backup/`date +%Y%m`
fi
mysqldump -uroot corpora | gzip > ./backup/`date +%Y%m`/`date +%Y%m%d_%H%M`.sql.gz
