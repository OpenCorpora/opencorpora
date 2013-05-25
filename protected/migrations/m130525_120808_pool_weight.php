<?php

class m130525_120808_pool_weight extends CDbMigration
{
    public function up()
    {
        $this->addColumn('morph_annot_pool_types', 'rating_weight', 'SMALLINT UNSIGNED NOT NULL');
    }

    public function down()
    {
        $this->dropColumn('morph_annot_pool_types', 'rating_weight');
    }
}
