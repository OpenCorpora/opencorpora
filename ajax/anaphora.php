<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_anaphora_syntax.php');

/*
    Сюда приходит POST'ом следующее:

    act - что нужно делать

*/

try {
    // TODO: проверка на модератора книги
    $action = POST('act', '');
    switch ($action) {
        case 'new':
            $result['aid'] = add_anaphora(POST('anph_id'), POST('group_id'));
            $result['token_ids'] = json_encode(get_group_tokens((int)POST('group_id')));
            break;

        case 'delete':
            delete_anaphora(POST('aid'));
            break;

        default:
            $result['error'] = 1;
            $result['message'] = "Action not implemented: $action";
            break;

    }
}
catch (Exception $e) {
    $result['error'] = 1;
}

log_timing(true);
die(json_encode($result));
