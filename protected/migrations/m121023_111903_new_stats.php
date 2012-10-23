<?php

class m121023_111903_new_stats extends CDbMigration
{
    public function up()
    {
        $this->execute("
            INSERT INTO stats_param VALUES
            (40, 'dump_full_sentences', 1),
            (41, 'dump_disamb_sentences', 1),
            (42, 'dump_full_tokens', 1),
            (43, 'dump_disamb_tokens', 1),
            (44, 'dump_full_words', 1),
            (45, 'dump_disamb_words', 1);
        ");
    }

    public function down()
    {
        $this->execute("DELETE FROM stats_param WHERE param_id > 39)");
    }
}
