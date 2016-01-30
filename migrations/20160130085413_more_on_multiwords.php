<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class MoreOnMultiwords extends AbstractMigration
{
    public function change()
    {
        $this->table('mw_answers')
             ->addColumn('answer', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_TINY))
             ->save();

        $this->table('mw_main')
             ->addColumn('applied', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_TINY))
             ->save();
    }
}
