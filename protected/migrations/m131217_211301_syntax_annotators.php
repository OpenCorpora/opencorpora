<?php

class m131217_211301_syntax_annotators extends CDbMigration
{
    public function up()
    {
        $this->createTable('syntax_annotators', array(
            'user_id' => 'SMALLINT UNSIGNED NOT NULL',
            'book_id' => 'MEDIUMINT UNSIGNED NOT NULL',
            'status' => 'TINYINT UNSIGNED NOT NULL'
        ), 'ENGINE = INNODB');
    }

    public function down()
    {
        $this->dropTable('syntax_annotators');
    }
}
