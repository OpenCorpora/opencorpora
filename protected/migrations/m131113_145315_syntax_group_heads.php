<?php

class m131113_145315_syntax_group_heads extends CDbMigration
{
    public function up()
    {
        $this->addColumn('syntax_groups', 'head_id', 'INT UNSIGNED NOT NULL');
    }

    public function down()
    {
        $this->dropColumn('syntax_groups', 'head_id');
    }

}
