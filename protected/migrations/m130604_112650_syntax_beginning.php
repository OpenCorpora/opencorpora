<?php

class m130604_112650_syntax_beginning extends CDbMigration
{
    public function up()
    {
        $this->createTable('syntax_group_types', array(
            'type_id' => 'TINYINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
            'type_name' => 'VARCHAR(255) NOT NULL'
        ), 'ENGINE = INNODB');

        $this->createTable('syntax_groups', array(
            'group_id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
            'group_type' => 'TINYINT UNSIGNED NOT NULL',
            'rev_set_id' => 'INT UNSIGNED NOT NULL'
        ), 'ENGINE = INNODB');

        $this->createTable('syntax_groups_simple', array(
            'group_id' => 'INT UNSIGNED NOT NULL',
            'token_id' => 'INT UNSIGNED NOT NULL'
        ), 'ENGINE = INNODB');

        $this->createTable('syntax_groups_complex', array(
            'parent_gid' => 'INT UNSIGNED NOT NULL',
            'child_gid' => 'INT UNSIGNED NOT NULL'
        ), 'ENGINE = INNODB');

        $this->createIndex('group_id', 'syntax_groups_simple', 'group_id');
        $this->createIndex('token_id', 'syntax_groups_simple', 'token_id');

        $this->createIndex('parent_gid', 'syntax_groups_complex', 'parent_gid');
        $this->createIndex('child_gid', 'syntax_groups_complex', 'child_gid');

        $this->execute("
            INSERT INTO syntax_group_types VALUES
            (NULL, 'Имя + фамилия'),
            (NULL, 'Имя + отчество'),
            (NULL, 'Составная фамилия'),
            (NULL, 'Составное имя'),
            (NULL, 'Имя + отчество + фамилия'),
            (NULL, 'Должность + имя [+ фамилия]'),
            (NULL, 'Прилагательное + существительное'),
            (NULL, 'Существительное + существительное'),
            (NULL, 'Существительное + прил./прич. оборот'),
            (NULL, 'Предлог + существительное'),
            (NULL, 'Существительное + предложная группа'),
            (NULL, 'Сложный предлог'),
            (NULL, 'Сложный союз'),
            (NULL, 'Сложное наречие'),
            (NULL, 'Сложная частица')
        ");
    }

    public function down()
    {
        $this->dropTable('syntax_group_types');
        $this->dropTable('syntax_groups');
        $this->dropTable('syntax_groups_simple');
        $this->dropTable('syntax_groups_complex');
    }
}
