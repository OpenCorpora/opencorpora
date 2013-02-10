<?php

class m130210_100736_ma_merge_control extends CDbMigration
{
    public function up()
    {
        $this->addColumn('morph_annot_moderated_samples', 'merge_status', 'TINYINT UNSIGNED NOT NULL');
    }

    public function down()
    {
        $this->dropColumn('morph_annot_moderated_samples', 'merge_status');
    }
}
