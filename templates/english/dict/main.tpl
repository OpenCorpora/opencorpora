{* Smarty *}
{include file='commonhtmlheader.tpl' title='Словарь'}
<body>
<div id='main'>
{include file='english/header.tpl'}
<div id='content'>
    <p>Всего {$stats.cnt_g} граммем, {$stats.cnt_l} лемм, {$stats.cnt_f} форм в индексе (не проверено {$stats.cnt_r} ревизий).</p>
    {if $is_admin}
        <p><a href="?act=gram">Редактор граммем</a><br/>
        <a href="?act=gram_restr">Ограничения на граммемы</a></p>
        <p><a href="?act=lemmata">Редактор лемм</a><br/>
        <a href="?act=errata">Ошибки в словаре</a> (не проверено {$stats.cnt_v} ревизий)</p>
        <p><button onClick="location.href='?act=edit&amp;id=-1'">Добавить лемму</button></p>
    {else}
        <p><a href="?act=gram">Просмотр граммем</a><br/>
        <a href="?act=gram_restr">Ограничения на граммемы</a></p>
        <p><a href="?act=lemmata">Просмотр лемм</a><br/>
        <a href="?act=errata">Ошибки в словаре</a> (не проверено {$stats.cnt_v} ревизий)</p>
    {/if}
</div>
<div id='rightcol'>
{include file='english/right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
