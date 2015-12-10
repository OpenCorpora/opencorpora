<?php

use Phinx\Migration\AbstractMigration;

class AddNeBookModerator extends AbstractMigration {
    
    /**
     * Migrate Up.
     */
    public function up() {
        $this->table("ne_books_tagsets")
             ->addColumn("moderator_id", "integer", array("default" => 0))
             ->update();
    }

    /**
     * Migrate Down.
     */
    public function down() {
        $this->table("ne_books_tagsets")
             ->removeColumn("moderator_id")
             ->update();
    }
}
