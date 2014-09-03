<?php

use Phinx\Migration\AbstractMigration;

class NamedEntitiesEventLog extends AbstractMigration
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
    	$users = $this->table('ne_event_log', array('id' => 'event_id', 'engine' => 'InnoDB'));
        $users->addColumn('user_id', 'integer')
            ->addColumn('message', 'text')
            ->addColumn('created', 'timestamp', array('default' => 'CURRENT_TIMESTAMP'))
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('ne_event_log');
    }
}