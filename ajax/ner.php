<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_ne.php');

try {

    switch (REQUEST('act', '')) {
        case 'newAnnotation':

            $tagset_id = get_current_tagset();

            $id = start_ne_annotation(POST('paragraph'), $tagset_id);
            $result['id'] = $id;
            break;

        case 'finishAnnotation':

            finish_ne_annotation(POST('paragraph'));
            break;

        case 'newEntity':
            list($par_id, $token_ids, $tags) = array(POST('paragraph'), POST('tokens'),
                                                     POST('types'));

            $id = add_ne_entity($par_id, $token_ids, $tags);
            $result['id'] = $id;
            break;

        case 'deleteEntity':
            delete_ne_entity(POST('entity'));
            break;

        case 'setTypes':
            list($entity_id, $tags) = array(POST('entity'), POST('types'));
            set_ne_tags($entity_id, $tags);
            break;

        case 'newMention':
            $result['id'] = add_mention(POST('entities'), POST('object_type'));
            break;

        case 'deleteMention':
            delete_mention(POST('mention'));
            break;

        case 'deleteEntityFromMention':
            clear_entity_mention(POST('entity'));
            break;

        case 'setMentionType':
            update_mention(POST('mention'), POST('object_type'));
            break;

        case 'addComment':
            $id = add_comment_to_paragraph(POST('paragraph'), $_SESSION['user_id'], POST('comment'));
            $result['id'] = $id;
            $result['time'] = date("M j, G:i");
            break;

        case 'becomeModerator':
            set_ne_book_moderator((int)POST('book_id'), (int)POST('tagset_id'));
            break;

        case 'copyEntity':
            $result['id'] = try_copy_ne_entity((int)POST('entity_id'), (int)POST('annot_id'));
            $result = array_merge($result, get_ne_entity_info($result['id']));
            break;

        case 'copyAllEntities':
            copy_all_entities(POST('annot_from'), POST('annot_to'));
            break;

        case 'copyMention':
            $result['id'] = copy_ne_mention((int)POST('mention_id'), (int)POST('annot_id'));
            break;

        case 'copyAll':
            // copy mentions and entities not in mentions
            copy_all_mentions_and_entities(POST('annot_from'), POST('annot_to'));
            break;

        case 'createObject':
            $id = create_object_from_mentions(POST('mentions'));

            $result['object_id'] = $id;
            $result['mentions'] = get_mentions_text_by_objects(array($id))[$id];
            break;

        case 'linkMentionToObject':
            link_mention_to_object(POST('mention_id'), POST('object_id'));
            break;

        case 'deleteMentionFromObject':
            link_mention_to_object(POST('mention_id'), 0);
            break;

        case 'updateObjectProperty':
            update_object_property(POST('val_id'), POST('prop_value'));
            break;

        case 'addObjectProperty':
            add_object_property(POST('object_id'), POST('prop_id'), "");
            break;

        case 'deleteProperty':
            delete_object_prop_val(POST('val_id'));
            break;

        case 'getObjects':
            $result['objects'] = get_book_objects(POST('book_id'));
            $result['possible_props'] = get_possible_properties();
            break;

        case 'searchWikidata':
            $result['api_response'] = search_wikidata(GET('search_query'));
            break;

        case 'deleteObject':
            delete_object(POST('object_id'));
            break;

        case 'finishModeration':
            finish_book_moderation(POST('book_id'), POST('tagset_id'));
            break;

        case 'restartModeration':
            restart_book_moderation(POST('book_id'), POST('tagset_id'));
            break;

        case 'resumeModeration':
            resume_book_moderation(POST('book_id'), POST('tagset_id'));
            break;

        case 'logEvent':
            $event = POST('event');
            $id = POST('id');
            $data = POST('data');

            switch (POST('type')) {
                case 'selection':
                    log_event("$event / par_id: $id / data: $data");
                    break;
                case 'entity':
                    log_event("$event / entity_id: $id / data: $data");
                    break;
                case 'mention':
                    log_event("$event / mention_id: $id / data: $data");
                    break;
                default:
                    break;
            }
            break;

        default:
            $result['message'] = "Action not implemented: " . POST('act', '');
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
