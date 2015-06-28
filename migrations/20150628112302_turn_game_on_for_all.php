<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class TurnGameOnForAll extends AbstractMigration
{
    public function up()
    {
        $users = $this->table("users");
        $users->removeColumn("show_game");
    }

    public function down()
    {
        $users = $this->table("users");
        $users->addColumn("show_game", "integer", array('signed' => false, 'limit' => MysqlAdapter::INT_TINY))
              ->save();
    }
}
