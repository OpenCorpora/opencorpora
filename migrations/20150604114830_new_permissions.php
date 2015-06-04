<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;
require_once(__DIR__.'/../lib/constants.php');

class NewPermissions extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        global $PERMISSION_MAP;
        $groups = $this->table('user_groups', array('id' => false, 'engine' => 'InnoDB'));
        $groups->addColumn('user_id', 'integer')
               ->addColumn('group_id', 'integer', array('limit' => MysqlAdapter::INT_TINY))
               ->addIndex(array('user_id', 'group_id'), array('unique' => true))
               ->save();

        $group_mapping = array_flip($PERMISSION_MAP);
        foreach ($this->fetchAll("SELECT * FROM user_permissions") as $row) {
            foreach ($group_mapping as $row_name => $group_id) {
                if ($row[$row_name] == 1)
                    $this->execute("INSERT INTO user_groups VALUES ({$row['user_id']}, $group_id)");
            }
        }

        $this->dropTable('user_permissions');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        global $PERMISSION_MAP;
        $this->execute("
            CREATE TABLE `user_permissions` (
              `user_id` smallint(5) unsigned NOT NULL,
              `perm_admin` tinyint(3) unsigned NOT NULL,
              `perm_adder` tinyint(3) unsigned NOT NULL,
              `perm_dict` tinyint(3) unsigned NOT NULL,
              `perm_disamb` tinyint(3) unsigned NOT NULL,
              `perm_check_tokens` tinyint(3) unsigned NOT NULL,
              `perm_check_morph` tinyint(3) unsigned NOT NULL,
              `perm_merge` tinyint(3) unsigned NOT NULL,
              `perm_syntax` tinyint(3) unsigned NOT NULL,
              `perm_check_syntax` tinyint(3) unsigned NOT NULL,
              `perm_check_ne` tinyint(3) unsigned NOT NULL,
              KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        $this->execute("
            INSERT INTO user_permissions (SELECT user_id, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 FROM users)
        ");
        foreach ($this->fetchAll("SELECT * FROM user_groups") as $row) {
            $this->execute("UPDATE user_permissions SET ".$PERMISSION_MAP[$row['group_id']]." = 1 WHERE user_id={$row['user_id']}");
        }

        $this->dropTable('user_groups');
    }
}
