<?php

use Phinx\Migration\AbstractMigration;

class MoveObjectTypeTo2Level extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up() {
        $this->table("ne_objects")
             ->removeColumn("object_type_id")
             ->update();

        $this->table("ne_mentions")
             ->addColumn('object_type_id', 'integer')
             ->update();
    }

    /**
     * Migrate Down.
     */
    public function down() {
        $this->table("ne_mentions")
             ->removeColumn("object_type_id")
             ->update();

        $this->table("ne_objects")
             ->addColumn('object_type_id', 'integer')
             ->update();
    }
}
