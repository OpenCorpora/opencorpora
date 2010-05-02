<?php
$r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_users FROM `users`"));
print '<b>Пользователей:</b> '.$r['cnt_users'].'<br/>';
$r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_books FROM `books`"));
print '<b><a href="'.$config['web_prefix'].'/books.php">Книг</a>:</b> '.$r['cnt_books'].'<br/>';
$r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_sent FROM `sentences`"));
print '<br/><b>Предложений:</b> <a href="'.$config['web_prefix'].'/?rand">'.$r['cnt_sent'].'</a><br/>';
$r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_words FROM `text_forms`"));
print '<b>Сл/употр:</b> '.$r['cnt_words'].'<br/>';
print '<br/><b><a href="'.$config['web_prefix'].'/dict.php">Словарь</a></b><br/>';
$r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_lemmata FROM `dict_lemmata`"));
print '<b>Лемм:</b> '.$r['cnt_lemmata'].'<br/>';
$r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_form FROM `form2lemma`"));
print '<b>Форм:</b> '.$r['cnt_form'];
?>
