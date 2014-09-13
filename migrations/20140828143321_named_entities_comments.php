<?php

use Phinx\Migration\AbstractMigration;

class NamedEntitiesComments extends AbstractMigration
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
        $users = $this->table('ne_paragraph_comments', array('id' => 'comment_id', 'engine' => 'InnoDB'));
        $users->addColumn('user_id', 'integer')
            ->addColumn('par_id', 'integer')
            ->addColumn('comment', 'text')
            ->addColumn('created', 'timestamp', array('default' => 'CURRENT_TIMESTAMP'))
            ->addIndex('par_id')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('ne_paragraph_comments');
    }
}