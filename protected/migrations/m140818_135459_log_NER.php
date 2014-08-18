<?php

class m140818_135459_log_NER extends CDbMigration
{
    public function up()
    {
        $this->renameColumn('ne_paragraphs', 'ts_finish', 'started_ts');
        $this->addColumn('ne_paragraphs', 'finished_ts', 'INT UNSIGNED NOT NULL');
    }

    public function down()
    {
        $this->renameColumn('ne_paragraphs', 'started_ts', 'ts_finish');
        $this->dropColumn('ne_paragraphs', 'finished_ts');
    }
}
