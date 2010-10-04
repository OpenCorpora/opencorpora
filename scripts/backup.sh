#!/bin/bash
touch /var/lock/oc_readonly.lock
if [ ! -d /data/backup/`date +%Y%m` ]; then
	mkdir /data/backup/`date +%Y%m`
fi
mysqldump opcorpora | bzip2 -c9 > /data/backup/`date +%Y%m`/oc`date +%Y%m%d_%H%M`.sql.bz2
rm /var/lock/oc_readonly.lock
