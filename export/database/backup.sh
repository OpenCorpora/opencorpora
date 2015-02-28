#!/usr/bin/env bash

mysql corpora < copy_nulled_tables.sql

mysqldump \
    --ignore-table=${MySqlDatabaseName}.users \
    --ignore-table=${MySqlDatabaseName}.user_tokens \
    corpora > dump.sql

sed -i 's/`users_for_selective_backup`/`users`/g' dump.sql
sed -i 's/`user_tokens_for_selective_backup`/`user_tokens`/g' dump.sql

#gzip dump.sql
