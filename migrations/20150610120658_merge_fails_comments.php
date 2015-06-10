<?php

use Phinx\Migration\AbstractMigration;

class MergeFailsComments extends AbstractMigration
{
    public function change()
    {
        $tbl = $this->table('morph_annot_merge_comments', array('id' => false, 'primary_key' => array('sample_id')));
        $tbl->addColumn('sample_id', 'integer', array('signed' => false))
            ->addColumn('comment', 'text')
            ->create();
    }
}
