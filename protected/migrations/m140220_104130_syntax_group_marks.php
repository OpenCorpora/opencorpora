<?php

class m140220_104130_syntax_group_marks extends CDbMigration
{
    public function up()
    {
        $this->addColumn('syntax_groups', 'marks', "ENUM ('bad', 'suspicious', 'no head')");
    }

    public function down()
    {
        $this->dropColumn('syntax_groups', 'marks');
    }
}
