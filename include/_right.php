<?php
$r = sql_fetch_array(sql_query("SELECT COUNT(`users`.`user_id`) AS cnt_users, COUNT(`books`.`book_id`) AS cnt_books FROM `users`, `books`"));
print '<b>Users:</b> '.$r['cnt_users'].'<br/>';
print '<b>Books:</b> '.$r['cnt_books'];
?>
