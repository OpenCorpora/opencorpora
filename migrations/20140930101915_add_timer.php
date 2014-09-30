<?php

use Phinx\Migration\AbstractMigration;

class AddTimer extends AbstractMigration
{
    public function up()
    {
        $this->execute("CREATE TABLE timing (
            `timestamp`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `user_id`    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            `page`       VARCHAR(255) NOT NULL,
            `total_time` FLOAT NOT NULL,
            `is_ajax`    TINYINT UNSIGNED NOT NULL
        ) ENGINE=INNODB");
    }

    public function down()
    {
        $this->dropTable("timing");
    }
}
