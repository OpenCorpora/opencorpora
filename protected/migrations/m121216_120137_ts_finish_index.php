<?php

class m121216_113838_indices extends CDbMigration
{
    public function up()
    {
        $this->createIndex('ts_finish', 'morph_annot_instances', 'ts_finish');
    }

    public function down()
    {
        $this->dropIndex('morph_annot_instances', 'ts_finish');
    }
}
