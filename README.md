opencorpora
===========

A web-based engine for creating and annotating textual corpora


How-to run with Docker Compose
==============================

- Modify MySql root password in `docker-compose.yml`

- Execure following commands in the first terminal:
```
docker-compose up --build
```

- Execute following commands in the second terminal:

```
docker-compose exec opencorpora_db /bin/bash

# Inside container:

# Create database in MySql and grant access
mysql -u root -p

# Inside MySql shell:
CREATE DATABASE corpora DEFAULT CHARSET utf8;
CREATE USER corpora@localhost IDENTIFIED BY 'password';
GRANT DELETE, INSERT, LOCK TABLES, SELECT, UPDATE, ALTER, CREATE ON corpora.* TO corpora;
exit;

exit
```

```
docker-compose exec opencorpora_front /bin/bash

# Inside contianer:

# Add password for MySql user 'corpora' into both config.ini and config.json

# Fill the database
zcat database-dev.sql.gz | mysql -Dcorpora -uroot -p

# Add cron tasks
crontab scripts/conf/crontab.list

exit
```

