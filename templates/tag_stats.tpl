{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>{t}Статистика{/t}</h1>
<h2><a href="?page=stats">{t}Общая{/t}</a> | {t}По тегам{/t}</h2>
<table border='1' cellspacing='0' cellpadding='3'>
{foreach from=$stats item=group key=gname}
<tr><th>{$gname}</th><th>текстов</th><th>слов</th></tr>
{foreach from=$group item=elem}
<tr><td>{$elem.value|htmlspecialchars}</td><td>{$elem.texts}</td><td>{$elem.words}</td></tr>
{/foreach}
{/foreach}
</table>
{/block}
