<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_syntax.php');

/*
    Сюда приходит POST'ом следующее:

    act - что нужно делать

*/

header('Content-type: application/json');

$res = array('error' => 1);

// TODO allow only perm_syntax
if (!user_has_permission('perm_disamb') && !user_has_permission('perm_syntax')) {
    die(json_encode($res));
}

switch ($_POST['act']) {
    case 'newGroup':
        $gid = add_group($_POST['tokens'], (int)$_POST['type']);
        if ($gid) {
            $res['gid'] = $gid;
            $res['error'] = 0;
        }
        break;

    case 'copyGroup':
        $old_groups = get_groups_by_sentence((int)$_POST['sentence_id'],
          (int)$_SESSION['user_id']);

        $new_group_id = copy_group((int)$_POST['gid'], (int)$_SESSION['user_id']);
        if (!$new_group_id) {
            $res['error'] = 1;
        }
        else {
            $res['error'] = 0;
            $new_groups = get_groups_by_sentence((int)$_POST['sentence_id'],
          (int)$_SESSION['user_id']);

            $res['new_groups'] = array();
            $res['new_groups']['simple'] = arr_diff($new_groups['simple'],
                $old_groups['simple']);
            $res['new_groups']['complex'] = arr_diff($new_groups['complex'],
                $old_groups['complex']);

        }
        break;

    case 'deleteGroup':
        $res['error'] = (int)!delete_group((int)$_POST['gid']);
        break;


    case 'setGroupHead':
        $res['error'] = (int)!set_group_head((int)$_POST['gid'], (int)$_POST['head_id']);
        break;

    case 'setGroupType':
        $res['error'] = (int)!set_group_type((int)$_POST['gid'], (int)$_POST['type']);
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
        $res['error'] = 0;
        break;


    default:
        $res['message'] = "Action not implemented: {$_POST['act']}";
        break;

}

die(json_encode($res));
