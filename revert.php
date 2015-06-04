<?php
require('lib/header.php');
require_once('lib/lib_history.php');
if (!is_logged() || !user_has_permission(PERM_DICT)) {
    header("Location:index.php");
    return;
}

if (isset($_POST['comment']))
    $comment = $_POST['comment'];
else
    $comment = '';

if (isset($_GET['set_id']) && $set_id = $_GET['set_id']) {
    $r = revert_changeset($set_id, $comment);
    header("Location:$r");
}
elseif (isset($_GET['tf_rev']) && $rev_id = $_GET['tf_rev']) {
    revert_token($rev_id);
    header("Location:history.php");
}
elseif (isset($_GET['dict_rev']) && $rev_id = $_GET['dict_rev']) {
    revert_dict($rev_id);
    header("Location:dict_history.php");
}
log_timing();
?>
