<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddNeTagsets extends AbstractMigration
{
    public function up()
    {
        $tagsets = $this->table('ne_tagsets', array('id' => false, 'primary_key' => 'tagset_id', 'engine' => 'InnoDB'));
        $tagsets->addColumn('tagset_id', 'integer', array('signed' => false, 'identity' => true, 'limit' => MysqlAdapter::INT_TINY))
                ->addColumn('tagset_name', 'string', array('limit' => 32))
                ->save();
        $this->execute("INSERT INTO ne_tagsets VALUES(1, 'NE_2014')");

        $this->table('ne_tags')
             ->addColumn('tagset_id', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_TINY))
             ->addIndex(array('tagset_id'))
             ->save();
        $this->execute("UPDATE ne_tags SET tagset_id = 1");

        $this->table('ne_paragraphs')
             ->addColumn('tagset_id', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_TINY))
             ->addIndex(array('tagset_id'))
             ->save();
        $this->execute("UPDATE ne_paragraphs SET tagset_id = 1");

        $this->table('ne_paragraph_comments')->renameColumn('par_id', 'annot_id')->update();
    }

    public function down()
    {
        $this->table('ne_tags')->removeColumn('tagset_id')->update();
        $this->table('ne_paragraphs')->removeColumn('tagset_id')->update();
        $this->table('ne_paragraph_comments')->renameColumn('annot_id', 'par_id')->update();
        $this->dropTable('ne_tagsets');
    }
}
