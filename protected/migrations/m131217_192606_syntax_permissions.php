<?php

class m131217_192606_syntax_permissions extends CDbMigration
{
    public function up()
    {
        $this->addColumn('user_permissions', 'perm_syntax', 'TINYINT UNSIGNED NOT NULL');
        $this->addColumn('user_permissions', 'perm_check_syntax', 'TINYINT UNSIGNED NOT NULL');
    }

    public function down()
    {
        $this->dropColumn('user_permissions', 'perm_syntax');
        $this->dropColumn('user_permissions', 'perm_check_syntax');
    }
}
