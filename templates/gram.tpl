{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
    <p><a href="?">&lt;&lt;&nbsp;назад</a></p>
    <h2>Граммемы</h2>
    {if $is_admin}
    <b>Добавить граммему</b>:<br/>
    <form action="?act=add_gram" method="post" class="inline">
        Внутр. ID <input name="g_name" value="grm" size="10" maxlength="20"/>,
        внешн. ID <input name="outer_id" value="грм" size="10" maxlength="20"/>,
        родительская граммема <select name='parent_gram'><option value='0'>--Не выбрана--</option>{$select}</select>,<br/>
        описание <input name="descr" size="40"/>
        <input type="submit" value="Добавить"/>
    </form>
    <br/><br/>
    {/if}
    <form action="?act=edit_gram" method="post">
    <table border="1" cellspacing="0" cellpadding="2">
        <tr><th>Внутр. ID</th><th>Внешн. ID</th><th>Описание</th><th>Parent</th>{if $is_admin}<th>&nbsp;</th>{/if}</tr>
        {foreach key=id item=grammem from=$grammems}
            <tr class='{$grammem.css_class}'><td>{$grammem.name}</td><td>{$grammem.outer_id|default:'&nbsp;'}</td><td>{$grammem.description}</td><td>{$grammem.parent_name|default:'&mdash;'}</td>{if $is_admin}<td>[<a href='?act=move_gram&amp;dir=up&amp;id={$grammem.id}'>вверх</a>] [<a href='?act=move_gram&amp;dir=down&amp;id={$grammem.id}'>вниз</a>] [<a href='#' onClick='edit_gram(this, {$grammem.id}); return false;'>ред.</a>] [<a href='?act=del_gram&amp;id={$grammem.id}' onClick="return confirm('Вы уверены, что хотите удалить граммему?');">x</a>]</td>{/if}</tr>
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
