<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class NeAnnotNumberPerTagset extends AbstractMigration
{
    public function change()
    {
        $this->table("ne_tagsets")
            ->addColumn('annots_per_text', 'integer', array(
                'signed' => false,
                'limit' => MysqlAdapter::INT_TINY,
                'default' => 4
            ))
            ->addColumn('active_texts', 'integer', array(
                'signed' => false,
                'limit' => MysqlAdapter::INT_SMALL,
                'default' => 10   
            ))
            ->save();
    }
}
