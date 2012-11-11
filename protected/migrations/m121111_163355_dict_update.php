<?php

class m121111_163355_dict_update extends CDbMigration
{
    public function up()
    {
        $this->addColumn('updated_forms', 'rev_id', 'INT UNSIGNED NOT NULL');
    }

    public function down()
    {
        $this->dropColumn('updated_forms', 'rev_id');
    }
}
