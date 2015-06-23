<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class RemoveUserMetaOptions extends AbstractMigration
{
    public function up()
    {
        $this->dropTable('user_options');
    }

    public function down()
    {
        $opt = $this->table('user_options', array('id' => false, 'primary_key' => array('option_id')));
        $opt->addColumn('option_id', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_SMALL))
            ->addColumn('option_name', 'string', array('limit' => 128))
            ->addColumn('option_values', 'string', array('limit' => 64))
            ->addColumn('default_value', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_SMALL))
            ->addColumn('order_by', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_SMALL))
            ->save();

        $this->execute("INSERT INTO `user_options` VALUES
                            (1,'Показывать русские названия граммем','1',1,1),
                            (2,'Язык/Language','1=Русский|2=English',1,2),
                            (3,'Количество примеров для разметки','1=5|2=10|3=20|4=50',1,3),
                            (4,'Split annotation pools into pages','1',1,4),
                            (5,'Use fast mode in NE annotation','1',0,5);
                        ");
    }
}
