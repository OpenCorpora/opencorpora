<?php

use Phinx\Migration\AbstractMigration;

class LastDictRevision extends AbstractMigration
{
    public function up()
    {
        $rev = $this->table("dict_revisions");
        $rev->addColumn('is_last', 'boolean', array('signed' => false))
            ->addIndex(array('is_last'))
            ->save();

        $tmp = $this->table("tmp_dict_rev", array('id' => false));
        $tmp->addColumn('rev_id', 'integer')->save();

        $this->execute("INSERT INTO tmp_dict_rev (SELECT MAX(rev_id) FROM dict_revisions GROUP BY lemma_id)");
        $this->execute("UPDATE dict_revisions LEFT JOIN tmp_dict_rev USING (rev_id) SET is_last = 1 WHERE tmp_dict_rev.rev_id IS NOT NULL");

        $this->dropTable("tmp_dict_rev");
    }

    public function down()
    {
        $this->table("dict_revisions")->removeColumn('is_last')->update();
    }
}
