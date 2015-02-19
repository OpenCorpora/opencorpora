#!/usr/bin/env bash
touch /var/lock/oc_readonly.lock
TEMP_DUMP=/srv/sql_opencorpora/tmp/temp.sql
if [ ! -d /backup/`date +%Y%m` ]; then
	mkdir /backup/`date +%Y%m`
fi
NOW=`date +%Y%m%d_%H%M`
mysqldump \
    --ignore-table=opcorpora.dict_errata \
    --ignore-table=opcorpora.form2lemma \
    --ignore-table=opcorpora.form2tf \
    --ignore-table=opcorpora.tokenizer_strange \
    opcorpora > $TEMP_DUMP
rm /var/lock/oc_readonly.lock
nice xz -cze8 $TEMP_DUMP >/backup/`date +%Y%m`/oc$NOW.sql.xz
rm $TEMP_DUMP
mysqldump \
    wikidb | xz -ze8 > /backup/`date +%Y%m`/wiki$NOW.sql.xz

# backup to Yandex.Disk
curl --user `cat /corpus/yadisk-auth` -T /backup/`date +%Y%m`/oc$NOW.sql.xz https://webdav.yandex.ru/opencorpora/backup/ || echo "Failed to upload backup to YaDisk"
