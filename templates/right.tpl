{* Smarty *}
{php}
$this->assign('dl', get_downloads_info());
$this->assign('dict_errors', sql_num_rows(sql_query("SELECT error_id FROM dict_errata LIMIT 1")));
{/php}
<div>
    <a href="{$web_prefix}/?page=about">О проекте</a><br/>
    <a href="{$web_prefix}/?page=publications">Публикации</a><br/>
</div>
<div>
    <a href="{$web_prefix}/dict.php">Словарь</a>
        {if $is_admin && $dict_errors}(<a class="red" href="{$web_prefix}/dict.php?act=errata">есть ошибки</a>){/if}<br/>
    <a href="{$web_prefix}/?page=stats">Статистика</a><br/>
    <a href="{$web_prefix}/?rand">Случайное предложение</a>
</div>
<div>
    <b>Свежие правки</b><br/>
    <a href='{$web_prefix}/history.php'>В разметке</a><br/>
    <a href='{$web_prefix}/dict_history.php'>В словаре</a>
</div>
<b>Скачать:</b>
<div class='small'>
    <a href="{$web_prefix}/files/export/dict/dict.opcorpora.xml.bz2">Словарь</a> (обновлён {$dl.dict.updated}, {$dl.dict.size} Мб)
</div>
{if $is_admin}
<div>
    <b>Ревизия</b> {$svn_revision}
</div>
{/if}
