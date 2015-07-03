<?php

use Phinx\Migration\AbstractMigration;

class AddPoolProtoName extends AbstractMigration
{
    public function change()
    {
        $types = $this->table("morph_annot_pool_types");
        $types->addColumn('pool_proto_name', 'string', array('limit' => 120))
              ->update();
    }
}
