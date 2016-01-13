#!/usr/bin/env bash
RO_FLAG=/corpus/readonly.tmp

touch $RO_FLAG
TEMP_DUMP=/backup/temp.sql
if [ ! -d /backup/`date +%Y%m` ]; then
	mkdir /backup/`date +%Y%m`
fi
NOW=`date +%Y%m%d_%H%M`
mysqldump \
    --host ocdb \
    --ignore-table=opcorpora.dict_errata \
    --ignore-table=opcorpora.form2lemma \
    --ignore-table=opcorpora.form2tf \
    --ignore-table=opcorpora.tokenizer_strange \
    opcorpora > $TEMP_DUMP
mysqldump \
    --host ocdb \
    --no-data \
    opcorpora \
        dict_errata \
        form2lemma \
        form2tf \
        tokenizer_strange \
    >> $TEMP_DUMP
rm $RO_FLAG
nice xz -cze8 $TEMP_DUMP >/backup/`date +%Y%m`/oc$NOW.sql.xz
rm $TEMP_DUMP
mysqldump \
    --host ocdb \
    wikidb | xz -ze8 > /backup/`date +%Y%m`/wiki$NOW.sql.xz

# backup to Yandex.Disk
curl -s --user `cat /corpus/yadisk-auth` -T /backup/`date +%Y%m`/oc$NOW.sql.xz https://webdav.yandex.ru/opencorpora/backup/ || echo "Failed to upload backup to YaDisk"
