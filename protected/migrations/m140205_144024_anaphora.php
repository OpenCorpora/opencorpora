<?php

class m140205_144024_anaphora extends CDbMigration
{
    public function up()
    {
        $this->createTable('anaphora', array(
            'ref_id' => 'SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
            'token_id' => 'INT UNSIGNED NOT NULL',
            'group_id' => 'INT UNSIGNED NOT NULL',
            'rev_set_id' => 'INT UNSIGNED NOT NULL',
            'user_id' => 'SMALLINT UNSIGNED NOT NULL'
        ), 'ENGINE = INNODB');
    }

    public function down()
    {
        $this->dropTable('anaphora');
    }
}
