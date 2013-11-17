<?php

class m131117_204826_syntax_users extends CDbMigration
{
    public function up()
    {
        $this->addColumn('syntax_groups', 'user_id', 'SMALLINT UNSIGNED NOT NULL');
    }

    public function down()
    {
        $this->dropColumn('syntax_groups', 'user_id');
    }
}
