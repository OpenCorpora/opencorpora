<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_ne.php');

/*
    Сюда приходит POST'ом следующее:
    act - что нужно делать

*/


try {

    switch ($_POST['act']) {
        case 'newAnnotation':

            $tagset_id = get_current_tagset();

            if (empty($_POST['paragraph'])) throw new Exception();
            $id = start_ne_annotation($_POST['paragraph'], $tagset_id);
            $result['id'] = $id;
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
            $result['id'] = $id;
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

        case 'newMention':
            if (empty($_POST['entities']) || empty($_POST['object_type']))
                throw new UnexpectedValueException();
            $result['id'] = add_mention($_POST['entities'], $_POST['object_type']);
            break;

        case 'deleteMention':
            if (empty($_POST['mention']))
                throw new UnexpectedValueException();
            delete_mention($_POST['mention']);
            break;

        case 'deleteEntityFromMention':
            if (empty($_POST['entity']))
                throw new UnexpectedValueException();
            clear_entity_mention($_POST['entity']);
            break;

        case 'setMentionType':
            if (empty($_POST['mention']) || empty($_POST['object_type']))
                throw new UnexpectedValueException();
            update_mention($_POST['mention'], $_POST['object_type']);
            break;

        case 'addComment':
            if (empty($_POST['paragraph'])
             or empty($_POST['comment'])) throw new Exception();

            $id = add_comment_to_paragraph($_POST['paragraph'], $_SESSION['user_id'], $_POST['comment']);
            $result['id'] = $id;
            $result['time'] = date("M j, G:i");
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
                case 'mention':
                    log_event("{$_POST['event']} / mention_id: {$_POST['id']} / data: {$_POST['data']}");
                    break;
                default:
                    break;
            }
            break;

        default:
            $result['message'] = "Action not implemented: {$_POST['act']}";
            $result['error'] = 1;
            break;

    }
}
catch (Exception $e) {
    $result['error'] = 1;
    $result['error_message'] = $e->getMessage();
}

log_timing(true);
die(json_encode($result));
