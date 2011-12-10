<?php
require('lib/header.php');
require('lib/lib_history.php');
if (!is_logged()) {
    header("Location:index.php");
    return;
}

if (isset($_POST['comment']))
    $comment = $_POST['comment'];
else
    $comment = '';

if (isset($_GET['set_id']) && $set_id = (int)$_GET['set_id']) {
    if ($r = revert_changeset($set_id, $comment)) {
        header("Location:$r");
    } else {
        show_error();
    }
}
elseif (isset($_GET['tf_rev']) && $rev_id = (int)$_GET['tf_rev']) {
    if (revert_token($rev_id)) {
        header("Location:history.php");
    } else {
        show_error();
    }
}
elseif (isset($_GET['dict_rev']) && $rev_id = (int)$_GET['dict_rev'] && revert_dict($rev_id)) {
    header("Location:dict_history.php");
} else {
    show_error();
}
?>
