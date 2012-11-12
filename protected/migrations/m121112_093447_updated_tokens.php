<?php

class m121112_093447_updated_tokens extends CDbMigration
{
    public function up()
    {
        $this->dropIndex('dict_updated', 'text_forms');
        $this->dropColumn('text_forms', 'dict_updated');

        $this->createTable('updated_tokens', array(
            'token_id' => 'INT UNSIGNED NOT NULL',
            'dict_revision' => 'INT UNSIGNED NOT NULL'
        ), 'ENGINE = INNODB');
        $this->createIndex('token_id', 'updated_tokens', 'token_id');
        $this->createIndex('dict_revision', 'updated_tokens', 'dict_revision');
    }

    public function down()
    {
        $this->dropTable('updated_tokens');

        $this->addColumn('text_forms', 'dict_updated', 'TINYINT UNSIGNED NOT NULL');
        $this->createIndex('dict_updated', 'text_forms', 'dict_updated');
    }
}
