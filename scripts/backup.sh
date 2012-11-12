#!/usr/bin/env bash
touch /var/lock/oc_readonly.lock
if [ ! -d /backup/`date +%Y%m` ]; then
	mkdir /backup/`date +%Y%m`
fi
NOW=`date +%Y%m%d_%H%M`
mysqldump \
    --ignore-table=opcorpora.dict_errata \
    --ignore-table=opcorpora.form2lemma \
    --ignore-table=opcorpora.form2tf \
    --ignore-table=opcorpora.tokenizer_strange \
    opcorpora | xz -ze8 >/backup/`date +%Y%m`/oc$NOW.sql.xz
rm /var/lock/oc_readonly.lock
mysqldump \
    wikidb | xz -ze8 > /backup/`date +%Y%m`/wiki$NOW.sql.bz2
