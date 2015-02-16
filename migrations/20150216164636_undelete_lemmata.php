<?php

use Phinx\Migration\AbstractMigration;

class UndeleteLemmata extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("
            ALTER TABLE dict_lemmata
            ADD COLUMN `deleted` tinyint(3) unsigned not null;
        ");
        $this->execute("
            ALTER TABLE dict_lemmata
            ADD INDEX(`deleted`)
        ");
        $this->execute("
            INSERT INTO dict_lemmata (
                SELECT lemma_id, lemma_text, 1
                FROM dict_lemmata_deleted
            )
        ");
        $this->dropTable('dict_lemmata_deleted');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('dict_lemmata_deleted', array('id' => false))
             ->addColumn('lemma_id', 'integer')
             ->addColumn('lemma_text', 'string', array('limit' => 50))
             ->save();
        $this->execute("
            INSERT INTO dict_lemmata_deleted (
                SELECT lemma_id, lemma_text
                FROM dict_lemmata
                WHERE deleted=1
            )
        ");
        $this->execute("
            DELETE FROM dict_lemmata
            WHERE deleted=1
        ");
        $this->table('dict_lemmata')->removeIndex(array('deleted'));
        $this->table('dict_lemmata')->removeColumn('deleted');
    }
}
