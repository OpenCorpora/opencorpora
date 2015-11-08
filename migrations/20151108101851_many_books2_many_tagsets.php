<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class ManyBooks2ManyTagsets extends AbstractMigration {
    /**
     * Migrate Up.
     */
    public function up() {
        $link = $this->table("ne_books_tagsets", array("id" => false, "primary_key" => array("book_id", "tagset_id"), "engine" => "InnoDB"));
        $link
            ->addColumn("book_id", "integer")
            ->addColumn("tagset_id", "integer", array("signed" => false, "limit" => MysqlAdapter::INT_TINY))
            ->addIndex("book_id")
            ->addIndex("tagset_id")
            ->save();

        $this->execute("INSERT INTO ne_books_tagsets SELECT book_id, 1 FROM books where ne_on = 1");
        $this->table("books")->removeColumn("ne_on")->update();
    
    }

    /**
     * Migrate Down.
     */
    public function down() {
        $this->table("books")
             ->addColumn("ne_on", "boolean")
             ->addIndex("ne_on")
             ->update();
        $books_on = $this->fetchAll("SELECT book_id FROM ne_books_tagsets");
        $book_ids = array();
        foreach ($books_on as $book)
            $book_ids[] = $book["book_id"];
        $this->execute("UPDATE books SET ne_on = 1 WHERE book_id IN (" . implode(', ', $book_ids) . ")");
        $this->dropTable("ne_books_tagsets");
    }
}
