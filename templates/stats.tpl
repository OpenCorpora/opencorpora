{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>{t}Статистика{/t}</h1>
{t}Пользователей{/t}: {$stats.cnt_users}<br/>
<a href="{$web_prefix}/books.php">{t}Книг{/t}</a>:</b> {$stats.cnt_books}<br/>
{t}Предложений{/t}: {$stats.cnt_sent}<br/>
{t}Словоупотреблений{/t}: {$stats.cnt_words}<br/>
<h2>{t}Словарь{/t}</h2>
{t}Лемм{/t}: {$stats.cnt_lemmata}<br/>
{t}Форм{/t}: {$stats.cnt_forms}
{/block}
