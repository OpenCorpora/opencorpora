<?php

use Phinx\Migration\AbstractMigration;

class AddTablesForSelectiveBackup extends AbstractMigration
{
    public function up()
    {
        $this->execute("CREATE TABLE `users_for_selective_backup` (
                `user_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
                `user_name` varchar(120) NOT NULL,
                `user_passwd` varchar(32) NOT NULL,
                `user_email` varchar(100) NOT NULL,
                `user_reg` int(10) unsigned NOT NULL,
                `user_shown_name` varchar(120) NOT NULL,
                `user_team` smallint(5) unsigned NOT NULL,
                `user_level` tinyint(3) unsigned NOT NULL,
                `user_shown_level` tinyint(3) unsigned NOT NULL,
                `user_rating10` int(10) unsigned NOT NULL,
                `show_game` tinyint(3) unsigned NOT NULL,
                PRIMARY KEY (`user_id`),
                KEY `user_team` (`user_team`),
                KEY `user_rating10` (`user_rating10`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

        $this->execute("CREATE TABLE `user_tokens_for_selective_backup` (
                `user_id` SMALLINT(5) UNSIGNED NOT NULL,
                `token` INT(10) UNSIGNED NOT NULL,
                `timestamp` INT(10) UNSIGNED NOT NULL,
                KEY `user_id` (`user_id`)
            ) ENGINE=INNODB DEFAULT CHARSET=utf8;");
    }

    public function down()
    {
        $this->dropTable("users_for_selective_backup");
        $this->dropTable("user_tokens_for_selective_backup");
    }
}