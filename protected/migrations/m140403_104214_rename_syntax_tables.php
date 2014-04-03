<?php

class m140403_104214_rename_syntax_tables extends CDbMigration
{
    public function up()
    {
        $this->renameTable('syntax_annotators', 'anaphora_syntax_annotators');
        $this->renameTable('syntax_group_types', 'anaphora_syntax_group_types');
        $this->renameTable('syntax_groups', 'anaphora_syntax_groups');
        $this->renameTable('syntax_groups_simple', 'anaphora_syntax_groups_simple');
        $this->renameTable('syntax_groups_complex', 'anaphora_syntax_groups_complex');
    }

    public function down()
    {
        $this->renameTable('anaphora_syntax_annotators', 'syntax_annotators');
        $this->renameTable('anaphora_syntax_group_types', 'syntax_group_types');
        $this->renameTable('anaphora_syntax_groups', 'syntax_groups');
        $this->renameTable('anaphora_syntax_groups_simple', 'syntax_groups_simple');
        $this->renameTable('anaphora_syntax_groups_complex', 'syntax_groups_complex');
    }
}
