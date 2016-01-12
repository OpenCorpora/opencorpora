<?php

use Phinx\Migration\AbstractMigration;

class NeObjectsProperties extends AbstractMigration {
    
    /**
     * Migrate Up.
     */
    public function up() {
        $props = $this->table("ne_object_props", array("id" => "prop_id", "engine" => "InnoDB"));
        $props
            ->addColumn("prop_key", "string", array("limit" => 100))
            ->save();
        $vals = $this->table("ne_object_prop_vals", array("id" => false, "primary_key" => array("object_id", "prop_id"), "engine" => "InnoDB"));
        $vals
            ->addColumn("object_id", "integer")
            ->addColumn("prop_id", "integer")
            ->addColumn("prop_val", "string")
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down() {
        $this->dropTable("ne_object_props");
        $this->dropTable("ne_object_prop_vals");
    }
}
