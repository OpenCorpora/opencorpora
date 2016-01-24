<?php

use Phinx\Migration\AbstractMigration;

class AddPropOrder extends AbstractMigration
{

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("alter table ne_object_props add column `order` int unsigned not null default 0");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table("ne_object_props")->removeColumn("order");
    }
}