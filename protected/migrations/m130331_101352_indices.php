<?php

class m130331_101352_indices extends CDbMigration
{
    public function up()
    {
        $this->createIndex('tf_id', 'morph_annot_samples', 'tf_id');
    }

    public function down()
    {
        $this->dropIndex('morph_annot_samples', 'tf_id');
    }
}
