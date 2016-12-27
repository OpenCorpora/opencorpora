<?php
require('lib/header.php');
require_once('lib/lib_history.php');

$set_id = GET('set_id', 0);
$comment = POST('comment', '');
$rev_id = GET('tf_rev', 0);
$dict_rev_id = GET('dict_rev', 0);

if ($set_id) {
    $r = revert_changeset($set_id, $comment);
    header("Location:$r");
}
elseif ($rev_id) {
    revert_token($rev_id);
    header("Location:history.php");
}
elseif ($dict_rev_id) {
    revert_dict($dict_rev_id);
    header("Location:dict_history.php");
}
log_timing();
?>
