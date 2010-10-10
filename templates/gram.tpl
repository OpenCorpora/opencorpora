{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
    <p><a href="?">&lt;&lt;&nbsp;назад</a></p>
    <h2>Группы граммем</h2>
    {if $is_admin}
    <b>Добавить группу</b>:
    <form action="?act=add_gg" method="post" class="inline">
        <input name="g_name" value="&lt;Название&gt;">
        <input type="submit" value="Добавить"/>
    </form>
    <br/><br/>
    <b>Добавить граммему</b>:<br/>
    <form action="?act=add_gram" method="post" class="inline">
        Внутр. ID <input name="g_name" value="grm" size="10" maxlength="20"/>,
        внешн. ID <input name="aot_id" value="грм" size="10" maxlength="20"/>,
        группа <select name="group">{$editor.select}</select>,<br/>
        описание <input name="descr" size="40"/>
        <input type="submit" value="Добавить"/>
    </form>
    <br/><br/>
    {/if}
    <form action="?act=edit_gram" method="post">
    <table border="1" cellspacing="0" cellpadding="2">
        <tr><th>Внутр. ID</th><th>Внешн. ID</th><th>Описание</th>{if $is_admin}<th>&nbsp;</th>{/if}</tr>
        {foreach key=id item=group from=$editor.groups}
            <tr><td colspan="2"><b>{$group.name}</b></td><td>&nbsp;</td><td>{if $is_admin}[<a href='?act=move_gg&amp;dir=up&amp;id={$id}'>вверх</a>] [<a href='?act=move_gg&amp;dir=down&amp;id={$id}'>вниз</a>] [<a href='?act=del_gg&amp;id={$id}' onClick="return confirm('Вы уверены?');">x</a>]{else}&nbsp;{/if}</td></tr>
            {foreach item=grammem from=$group.grammems}
                <tr><td>{$grammem.name}</td><td>{$grammem.aot_id|default:'&nbsp;'}</td><td>{$grammem.description}</td><td>{if $is_admin}[<a href='?act=move_gram&amp;dir=up&amp;id={$grammem.id}'>вверх</a>] [<a href='?act=move_gram&amp;dir=down&amp;id={$grammem.id}'>вниз</a>] [<a href='#' onClick='edit_gram(this, {$grammem.id}); return false;'>ред.</a>]{else}&nbsp;{/if}</td></tr>
            {/foreach}
        {/foreach}
    </table>
    </form>
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
