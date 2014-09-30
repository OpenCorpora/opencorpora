<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_ne.php');

/*
    Сюда приходит POST'ом следующее:
    act - что нужно делать

*/

header('Content-type: application/json');

$res = array('error' => 0);

try {

    switch ($_POST['act']) {
        case 'newAnnotation':

            if (empty($_POST['paragraph'])) throw new Exception();
            $id = start_ne_annotation($_POST['paragraph']);
            $res['id'] = $id;
            break;

        case 'finishAnnotation':

            if (empty($_POST['paragraph'])) throw new Exception();
            finish_ne_annotation($_POST['paragraph']);
            break;

        case 'newEntity':
            if (empty($_POST['paragraph'])
             or empty($_POST['tokens'])
             or empty($_POST['types'])) throw new Exception();

            list($par_id, $token_ids, $tags) = array($_POST['paragraph'], $_POST['tokens'],
                                                     $_POST['types']);

            $id = add_ne_entity($par_id, $token_ids, $tags);
            $res['id'] = $id;
            break;

        case 'deleteEntity':
            if (empty($_POST['entity'])) throw new Exception();
            delete_ne_entity($_POST['entity']);
            break;

        case 'setTypes':
            if (empty($_POST['entity'])
             or empty($_POST['types'])) throw new Exception();

            list($entity_id, $tags) = array($_POST['entity'], $_POST['types']);
            set_ne_tags($entity_id, $tags);
            break;

        case 'addComment':
            if (empty($_POST['paragraph'])
             or empty($_POST['comment'])) throw new Exception();

            $id = add_comment_to_paragraph($_POST['paragraph'], $_SESSION['user_id'], $_POST['comment']);
            $res['id'] = $id;
            $res['time'] = date("M j, G:i");
            break;

        case 'logEvent':
            if (empty($_POST['id'])) throw new Exception();
            switch ($_POST['type']) {
                case 'selection':
                    log_event("{$_POST['event']} / par_id: {$_POST['id']} / data: {$_POST['data']}");
                    break;
                case 'entity':
                    log_event("{$_POST['event']} / entity_id: {$_POST['id']} / data: {$_POST['data']}");
                    break;
                default:
                    break;
            }
            break;

        default:
            $res['message'] = "Action not implemented: {$_POST['act']}";
            $res['error'] = 1;
            break;

    }
}
catch (Exception $e) {
    $res['error'] = 1;
}

log_timing(true);
die(json_encode($res));
