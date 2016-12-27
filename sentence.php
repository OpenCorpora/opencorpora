<?php
require_once('lib/header.php');
require_once('lib/lib_xml.php');
require_once('lib/lib_annot.php');
require_once('lib/lib_anaphora_syntax.php');
require_once('lib/lib_dict.php');
require_once('lib/lib_users.php');
require_once('lib/lib_books.php');

$id = (int)GET('id');
$action = GET('act', '');
$mode = GET('mode', 'morph');

switch ($action) {
    case 'save':
        sentence_save($id, POST('comment', ''), POST('var_flag'), POST('dict_flag', array()));
        header("Location:sentence.php?id=$id");
        break;
    case 'save_src':
        sentence_save_source($id, POST('src_text'));
        header("Location:sentence.php?id=$id");
        break;
    default:
        $smarty->assign('sentence', $sentence = get_sentence($id));
        if ($mode == 'syntax') {

            if ($sentence['syntax_moder_id']
                && $_SESSION['user_id'] == $sentence['syntax_moder_id']) {
                $smarty->assign('group_types', get_syntax_group_types());
                $smarty->assign('groups', get_groups_by_sentence($id, $_SESSION['user_id']));
                $smarty->assign('all_groups', $all = get_all_groups_by_sentence($id));

                $users = array();
                foreach (array_keys($all) as $uid) {
                    $users[$uid] = get_user_info($uid);
                }
                $smarty->assign('group_owners', $users);
                $smarty->display('sentence_syntax_moderator.tpl');

            } else {
                $smarty->assign('group_types', get_syntax_group_types());
                $smarty->assign('groups', get_groups_by_sentence($id, $_SESSION['user_id']));
                $smarty->display('sentence_syntax.tpl');
            }

        } else
            $smarty->display('sentence.tpl');
}
log_timing();
