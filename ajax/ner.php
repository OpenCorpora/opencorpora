<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_ne.php');

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

        case 'becomeModerator':
            if (empty($_POST['book_id'])
                or empty($_POST['tagset_id'])) throw new Exception("book_id or tagset_id missing");

            set_ne_book_moderator((int)$_POST['book_id'], (int)$_POST['tagset_id']);
            break;

        case 'copyEntity':
            if (empty($_POST['entity_id'])
                or empty($_POST['annot_id'])) throw new Exception("entity_id or annot_id missing");
            $result['id'] = try_copy_ne_entity((int)$_POST['entity_id'], (int)$_POST['annot_id']);
            $result = array_merge($result, get_ne_entity_info($result['id']));
            break;

        case 'copyAllEntities':
            if (empty($_POST['annot_from'])
                or empty($_POST['annot_to'])) throw new Exception("one of annot ids missing");
            copy_all_entities($_POST['annot_from'], $_POST['annot_to']);
            break;

        case 'copyMention':
            if (empty($_POST['mention_id'])
                or empty($_POST['annot_id'])) throw new Exception("mention_id or annot_id missing");
            $result['id'] = copy_ne_mention((int)$_POST['mention_id'], (int)$_POST['annot_id']);
            break;

        case 'copyAll':
            // copy mentions and entities not in mentions
            if (empty($_POST['annot_from'])
                or empty($_POST['annot_to'])) throw new Exception("one of annot ids missing");
            copy_all_mentions_and_entities($_POST['annot_from'], $_POST['annot_to']);
            break;

        case 'createObject':
            if (empty($_POST['mentions']) || !is_array($_POST['mentions']))
                throw new UnexpectedValueException();
            $id = create_object_from_mentions($_POST['mentions']);
            $result['object_id'] = $id;
            $result['mentions'] = get_mentions_text_by_objects(array($id))[$id];
            break;

        case 'deleteObject':
            if (empty($_POST['object_id']))
                throw new UnexpectedValueException();
            delete_object($_POST['object_id']);
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
