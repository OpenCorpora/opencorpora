<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_syntax.php');

/*
    Сюда приходит POST'ом следующее:

    act - что нужно делать

*/

header('Content-type: application/json');

$res = array('error' => 1);

try {
    // TODO: проверка на модератора книги
    if (!user_has_permission('perm_disamb') && !user_has_permission('perm_syntax')) {
        throw new Exception("недостаточно прав");
    }

    switch ($_POST['act']) {
        case 'new':
            $res['aid'] = add_anaphora($_POST['anph_id'], $_POST['group_id']);
            $res['error'] = 0;
            $res['token_ids'] = json_encode(get_group_tokens((int)$_POST['group_id']));
            break;

        case 'delete':
            delete_anaphora($_POST['aid']);
            $res['error'] = 0;
            break;

        default:
            $res['message'] = "Action not implemented: {$_POST['act']}";
            break;

    }
}
catch (Exception $e) {
    $res['error'] = 1;
}

log_timing(true);
die(json_encode($res));
