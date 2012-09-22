#!/usr/bin/env bash
touch /var/lock/oc_readonly.lock
if [ ! -d /backup/`date +%Y%m` ]; then
	mkdir /backup/`date +%Y%m`
fi
mysqldump \
    --ignore-table=opcorpora.dict_errata \
    --ignore-table=opcorpora.form2lemma \
    --ignore-table=opcorpora.form2tf \
    --ignore-table=opcorpora.tokenizer_strange \
    opcorpora | bzip2 -c9 > /backup/`date +%Y%m`/oc`date +%Y%m%d_%H%M`.sql.bz2
rm /var/lock/oc_readonly.lock
mysqldump \
    wikidb | bzip2 -c9 > /backup/`date +%Y%m`/wiki`date +%Y%m%d_%H%M`.sql.bz2
