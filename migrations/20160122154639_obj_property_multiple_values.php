<?php

use Phinx\Migration\AbstractMigration;

class ObjPropertyMultipleValues extends AbstractMigration {
    
    /**
     * Migrate Up.
     */
    public function up() {
        $this->execute("alter table ne_object_prop_vals drop primary key");
        $this->execute("alter table ne_object_prop_vals add column val_id int unsigned not null auto_increment first, add primary key (val_id)");
    }

    /**
     * Migrate Down.
     */
    public function down() {
        $this->table("ne_object_prop_vals")->removeColumn("val_id")->update();
        $this->execute("alter table ne_object_prop_vals add primary key (object_id, prop_id)");
    }
}
