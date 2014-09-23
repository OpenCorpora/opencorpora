<?php

use Phinx\Migration\AbstractMigration;

class AddPermissionNeModerator extends AbstractMigration
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
        // Phinx does not support tinyint, have to do this shit manually
        $this->execute("
            ALTER TABLE `user_permissions`
            ADD COLUMN `perm_check_ne` tinyint(3) unsigned not null;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('user_permissions')->removeColumn('perm_check_ne');
    }
}