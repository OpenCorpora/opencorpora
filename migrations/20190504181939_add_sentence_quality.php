<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddSentenceQuality extends AbstractMigration
{
    public function change()
    {
        $sq = $this->table('sentence_quality', array('id' => false, 'engine' => 'InnoDB'));
        $sq->addColumn('length', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_SMALL))
           ->addColumn('status', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_TINY))
           ->addColumn('count', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_MEDIUM))
           ->create();
    }
}
