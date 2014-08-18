<?php

class m140818_123712_user_in_entity extends CDbMigration
{
    public function up()
    {
        $this->addColumn('ne_paragraphs', 'annot_id', 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST');
        $this->renameColumn('ne_entities', 'par_id', 'annot_id');
    }

    public function down()
    {
        $this->dropColumn('ne_paragraphs', 'annot_id');
        $this->renameColumn('ne_entities', 'annot_id', 'par_id');
    }
}
