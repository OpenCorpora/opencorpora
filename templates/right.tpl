{* Smarty *}
{php}
$this->assign('dl', get_downloads_info());
{/php}
<div>
    <a href="{$web_prefix}/?page=publications">Публикации</a><br/>
</div>
<div>
    <a href="{$web_prefix}/dict.php">Словарь</a><br/>
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
{if $is_admin == 1}
<div>
    <b>Ревизия</b> {$svn_revision}
</div>
{/if}
