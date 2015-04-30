#!/usr/bin/env bash
DBHOST=ocdb
DBNAME=opcorpora

mysql --host $DBHOST $DBNAME < copy_nulled_tables.sql || exit 1

mysqldump --host $DBHOST \
    --ignore-table=opcorpora.users \
    --ignore-table=opcorpora.user_tokens \
    --ignore-table=opcorpora.dict_errata \
    --ignore-table=opcorpora.form2lemma \
    --ignore-table=opcorpora.form2tf \
    --ignore-table=opcorpora.tokenizer_strange \
    $DBNAME > dump.sql || exit 1

sed -i 's/`users_for_selective_backup`/`users`/g' dump.sql
sed -i 's/`user_tokens_for_selective_backup`/`user_tokens`/g' dump.sql

#gzip dump.sql
