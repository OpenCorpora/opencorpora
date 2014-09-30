<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_syntax.php');

/*
    Сюда приходит POST'ом следующее:

    act - что нужно делать

*/

header('Content-type: application/json');

$res = array('error' => 0);

try {
    // TODO allow only perm_syntax
    if (!user_has_permission('perm_disamb') && !user_has_permission('perm_syntax'))
        throw new Exception();

    switch ($_POST['act']) {
        case 'newGroup':
            $res['gid'] = add_group($_POST['tokens'], (int)$_POST['type']);
            break;

        case 'copyGroup':
            $old_groups = get_groups_by_sentence((int)$_POST['sentence_id'],
              (int)$_SESSION['user_id']);

            $new_group_id = copy_group((int)$_POST['gid'], (int)$_SESSION['user_id']);
            $new_groups = get_groups_by_sentence((int)$_POST['sentence_id'],
              (int)$_SESSION['user_id']);

            $res['new_groups'] = array();
            $res['new_groups']['simple'] = arr_diff($new_groups['simple'],
                $old_groups['simple']);
            $res['new_groups']['complex'] = arr_diff($new_groups['complex'],
                $old_groups['complex']);

            break;

        case 'deleteGroup':
            delete_group($_POST['gid']);
            break;

        case 'setGroupHead':
            set_group_head($_POST['gid'], $_POST['head_id']);
            break;

        case 'setGroupType':
            set_group_type($_POST['gid'], $_POST['type']);
            break;

        case 'getGroupsTable':
            // Решил это вынести в аякс, потому что перерисовывать такую
            // табличку на клиенте - сложно, не используя шаблонизатор.

            // TODO: проверка, свое ли спрашивает пользователь

            require_once('Smarty.class.php');
            $smarty = new Smarty();
            $smarty->template_dir = $config['smarty']['template_dir'];
            $smarty->compile_dir  = $config['smarty']['compile_dir'];
            $smarty->config_dir   = $config['smarty']['config_dir'];
            $smarty->cache_dir    = $config['smarty']['cache_dir'];

            $smarty->assign('group_types', get_syntax_group_types());
            $smarty->assign('groups', get_groups_by_sentence((int)$_POST['sentence_id'],
              $_SESSION['user_id']));

            $res['table'] = $smarty->fetch('sentence_syntax_groups.tpl');
            break;

        default:
            $res['message'] = "Action not implemented: {$_POST['act']}";
            throw new Exception();

    }
}
catch (Exception $e) {
    $res['error'] = 1;
}

log_timing(true);
die(json_encode($res));
