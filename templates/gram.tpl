{* Smarty *}
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='{$web_prefix}/css/main.css'/>
</head>
<body>
{include file='header.tpl'}
<div id='content'>
    <p><a href="?">&lt;&lt;&nbsp;назад</a></p>
    <h2>Группы граммем</h2>
    <b>Добавить группу</b>:
    <form action="?act=add_gg" method="post" class="inline">
        <input name="g_name" value="&lt;Название&gt;">
        <input type="submit" value="Добавить"/>
    </form>
    <br/><br/>
    <b>Добавить граммему</b>:<br/>
    <form action="?act=add_gram" method="post" class="inline">
        ID <input name="g_name" value="grm" size="10" maxlength="20"/>,
        AOT_ID <input name="aot_id" value="грм" size="10" maxlength="20"/>,
        группа <select name="group">{$editor.select}</select>,<br/>
        полное название <input name="descr" size="40"/>
        <input type="submit" value="Добавить"/>
    </form>
    <br/><br/>
    <table border="1" cellspacing="0" cellpadding="2">
        <tr><th>Название<th>AOT_id<th>Описание</tr>
        {foreach key=id item=group from=$editor.groups}
            <tr><td colspan="2"><b>{$group.name}</b><td>[<a href='#'>вверх</a>] [<a href='#'>вниз</a>]</tr>
            {foreach item=grammem from=$group.grammems}
                <tr><td>{$grammem.name}<td>{$grammem.aot_id|default:'&nbsp;'}<td>{$grammem.description}</tr>
            {/foreach}
        {/foreach}
    </table>
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
</body>
</html>
