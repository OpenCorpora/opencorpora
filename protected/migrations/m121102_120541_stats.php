<?php

class m121102_120541_stats extends CDbMigration
{
    public function up()
    {
        $this->execute("
            INSERT INTO stats_param VALUES
            (46, 'law_books', 1),
            (47, 'law_sentences', 1),
            (48, 'law_tokens', 1),
            (49, 'law_words', 1),
            (50, 'misc_books', 1),
            (51, 'misc_sentences', 1),
            (52, 'misc_tokens', 1),
            (53, 'misc_words', 1);
        ");
    }

    public function down()
    {
        $this->execute("DELETE FROM stats_param WHERE param_id > 45)");
    }
}
