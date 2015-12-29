<?php

use Phinx\Migration\AbstractMigration;

class AddModeratorColumnToNePar extends AbstractMigration {
    
    /**
     * Migrate Up.
     */
    public function up() {
        $this->table("ne_paragraphs")
             ->addColumn("is_moderator", "boolean", array("default" => false))
             ->update();
    }

    /**
     * Migrate Down.
     */
    public function down() {
        $this->table("ne_paragraphs")
             ->removeColumn("is_moderator")
             ->update();
    }
}
