<?php
print "<b>Users:</b>";
$r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt FROM `users`"));
print ' '.$r['cnt'];
?>
