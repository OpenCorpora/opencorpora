<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class SimplifyUserRating extends AbstractMigration
{
    public function up()
    {
        $this->dropTable("user_rating_log");
        $this->table("morph_annot_pool_types")->removeColumn("rating_weight")->update();
    }

    public function down()
    {
        $log = $this->table("user_rating_log", array('id' => false));
        $log->addColumn('user_id', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_SMALL))
            ->addColumn('delta', 'integer', array('signed' => true, 'limit' => MysqlAdapter::INT_SMALL))
            ->addColumn('pool_id', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_SMALL))
            ->addColumn('timestamp', 'integer', array('signed' => false))
            ->addIndex(array('user_id'))
            ->addIndex(array('timestamp'))
            ->save();
        $this->table("morph_annot_pool_types")
             ->addColumn('rating_weight', 'integer', array('signed' => false, 'after' => 'has_focus', 'limit' => MysqlAdapter::INT_SMALL))
             ->save();
    }
}
