<?php
function get_page_tok_strange() {
    $out = array();
    $res = sql_query("SELECT ts.sent_id, ts.pos, ts.border, ts.coeff, s.source FROM tokenizer_strange ts LEFT JOIN sentences s ON (ts.sent_id=s.sent_id) ORDER BY ts.coeff DESC");
    while ($r = sql_fetch_array($res)) {
        $out[] = array(
            'sent_id' => $r['sent_id'],
            'coeff' => $r['coeff'], 
            'border' => $r['border'], 
            'lcontext' => mb_substr($r['source'], max(0, $r['pos']-10), min(10, $r['pos'])),
            'focus' => mb_substr($r['source'], $r['pos'], 1),
            'rcontext' => mb_substr($r['source'], $r['pos']+1, 9)
        );
    }
    return $out;
}
?>
