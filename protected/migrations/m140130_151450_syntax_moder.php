<?php

class m140130_151450_syntax_moder extends CDbMigration
{
    public function up()
    {
        $this->addColumn('books', 'syntax_moder_id', 'SMALLINT UNSIGNED NOT NULL');
    }

    public function down()
    {
        $this->dropColumn('books', 'syntax_moder_id');
    }
}
