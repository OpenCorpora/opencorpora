<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_annot.php');

$result['context'] = array();
$tf_id = (int)$_POST['tf_id'];
$dir = isset($_POST['dir']) ? (int)$_POST['dir'] : 0;
if ($tf_id && $dir) {
    $wds = get_context_for_word($tf_id, -1, $dir, 0);
    foreach ($wds['context'] as $word) {
        $result['context'][] = htmlspecialchars($word);
    }
}
log_timing(true);
die(json_encode($result));
?>
