<?php

class m121106_122529_nonfiction extends CDbMigration
{
    public function up()
    {
        $this->execute("
            INSERT INTO stats_param VALUES
            (54, 'nonfiction_books', 1),
            (55, 'nonfiction_sentences', 1),
            (56, 'nonfiction_tokens', 1),
            (57, 'nonfiction_words', 1);
        ");
    }

    public function down()
    {
        $this->execute("DELETE FROM stats_param WHERE param_id > 53)");
    }
}
