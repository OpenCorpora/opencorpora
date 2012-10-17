<?php

class m121017_142157_pool_complexity extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn('morph_annot_pool_types', 'complexity', 'TINYINT UNSIGNED NOT NULL');
        $this->update(
            'morph_annot_pool_types',
            array('complexity' => 1),
            'type_id = 12'
        );
        $this->update(
            'morph_annot_pool_types',
            array('complexity' => 3),
            'type_id IN (7, 9, 11)'
        );
        $this->update(
            'morph_annot_pool_types',
            array('complexity' => 4),
            'type_id = 6'
        );
    }

    public function safeDown()
    {
        $this->dropColumn('morph_annot_pool_types', 'complexity');
    }
}
