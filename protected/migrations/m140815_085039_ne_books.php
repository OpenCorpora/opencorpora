<?php

class m140815_085039_ne_books extends CDbMigration
{
    public function up()
    {
        $this->addColumn('books', 'ne_on', 'TINYINT UNSIGNED NOT NULL');
        $this->createIndex('ne_on', 'books', 'ne_on');
        $this->createIndex('syntax_on', 'books', 'syntax_on');
    }

    public function down()
    {
        $this->dropIndex('books', 'ne_on');
        $this->dropIndex('books', 'syntax_on');
        $this->dropColumn('books', 'ne_on');
    }
}
