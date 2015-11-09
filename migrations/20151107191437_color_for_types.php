<?php

use Phinx\Migration\AbstractMigration;

class ColorForTypes extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table("ne_tags")
             ->addColumn("color_number", "integer", array("default" => 1))
             ->update();
        $this->table("ne_object_types")
             ->addColumn("color_number", "integer", array("default" => 1))
             ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table("ne_tags")
             ->removeColumn("color_number")
             ->update();
        $this->table("ne_object_types")
             ->removeColumn("color_number")
             ->update();
    }
}