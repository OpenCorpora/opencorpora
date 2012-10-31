<?php

class m121031_084339_mod_answer extends CDbMigration
{
    public function up()
    {
        $this->addColumn('morph_annot_moderated_samples', 'manual', 'TINYINT UNSIGNED NOT NULL');
        $this->update(
            'morph_annot_moderated_samples',
            array('manual' => 1),
            'answer > 0'
        );
    }

    public function down()
    {
        $this->dropColumn('morph_annot_moderated_samples', 'manual');
    }
}
