#!/bin/bash
if [ `df | grep sql_opencorpora | awk '{print $4}'` -lt 100000 ]; then
    touch /var/lock/oc_readonly.lock
    echo 'Insufficient space on opencorpora.org!' | sendmail dima.granovsky@gmail.com
fi
