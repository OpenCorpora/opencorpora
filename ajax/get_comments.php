<?php
require_once('../lib/header_ajax.php');

function recursive_print($id, $comm, $hier, &$ret) {
    if (isset($comm[$id]))
        $ret[] = array(
            'id' => $id,
            'timestamp' => $comm[$id]['ts'],
            'author' => $comm[$id]['author'],
            'reply_to' => $comm[$id]['parent'],
            'text' => htmlspecialchars($comm[$id]['text'])
        );
    if (!isset($hier[$id]))
        return;
    if (!$id) {
        foreach($hier[$id] as $cid) {
            recursive_print($cid, $comm, $hier, $ret);
        }
    } else {
        foreach(array_reverse($hier[$id]) as $cid) {
            recursive_print($cid, $comm, $hier, $ret);
        }
    }
}


if (!isset($_POST['sent_id'])) {
    $result['error'] = 1;
    die(json_encode($result));
}

$sent_id = (int)$_POST['sent_id'];

$comm = array();
$result['comments'] = array();
$res = sql_query("SELECT sc.*, u.user_name FROM sentence_comments sc LEFT JOIN users u ON (sc.user_id=u.user_id) WHERE sent_id=$sent_id ORDER BY timestamp");
while($r = sql_fetch_array($res)) {
    $comm[$r['comment_id']] = array(
        'ts' => date("d.m.y, H:i", $r['timestamp']),
        'author' => $r['user_name'],
        'parent' => $r['parent_id'],
        'text' => nl2br($r['text']),
    );
    $hier[$r['parent_id']][] = $r['comment_id'];
}
recursive_print(0, $comm, $hier, $result['comments']);

log_timing(true);
die(json_encode($result));
?>
