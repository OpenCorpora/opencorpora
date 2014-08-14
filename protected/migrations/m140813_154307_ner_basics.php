<?php

class m140813_154307_ner_basics extends CDbMigration
{
    public function up()
    {
        $this->createTable('ne_paragraphs', array(
            'par_id' => 'SMALLINT UNSIGNED NOT NULL',
            'user_id' => 'SMALLINT UNSIGNED NOT NULL',
            'status' => 'TINYINT UNSIGNED NOT NULL',
            'ts_finish' => 'INT UNSIGNED NOT NULL'
        ), 'ENGINE=INNODB');

        $this->createTable('ne_entities', array(
            'entity_id' => 'MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
            'par_id' => 'SMALLINT UNSIGNED NOT NULL',
            'start_token' => 'INT UNSIGNED NOT NULL',
            'length' => 'TINYINT UNSIGNED NOT NULL',
            'updated_ts' => 'INT UNSIGNED NOT NULL'
        ), 'ENGINE=INNODB');

        $this->createTable('ne_tags', array(
            'tag_id' => 'TINYINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
            'tag_name' => 'VARCHAR(31) NOT NULL'
        ), 'ENGINE=INNODB');

        $this->createTable('ne_entity_tags', array(
            'entity_id' => 'MEDIUMINT UNSIGNED NOT NULL',
            'tag_id' => 'TINYINT UNSIGNED NOT NULL'
        ), 'ENGINE=INNODB');

        $this->createIndex('par_id', 'ne_paragraphs', 'par_id');
        $this->createIndex('user_id', 'ne_paragraphs', 'user_id');
        $this->createIndex('status', 'ne_paragraphs', 'status');
        $this->createIndex('par_id', 'ne_entities', 'par_id');
        $this->createIndex('entity_id', 'ne_entity_tags', 'entity_id');
    }

    public function down()
    {
        $this->dropTable('ne_paragraphs');
        $this->dropTable('ne_entities');
        $this->dropTable('ne_tags');
        $this->dropTable('ne_entity_tags');
    }
}
