<?php

class m131217_190740_syntax_coord extends CDbMigration
{
    public function up()
    {
        $this->addColumn('books', 'syntax_on', 'TINYINT UNSIGNED NOT NULL');
    }

    public function down()
    {
        $this->dropColumn('books', 'syntax_on');
    }
}
