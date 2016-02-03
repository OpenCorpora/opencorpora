<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class MultiwordTypes extends AbstractMigration
{
    public function change()
    {
        $this->table("mw_main")
             ->addColumn('mw_type', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_TINY))
             ->save();
    }
}
