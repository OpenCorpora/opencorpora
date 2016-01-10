<?php

use Phinx\Migration\AbstractMigration;

class RemoveOldNeProp extends AbstractMigration
{
    public function up()
    {
        $this->table("ne_objects")
             ->removeColumn("canon_name")
             ->removeColumn("wikidata_id")
             ->update();
    }

    public function down()
    {
        $this->table("ne_objects")
             ->addColumn("canon_name", "string", array("limit" => 255))
             ->addColumn("wikidata_id", "integer")
             ->save();
    }
}
