<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class LongGoodSentences extends AbstractMigration
{
    public function up()
    {
        $gs = $this->table('good_sentences');
        $gs->changeColumn('num_words', 'integer', ['signed' => false, 'limit' => MysqlAdapter::INT_SMALL]);
        $gs->changeColumn('num_homonymous', 'integer', ['signed' => false, 'limit' => MysqlAdapter::INT_SMALL]);
    }

    public function down()
    {
        $gs = $this->table('good_sentences');
        $gs->changeColumn('num_words', 'integer', ['signed' => false, 'limit' => MysqlAdapter::INT_TINY]);
        $gs->changeColumn('num_homonymous', 'integer', ['signed' => false, 'limit' => MysqlAdapter::INT_TINY]);
    }
}
