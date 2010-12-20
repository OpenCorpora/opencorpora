{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
<h1>Контроль словаря</h1>
<p>Не проверено {$errata.lag} ревизий, всего {$errata.total} ошибок.
{if $errata.total > 200}
    {if isset($smarty.get.all)}
    (<a href="?act=errata">Показать только первые 200</a>.)
    {elseif isset($smarty.get.rand)}
    (Показаны 200 случайных. <a href="?act=errata&amp;all">Показать все</a>, <a href="?act=errata">показать первые 200</a>.)
    {else}
    (Показаны первые 200. <a href="?act=errata&amp;all">Показать все</a>, <a href="?act=errata&amp;rand">показать 200 случайных</a>.)
    {/if}
{/if}
</p>
{if $is_admin}
<p>Сбросить флаг проверки <a href="?act=clear_errata&amp;old" onclick="return confirm('Вы уверены?')">у всех ревизий</a>, <a href="?act=clear_errata" onclick="return confirm('Вы уверены?')">у текущих ревизий</a>.</p>
{/if}
<table border='1' cellspacing='0' cellpadding='2'>
<tr>
    <th>id</th>
    <th>Время проверки</th>
    <th>Ревизия</th>
    <th>Тип ошибки</th>
    <th>Описание</th>
</tr>
{foreach item=error from=$errata.errors}
<tr>
    <td>{$error.id}</td>
    <td>{$error.timestamp|date_format:"%d.%m.%Y, %H:%M"}</td>
    <td><a href="{$web_prefix}/dict_diff.php?lemma_id={$error.lemma_id}&amp;set_id={$error.set_id}">{$error.revision}</a></td>
    <td>
        {if $error.type == 1}
            Несовместимые граммемы 
        {elseif $error.type == 2}
            Неизвестная граммема
        {elseif $error.type == 3}
            Формы-дубликаты
        {elseif $error.type == 4}
            Нет обязательной граммемы
        {/if}
    </td>
    <td>{$error.description}</td>
</tr>
{/foreach}
</table>
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
