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
            start_ne_annotation((int)$_POST['paragraph']);
            break;

        case 'finishAnnotation':

            if (empty($_POST['paragraph'])) throw new Exception();
            finish_ne_annotation((int)$_POST['paragraph']);
            break;

        case 'newEntity':
            if (empty($_POST['paragraph'])
             or empty($_POST['tokens'])
             or empty($_POST['types'])) throw new Exception();

            list($par_id, $token_ids, $tags) = array((int)$_POST['paragraph'], $_POST['tokens'],
                                                     $_POST['types']);

            $id = add_ne_entity($par_id, $token_ids, $tags);
            $res['id'] = $id;
            break;

        case 'deleteEntity':
            if (empty($_POST['entity'])) throw new Exception();
            delete_ne_entity((int)$_POST['entity']);
            break;

        case 'setTypes':
            if (empty($_POST['entity'])
             or empty($_POST['types'])) throw new Exception();

            list($entity_id, $tags) = array((int)$_POST['entity'], $_POST['types']);
            set_ne_tags($entity_id, $tags);
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

die(json_encode($res));
