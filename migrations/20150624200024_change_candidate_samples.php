<?php

use Phinx\Migration\AbstractMigration;

class ChangeCandidateSamples extends AbstractMigration
{
    public function up()
    {
        $this->execute("TRUNCATE TABLE morph_annot_candidate_samples");  // yes, this is irreversible
        $cs = $this->table("morph_annot_candidate_samples");
        $cs->renameColumn("pool_id", "pool_type")->save();
    }

    public function down()
    {
        $cs = $this->table("morph_annot_candidate_samples");
        $cs->renameColumn("pool_type", "pool_id")->save();
    }
}
