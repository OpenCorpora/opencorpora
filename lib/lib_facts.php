<?php

function get_fact($fact_id) {
    $res = sql_pe("SELECT * FROM facts WHERE fact_id = ? LIMIT 1", array($fact_id));
    if (!sizeof($res))
        throw new Exception("Fact not found");
    return $res[0];
}

function create_fact($fact_type, $book_id, $fields) {
    sql_begin();
    sql_pe("INSERT INTO facts
        VALUES (NULL, ?, ?, ?)", array($book_id, $user_id, $fact_type));
    $fact_id = sql_insert_id();
    add_field_values($fact_id, $fact_type, $fields);
    sql_commit();
}

function update_fact($fact_id, $fact_type, $book_id, $fields) {
    $fact = get_fact($fact_id);
    if ($fact['user_id'] != $_SESSION['user_id'])
        throw new Exception("Insufficient permissions");
    sql_begin();
    sql_pe("UPDATE facts
        SET fact_type = ?, book_id = ?
        WHERE fact_id = ?
        LIMIT 1", array($fact_type, $book_id, $fact_id));
    sql_pe("DELETE FROM fact_field_values WHERE fact_id = ?", array($fact_id));
    add_field_values($fact_id, $fact_type, $fields);
    sql_commit();
}

function get_allowed_fields($fact_type) {
    $res = sql_pe("SELECT field_id, required, repeated, field_name FROM fact_fields WHERE fact_type_id = ?", array($fact_type));
    $allowed_fields = array();
    foreach ($res as $field)
        $allowed_fields[$field['field_id']] = $field;
    return $allowed_fields;
}

/**
 * Add bulk of field values with all checks 
 * fields = array(
 * array('field_type_id' => int, 'object_id' => int, 'entity_id' => int, 'string_value' => string), ...
 * )
 */
function add_field_values($fact_id, $fact_type, $fields) {
    $allowed_fields = get_allowed_fields($fact_type);
    sql_begin();
    foreach ($fields as $field) {
        $fid = $field['field_type_id'];
        if (!isset($allowed_fields[$fid]))
            throw new Exception("This field is not allowed for this fact type");
        if (!$allowed_fields[$fid]['repeated'] and isset($allowed_fields[$fid]['isset']))
            throw new Exception("Adding multiple values of a non-repeated field '" . $allowed_fields[$fld]['field_name'] . "' is not allowed");
        check_field_value($field);
        sql_pe("INSERT INTO fact_field_values
            VALUES (NULL, ?, ?, ?, ?, ?)", array($fid, $fact_id, $field['object_id'], $field['entity_id'], $field['string_value']));
        $allowed_fields[$fid]['isset'] = true;
    }
    foreach ($allowed_fields as $fld) {
        if ($fld['required'] and !isset($fld['isset']))
            throw new Exception("Required field '" . $fld['field_name'] . "' is not specified");
    }
    sql_commit();
}

function check_field_value($field) {
    // check if any value exists
    if (!$field['object_id'] && !$field['entity_id'] && !$field['string_value'])
        throw new Exception("Any value should be specified: object, entity or plain string");

    if ($field['object_id']) {
        // check if only one value exists
        if ($field['entity_id'] || $field['string_value'])
            throw new Exception("Only one value should be specified: object, entity or plain string");
        // check foreign key
        $check = sql_pe("SELECT object_id FROM ne_objects WHERE object_id = ? LIMIT 1", array($field['object_id']));
        if (!$check)
            throw new Exception("Object not found");
    }
    if ($field['entity_id']) {
        // check if only one value exists
        if ($field['string_value'])
            throw new Exception("Only one value should be specified: object, entity or plain string");
        // check foreign key
        $check = sql_pe("SELECT entity_id FROM ne_entities WHERE entity_id = ? LIMIT 1", array($field['entity_id']));
        if (!$check)
            throw new Exception("Entity not found");
    }
}

function delete_fact($fact_id) {
    sql_begin();
    sql_pe("DELETE FROM fact_field_values WHERE fact_id = ?", array($fact_id));
    sql_pe("DELETE FROM facts WHERE fact_id = ?", array($fact_id));
    sql_commit();
}
