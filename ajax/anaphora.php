<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_anaphora_syntax.php');

/*
    Сюда приходит POST'ом следующее:

    act - что нужно делать

*/

try {
    // TODO: проверка на модератора книги
    if (!user_has_permission(PERM_DISAMB) && !user_has_permission(PERM_SYNTAX)) {
        throw new Exception("недостаточно прав");
    }

    switch ($_POST['act']) {
        case 'new':
            $result['aid'] = add_anaphora($_POST['anph_id'], $_POST['group_id']);
            $result['token_ids'] = json_encode(get_group_tokens((int)$_POST['group_id']));
            break;

        case 'delete':
            delete_anaphora($_POST['aid']);
            break;

        default:
            $result['error'] = 1;
            $result['message'] = "Action not implemented: {$_POST['act']}";
            break;

    }
}
catch (Exception $e) {
    $result['error'] = 1;
}

log_timing(true);
die(json_encode($result));
