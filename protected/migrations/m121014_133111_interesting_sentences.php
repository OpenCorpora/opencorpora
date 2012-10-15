<?php

class m121014_133111_interesting_sentences extends CDbMigration
{
    public function up()
    {
        $this->createTable('good_sentences', array(
            'sent_id' => 'MEDIUMINT UNSIGNED NOT NULL UNIQUE',
            'num_words' => 'TINYINT UNSIGNED NOT NULL',
            'num_homonymous' => 'TINYINT UNSIGNED NOT NULL'
        ), 'ENGINE = INNODB');
    }

    public function down()
    {
        $this->dropTable('good_sentences');
    }
}
