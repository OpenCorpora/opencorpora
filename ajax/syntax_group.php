<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_anaphora_syntax.php');
require_once('../lib/lib_diff.php');

/*
    Сюда приходит POST'ом следующее:

    act - что нужно делать

*/

try {
    $action = POST('act', '');
    switch ($action) {
        case 'newGroup':
            $result['gid'] = add_group(POST('tokens'), (int)POST('type'));
            break;

        case 'copyGroup':
            $old_groups = get_groups_by_sentence((int)POST('sentence_id'),
              (int)$_SESSION['user_id']);

            $new_group_id = copy_group((int)POST('gid'), (int)$_SESSION['user_id']);
            $new_groups = get_groups_by_sentence((int)POST('sentence_id'),
              (int)$_SESSION['user_id']);

            $result['new_groups'] = array();
            $result['new_groups']['simple'] = arr_diff($new_groups['simple'],
                $old_groups['simple']);
            $result['new_groups']['complex'] = arr_diff($new_groups['complex'],
                $old_groups['complex']);

            break;

        case 'deleteGroup':
            delete_group(POST('gid'));
            break;

        case 'setGroupHead':
            set_group_head(POST('gid'), POST('head_id'));
            break;

        case 'setGroupType':
            set_group_type(POST('gid'), POST('type'));
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
            $smarty->assign('groups', get_groups_by_sentence((int)POST('sentence_id'),
              $_SESSION['user_id']));

            $result['table'] = $smarty->fetch('sentence_syntax_groups.tpl');
            break;

        default:
            $result['message'] = "Action not implemented: $action";
            throw new Exception();

    }
}
catch (Exception $e) {
    $result['error'] = 1;
}

log_timing(true);
die(json_encode($result));
