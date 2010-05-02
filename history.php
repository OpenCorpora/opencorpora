<?php
require('lib/header.php');
?>
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='<?=$config['web_prefix']?>/css/main.css'/>
</head>
<body>
<?php require('include/_header.php'); ?>
<div id='content'>
<table border='1' cellspacing='0' cellpadding='3'>
<?php
if (isset($_GET['sent_id']))
    $sent_id = (int)$_GET['sent_id'];
$res = sql_query("SELECT DISTINCT s.*, u.user_name, st.sent_id FROM rev_sets s LEFT JOIN `users` u ON (s.user_id = u.user_id) LEFT JOIN `tf_revisions` tr ON (s.set_id = tr.set_id) RIGHT JOIN `text_forms` tf ON (tr.tf_id = tf.tf_id) RIGHT JOIN `sentences` st ON (tf.sent_id = st.sent_id)".($sent_id?" WHERE st.sent_id=$sent_id":"")." ORDER BY s.set_id DESC, tr.rev_id LIMIT 20");
while ($r = sql_fetch_array($res)) {
    printf ("<tr><td>%d<td>%s<td>%s<td><a href=\"sentence.php?id=%d\">Предложение %d</a><td><a href=\"diff.php?sent_id=%d&set_id=%d\">Изменения</a></tr>", $r['set_id'], $r['user_id']?$r['user_name']:'Робот', strftime("%a %d.%m.%Y, %H:%m", $r['timestamp']), $r['sent_id'], $r['sent_id'], $r['sent_id'], $r['set_id']);
}
?>
</table>
</div>
<div id='rightcol'><?php require('include/_right.php'); ?></div>
</body>
</html>
