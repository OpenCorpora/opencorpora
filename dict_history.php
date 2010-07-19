<?php
require('lib/header.php');
?>
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='<?php echo $config['web_prefix']?>/css/main.css'/>
</head>
<body>
<?php require('include/_header.php'); ?>
<div id='content'>
<?php
if (isset($_GET['lemma_id']))
    $lemma_id = (int)$_GET['lemma_id'];
$res = sql_query("SELECT s.*, u.user_name, dl.lemma_id, dl.lemma_text FROM rev_sets s LEFT JOIN `users` u ON (s.user_id = u.user_id) LEFT JOIN `dict_revisions` dr ON (s.set_id = dr.set_id) RIGHT JOIN `dict_lemmata` dl ON (dr.lemma_id = dl.lemma_id)".($lemma_id?" WHERE dl.lemma_id=$lemma_id":"")." ORDER BY s.set_id DESC LIMIT 20");
print "<table border='1' cellspacing='0' cellpadding='3'>";
while ($r = sql_fetch_array($res)) {
    printf ("<tr><td>%d<td>%s<td>%s<td><a href=\"dict.php?act=edit&id=%d\">%s</a><td><a href=\"dict_diff.php?lemma_id=%d&set_id=%d\">Изменения</a></tr>", $r['set_id'], $r['user_id']?$r['user_name']:'Робот', strftime("%a %d.%m.%Y, %H:%M", $r['timestamp']), $r['lemma_id'], $r['lemma_text'], $r['lemma_id'], $r['set_id']);
}
print "</table>";
?>
</div>
<div id='rightcol'><?php require('include/_right.php'); ?></div>
</body>
</html>
