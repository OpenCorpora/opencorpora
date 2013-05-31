<?php

class m130531_055830_badge_group extends CDbMigration
{
    public function up()
    {
        $this->addColumn('user_badges_types', 'badge_group', 'TINYINT UNSIGNED NOT NULL');
    }

    public function down()
    {
        $this->dropColumn('user_badges_types', 'badge_group');
    }
}
