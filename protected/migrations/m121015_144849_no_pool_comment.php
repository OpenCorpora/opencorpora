<?php

class m121015_144849_no_pool_comment extends CDbMigration
{
    public function up()
    {
        $this->dropColumn('morph_annot_pools', 'comment');
    }

    public function down()
    {
        $this->addColumn('morph_annot_pools', 'comment', 'TEXT NOT NULL');
    }
}
