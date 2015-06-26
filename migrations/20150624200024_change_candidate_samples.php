<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class ChangeCandidateSamples extends AbstractMigration
{
    public function up()
    {
        $this->execute("TRUNCATE TABLE morph_annot_candidate_samples");  // yes, this is irreversible
        $this->execute("DELETE FROM morph_annot_samples WHERE pool_id IN (
            SELECT pool_id FROM morph_annot_pools WHERE status < 2
        )");  // and this
        $this->execute("DELETE FROM morph_annot_pools WHERE status < 2");  // and this

        $cs = $this->table("morph_annot_candidate_samples");
        $cs->renameColumn("pool_id", "pool_type")->save();

        $types = $this->table("morph_annot_pool_types");
        $types->addColumn('last_auto_search', 'integer', array('signed' => false, 'default' => 0))->save();

        $this->table("morph_annot_pools")->removeColumn('token_check');
    }

    public function down()
    {
        $this->execute("TRUNCATE TABLE morph_annot_candidate_samples");  // yes, this is irreversible
        $cs = $this->table("morph_annot_candidate_samples");
        $cs->renameColumn("pool_type", "pool_id")->save();

        $types = $this->table("morph_annot_pool_types");
        $types->removeColumn('last_auto_search');

        $pools = $this->table("morph_annot_pools");
        $pools->addColumn('token_check', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_TINY, 'after' => 'pool_name'))
              ->save();
    }
}
