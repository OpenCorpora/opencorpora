<?php
function sentence_page($sent_id) {
    $tf_text = array();
    $res = sql_query("SELECT tf_id, tf_text FROM text_forms WHERE sent_id=$sent_id ORDER BY `pos`");
    while($r = sql_fetch_array($res)) {
        array_push ($tf_text, $r['tf_text']);
    }
    return implode(' ', $tf_text);
}
?>
