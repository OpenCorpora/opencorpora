<?php

class m121109_162400_weekly_stats extends CDbMigration
{
    public function up()
    {
        $this->execute("
            INSERT INTO stats_param VALUES
            (58, 'annotators_week', 1),
            (59, 'annotators_divergence_week', 1),
            (60, 'annotators_moderated_week', 1),
            (61, 'annotators_correct_week', 1);
        ");
    }

    public function down()
    {
        $this->execute("DELETE FROM stats_param WHERE param_id > 57)");
    }
}
