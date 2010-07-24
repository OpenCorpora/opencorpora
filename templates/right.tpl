{* Smarty *}
{php}
$this->assign('stats', get_common_stats());
{/php}
<b>Пользователей:</b> {$stats.cnt_users}<br/><br/>
<b>Свежие правки</b><br/>
<a href='{$web_prefix}/history.php'>В разметке</a><br/>
<a href='{$web_prefix}/dict_history.php'>В словаре</a><br/>
<br/><b><a href="{$web_prefix}/books.php">Книг</a>:</b> {$stats.cnt_books}<br/>
<b>Предложений:</b> <a href="{$web_prefix}/?rand">{$stats.cnt_sent}</a><br/>
<b>Сл/употр:</b> {$stats.cnt_words}<br/>
<br/><b><a href="{$web_prefix}/dict.php">Словарь</a></b><br/>
<b>Лемм:</b> {$stats.cnt_lemmata}<br/>
<b>Форм:</b> {$stats.cnt_forms}
