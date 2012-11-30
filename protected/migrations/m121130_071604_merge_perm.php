<?php

class m121130_071604_merge_perm extends CDbMigration
{
    public function up()
    {
        $this->addColumn('user_permissions', 'perm_merge', 'TINYINT UNSIGNED NOT NULL');
    }

    public function down()
    {
        $this->dropColumn('user_permissions', 'perm_merge');
    }
}
