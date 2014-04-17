<?php

class m140417_153219_rename_text_forms_to_tokens extends CDbMigration
{
    public function up()
    {
        $this->renameTable('text_forms', 'tokens');
    }

    public function down()
    {
        $this->renameTable('tokens', 'text_forms');
    }
}
