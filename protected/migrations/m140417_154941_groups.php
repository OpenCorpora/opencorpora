<?php

class m140417_154941_groups extends CDbMigration
{
    public function up()
    {
        $this->renameColumn('books', 'syntax_moder_id', 'old_syntax_moder_id');

        $this->createTable('syntax_group_types', array(
            'type_id' => 'TINYINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
            'type_name' => 'VARCHAR(255) NOT NULL'
        ), 'ENGINE = INNODB');

        $this->createTable('syntax_groups', array(
            'group_id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
            'parse_id' => 'MEDIUMINT UNSIGNED NOT NULL',
            'is_complex' => 'TINYINT UNSIGNED NOT NULL'
        ), 'ENGINE = INNODB');

        $this->createTable('syntax_groups_revisions', array(
            'rev_id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
            'revset_id' => 'INT UNSIGNED NOT NULL',
            'group_id' => 'INT UNSIGNED NOT NULL',
            'group_type' => 'TINYINT UNSIGNED NOT NULL',
            'head_id' => 'INT UNSIGNED NOT NULL',
            'rev_text' => 'TEXT NOT NULL',
            'is_last' => 'TINYINT UNSIGNED NOT NULL'
        ), 'ENGINE = INNODB');

        $this->createTable('syntax_parses', array(
            'parse_id' => 'MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
            'sent_id' => 'MEDIUMINT UNSIGNED NOT NULL',
        ), 'ENGINE = INNODB');

        $this->createTable('syntax_groups_simple', array(
            'group_id' => 'INT UNSIGNED NOT NULL',
            'token_id' => 'INT UNSIGNED NOT NULL'
        ), 'ENGINE = INNODB');

        $this->createIndex('group_id', 'syntax_groups_simple', 'group_id');
        $this->createIndex('token_id', 'syntax_groups_simple', 'token_id');
        $this->createIndex('parse_id', 'syntax_groups', 'parse_id');
        $this->createIndex('revset_id', 'syntax_groups_revisions', 'revset_id');
        $this->createIndex('group_id', 'syntax_groups_revisions', 'group_id');
        $this->createIndex('is_last', 'syntax_groups_revisions', 'is_last');
        $this->createIndex('sent_id', 'syntax_parses', 'sent_id');
    }

    public function down()
    {
        $this->renameColumn('books', 'old_syntax_moder_id', 'syntax_moder_id');

        $this->dropTable('syntax_group_types');
        $this->dropTable('syntax_groups');
        $this->dropTable('syntax_groups_revisions');
        $this->dropTable('syntax_parses');
        $this->dropTable('syntax_groups_simple');
    }
}
