<?php

class m121216_113838_indices extends CDbMigration
{
    public function up()
    {
        $this->createIndex('answer', 'morph_annot_instances', 'answer');
    }

    public function down()
    {
        $this->dropIndex('morph_annot_instances', 'answer');
    }
}
