<?php
require('lib/header.php');
require_once('lib/lib_xml.php');
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
$sent_id = (int)$_GET['sent_id'];
$set_id = (int)$_GET['set_id'];
$r = sql_fetch_array(sql_query("SELECT DISTINCT s.*, u.user_name FROM rev_sets s LEFT JOIN `users` u ON (s.user_id = u.user_id) WHERE s.set_id=$set_id"));
print "<h2>Предложение $sent_id, изменил ".$r['user_name'].strftime(" %d.%m.%Y в %H:%m", $r['timestamp']).'</h2>';
$res = sql_query("SELECT tf_id, `pos` FROM text_forms WHERE sent_id=$sent_id ORDER BY `pos`");
while($r = sql_fetch_array($res)) {
    $res1 = sql_query("SELECT tr.*, rs.*, `users`.user_name FROM tf_revisions tr LEFT JOIN rev_sets rs ON (tr.set_id = rs.set_id) LEFT JOIN `users` ON (rs.user_id = `users`.user_id) WHERE tr.tf_id=".$r['tf_id']." AND tr.set_id<=$set_id ORDER BY tr.rev_id DESC LIMIT 2");
    $r1 = sql_fetch_array($res1);
    $r2 = sql_fetch_array($res1);
    if ($r2 && $r1['set_id']==$set_id) {
        print "<tr><th colspan='2'>".$r['pos']."</tr><tr><td valign='top'><b>Версия ".$r2['rev_id']." (".$r2['user_name'].", ".strftime("%d.%m.%Y, %H:%m", $r2['timestamp']).")</b><pre>".htmlspecialchars(format_xml($r2['rev_text']))."</pre>";
        print "<td valign='top'><b>Версия ".$r1['rev_id']." (".$r1['user_name'].", ".strftime("%d.%m.%Y, %H:%m", $r1['timestamp']).")</b><pre>".htmlspecialchars(format_xml($r1['rev_text']))."</pre></tr>";
    } elseif ($r1['set_id']==$set_id) {
        print "<tr><th colspan='2'>".$r['pos']."</tr><tr><td valign='top'><b>Новое предложение</b>";
        print "<td valign='top'><b>Версия ".$r1['rev_id']." (".$r1['user_name'].", ".strftime("%d.%m.%Y, %H:%m", $r1['timestamp']).")</b><pre>".htmlspecialchars(format_xml($r1['rev_text']))."</pre></tr>";
    }
}
?>
</table>
</div>
<div id='rightcol'><?php require('include/_right.php'); ?></div>
</body>
</html>
