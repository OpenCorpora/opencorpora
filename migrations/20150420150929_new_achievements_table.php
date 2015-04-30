<?php

use Phinx\Migration\AbstractMigration;

class NewAchievementsTable extends AbstractMigration {

    /**
     * Migrate Up.
     */
    public function up() {
        $users = $this->table('user_achievements', array('id' => 'achievement_id', 'engine' => 'InnoDB'));

        $users
            ->addColumn('user_id', 'integer')
            ->addColumn('achievement_type', 'text')
            ->addColumn('level', 'integer', array('default' => 0))
            ->addColumn('progress', 'integer', array('default' => 0))
            ->addColumn('seen', 'boolean', array('default' => TRUE))
            ->addColumn('updated', 'timestamp', array('default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP'))
            ->addIndex('user_id')
            ->save();

            $this->execute("ALTER TABLE `user_achievements`
                ADD UNIQUE `user_id_achievement_type`
                (`user_id`, `achievement_type`(128));");
    }

    /**
     * Migrate Down.
     */
    public function down() {
        $this->dropTable('user_achievements');
    }
}