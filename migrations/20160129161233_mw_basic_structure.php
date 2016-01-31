<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class MwBasicStructure extends AbstractMigration {
    
    /**
     * Migrate Up.
     */
    public function up() {
        $main = $this->table("mw_main", array("id" => "mw_id", "engine" => "InnoDB"));
        $main
            ->addColumn('status', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_TINY))
            ->save();

        $tokens = $this->table("mw_tokens", array("id" => false, "primary_key" => array("mw_id", "tf_id"), "engine" => "InnoDB"));
        $tokens
            ->addColumn("mw_id", "integer")
            ->addColumn("tf_id", "integer", array("signed" => false))
            ->addIndex("mw_id")
            ->addIndex("tf_id")
            ->save();

        $answers = $this->table("mw_answers", array("id" => false, "primary_key" => array("mw_id", "user_id"), "engine" => "InnoDB"));
        $answers
            ->addColumn("mw_id", "integer")
            ->addColumn("user_id", "integer")
            ->addColumn("ts", "timestamp", array("default" => "CURRENT_TIMESTAMP"))
            ->addIndex("mw_id")
            ->addIndex("user_id")
            ->save();
    
    }

    /**
     * Migrate Down.
     */
    public function down() {
        $this->dropTable("mw_main");
        $this->dropTable("mw_tokens");
        $this->dropTable("mw_answers");

    }
}
