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
$res = sql_query("SELECT s.*, u.user_name FROM rev_sets s LEFT JOIN `users` u ON (s.user_id = u.user_id) ORDER BY s.set_id DESC LIMIT 20");
while ($r = sql_fetch_array($res)) {
    printf ("<tr><td>%d<td>%s<td>%s</tr>", $r['set_id'], $r['user_id']?$r['user_name']:'Робот', strftime("%a %d.%m.%Y, %H:%m", $r['timestamp']));
}
?>
</table>
</div>
<div id='rightcol'><?php require('include/_right.php'); ?></div>
</body>
</html>
