<?php
require_once('../lib/header.php');
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><comments>';

if (!isset($_GET['sent_id']))
    return;

$sent_id = (int)$_GET['sent_id'];

$comm = array();
$res = sql_query("SELECT sc.*, u.user_name FROM sentence_comments sc LEFT JOIN users u ON (sc.user_id=u.user_id) WHERE sent_id=$sent_id ORDER BY timestamp");
while($r = sql_fetch_array($res)) {
    $comm[$r['comment_id']] = array(
        'ts' => date("d.m.y, H:i", $r['timestamp']),
        'author' => $r['user_name'],
        'parent' => $r['parent_id'],
        'text' => $r['text'],
    );
    $hier[$r['parent_id']][] = $r['comment_id'];
}
recursive_print(0);

echo '</comments>';

function recursive_print($id) {
    global $comm;
    global $hier;
    if (isset($comm[$id]))
        echo '<comment id="'.$id.'" ts="'.$comm[$id]['ts'].'" author="'.$comm[$id]['author'].'" reply="'.$comm[$id]['parent'].'">'.htmlspecialchars($comm[$id]['text']).'</comment>';
    if (!isset($hier[$id]))
        return;
    if (!$id) {
        foreach($hier[$id] as $cid) {
            recursive_print($cid);
        }
    } else {
        foreach(array_reverse($hier[$id]) as $cid) {
            recursive_print($cid);
        }
    }
}
?>
