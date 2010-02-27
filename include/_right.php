<?php
$r = sql_fetch_array(sql_query("SELECT COUNT(`users`.`user_id`) AS cnt_users FROM `users`"));
print '<b>Пользователей:</b> '.$r['cnt_users'].'<br/>';
$r = sql_fetch_array(sql_query("SELECT COUNT(`books`.`book_id`) AS cnt_books FROM `books`"));
print '<b><a href="'.$config['web_prefix'].'/books.php">Книг</a>:</b> '.$r['cnt_books'];
?>
