<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_syntax.php');

/*
    Сюда приходит POST'ом следующее:

    act - что нужно делать

*/

header('Content-type: application/json');

$res = array('error' => 1);

// TODO: проверка на модератора книги
if (!user_has_permission('perm_disamb') && !user_has_permission('perm_syntax')) {
    die(json_encode($res));
}

switch ($_POST['act']) {
    case 'new':
        $aid = add_anaphora((int)$_POST['anph_id'], (int)$_POST['group_id']);
        if ($aid) {
            $res['aid'] = $aid;
            $res['error'] = 0;
            $res['token_ids'] = json_encode(get_group_tokens((int)$_POST['group_id']));
        }
        break;

    case 'delete':
        $res['error'] = (int)!delete_anaphora((int)$_POST['aid']);
        break;

    default:
        $res['message'] = "Action not implemented: {$_POST['act']}";
        break;

}

die(json_encode($res));
