<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class DropSentenceCheck extends AbstractMigration
{
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable("sentence_check");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $tbl = $this->table("sentence_check", array("id" => false,  "engine" => "InnoDB"));
        $tbl->addColumn('sent_id', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_MEDIUM))
            ->addColumn('user_id', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_SMALL))
            ->addColumn('status', 'integer',  array('signed' => false, 'limit' => MysqlAdapter::INT_TINY))
            ->addColumn('timestamp', 'integer', array('signed' => false))
            ->addIndex('sent_id')
            ->addIndex('user_id')
            ->save();
    }
}
