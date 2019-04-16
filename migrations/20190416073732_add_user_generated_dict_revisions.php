<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddUserGeneratedDictRevisions extends AbstractMigration
{
    public function change()
    {
        $revs_ugc = $this->table('dict_revisions_ugc', array('id' => 'rev_id' , 'engine' => 'InnoDB'));
        $revs_ugc->addColumn('user_id', 'integer', array('signed' => false))
                 ->addColumn('created_ts', 'timestamp', array('default' => 'CURRENT_TIMESTAMP'))
                 ->addColumn('lemma_id', 'integer', array('signed' => false))
                 ->addColumn('rev_text', 'text')
                 ->addColumn('comment', 'text')
                 ->addColumn('status', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_TINY, 'default' => 0))
                 ->addIndex(['user_id'])
                 ->create();
    }
}
