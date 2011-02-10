{* Smarty *}
{include file='commonhtmlheader.tpl' title='Словарь'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
    <p>{t}Всего{/t} {$stats.cnt_g} {t}граммем{/t}, {$stats.cnt_l} {t}лемм{/t}, {$stats.cnt_f} {t}форм в индексе{/t} ({$stats.cnt_r} {t}ревизий не проверено{/t}).</p>
    {if $is_admin}
        <p><a href="?act=gram">{t}Редактор граммем{/t}</a><br/>
        <a href="?act=gram_restr">{t}Ограничения на граммемы{/t}</a></p>
        <p><a href="?act=lemmata">{t}Редактор лемм{/t}</a><br/>
        <a href="?act=errata">{t}Ошибки в словаре{/t}</a> ({$stats.cnt_v} {t}ревизий не проверено{/t})</p>
        <p><button onClick="location.href='?act=edit&amp;id=-1'">{t}Добавить лемму{/t}</button></p>
    {else}
        <p><a href="?act=gram">{t}Просмотр граммем{/t}</a><br/>
        <a href="?act=gram_restr">{t}Ограничения на граммемы{/t}</a></p>
        <p><a href="?act=lemmata">{t}Просмотр лемм{/t}</a><br/>
        <a href="?act=errata">{t}Ошибки в словаре{/t}</a> ({$stats.cnt_v} {t}ревизий не проверено{/t})</p>
    {/if}
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
