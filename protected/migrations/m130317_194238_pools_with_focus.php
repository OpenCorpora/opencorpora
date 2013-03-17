<?php

class m130317_194238_pools_with_focus extends CDbMigration
{
    public function up()
    {
        $this->addColumn('morph_annot_pool_types', 'has_focus', 'TINYINT UNSIGNED NOT NULL');
    }

    public function down()
    {
        $this->dropColumn('morph_annot_pool_types', 'has_focus');
    }
}
