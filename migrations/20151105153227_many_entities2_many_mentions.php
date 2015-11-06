<?php

use Phinx\Migration\AbstractMigration;

class ManyEntities2ManyMentions extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up() {
        $this->table("ne_entities")
             ->removeColumn("mention_id")
             ->update();
        $this->table("ne_entities_mentions", array("id" => false, "primary_key" => array("entity_id", "mention_id"), "engine" => "InnoDB"))
             ->addColumn("entity_id", "integer")
             ->addColumn("mention_id", "integer")
             ->addIndex("entity_id")
             ->addIndex("mention_id")
             ->save();
    }

    /**
     * Migrate Down.
     */
    public function down() {
        $this->table('ne_entities')
             ->addColumn('mention_id', 'integer', array('default' => 0))
             ->update();
        $this->dropTable("ne_entities_mentions");
    }
}
