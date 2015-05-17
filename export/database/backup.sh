#!/usr/bin/env bash
TMPFILE=/tmp/devbackup.sql
DESTFILE=/corpus/files/export/database/database-dev.sql
DBHOST=`cat /corpus/config.ini | grep -A4 '\[mysql\]' | grep host   | cut -d'=' -f2 | sed 's/ //g'`
DBNAME=`cat /corpus/config.ini | grep -A4 '\[mysql\]' | grep dbname | cut -d'=' -f2 | sed 's/ //g'`

mysql --host $DBHOST $DBNAME < copy_nulled_tables.sql || exit 1

mysqldump --host $DBHOST \
    --ignore-table=opcorpora.users \
    --ignore-table=opcorpora.user_tokens \
    --ignore-table=opcorpora.dict_errata \
    --ignore-table=opcorpora.form2lemma \
    --ignore-table=opcorpora.form2tf \
    --ignore-table=opcorpora.tokenizer_strange \
    $DBNAME > $TMPFILE || exit 1

sed -i 's/`users_for_selective_backup`/`users`/g' $TMPFILE
sed -i 's/`user_tokens_for_selective_backup`/`user_tokens`/g' $TMPFILE

gzip -c $TMPFILE >$DESTFILE.tmp.gz && mv $DESTFILE{.tmp,}.gz
rm $TMPFILE
