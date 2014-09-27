<?php

use Phinx\Migration\AbstractMigration;

class AddDefaults extends AbstractMigration
{
    public static $newDefaults = array(
        'books' => array('syntax_on', 'old_syntax_moder_id', 'ne_on'),
        'dict_revisions' => array('f2l_check', 'dict_check'),
        'morph_annot_instances' => array('user_id', 'ts_finish', 'answer'),
        'morph_annot_moderated_samples' => array('user_id', 'answer', 'status', 'manual', 'merge_status'),
        'morph_annot_pool_types' => array('complexity', 'has_focus', 'rating_weight'),
        'morph_annot_pools' => array('moderator_id', 'status', 'revision'),
        'sentences' => array('check_status'),
        'sources' => array('user_id', 'book_id'),
        'syntax_groups_revisions' => array(array('is_last', 1)),
        'user_permissions' => array('perm_admin', 'perm_adder', 'perm_dict', 'perm_disamb', 'perm_check_tokens', 'perm_check_morph', 'perm_merge', 'perm_syntax', 'perm_check_syntax', 'perm_check_ne'),
        'users' => array('user_team', array('user_level', 1), array('user_shown_level', 1), 'user_rating10', 'show_game')
    );
    /**
     * Migrate Up.
     */
    public function up()
    {
        foreach (self::$newDefaults as $table => $fields) {
            foreach ($fields as $field)
                if (is_array($field))
                    $this->execute("ALTER TABLE $table ALTER $field[0] SET DEFAULT $field[1]");
                else
                    $this->execute("ALTER TABLE $table ALTER $field SET DEFAULT 0");
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        foreach (self::$newDefaults as $table => $fields) {
            foreach ($fields as $field)
                if (is_array($field))
                    $this->execute("ALTER TABLE $table ALTER $field[0] DROP DEFAULT");
                else
                    $this->execute("ALTER TABLE $table ALTER $field DROP DEFAULT");
        }
    }
}
