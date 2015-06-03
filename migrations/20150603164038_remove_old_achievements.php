<?php

use Phinx\Migration\AbstractMigration;

class RemoveOldAchievements extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable("user_badges_types");       
        $this->dropTable("user_badges");       
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("
            CREATE TABLE `user_badges` (
              `user_id` smallint(5) unsigned NOT NULL,
              `badge_id` tinyint(3) unsigned NOT NULL,
              `shown` int(10) unsigned NOT NULL,
              KEY `user_id` (`user_id`),
              KEY `shown` (`shown`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE `user_badges_types` (
              `badge_id` tinyint(3) unsigned NOT NULL,
              `badge_name` varchar(127) NOT NULL,
              `badge_descr` text NOT NULL,
              `badge_image` varchar(255) NOT NULL,
              `badge_group` tinyint(3) unsigned NOT NULL,
              PRIMARY KEY (`badge_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }
}
