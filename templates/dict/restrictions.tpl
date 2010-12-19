{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
<h1>Ограничения на граммемы</h1>
{if $is_admin}
<form id='addform' method='post' action='?act=add_restr'>
Новое правило:
&laquo;Если у формы есть граммема
<select name='if'>
<option value='0'>(любая)</option>
{$restrictions.gram_options}
</select>,
то у неё
<select name='rtype'>
<option value='0'>может быть</option>
<option value='1'>должна быть</option>
<option value='2'>должна отсутствовать</option>
</select>
граммема
<select name='then'>
{$restrictions.gram_options}
</select>
&raquo;.<br/>
<input type='submit' value='Добавить'/>
</form><br/>
{/if}
<table border='1' cellspacing='0' cellpadding='2'>
<tr>
    <th>id</th>
    <th>Если</th>
    <th>То</th>
    <th>Тип</th>
    <th>Выведено?</th>
    <th>&nbsp;</th>
</tr>
{foreach item=r from=$restrictions.list}
<tr>
    <td>{$r.id}</td>
    <td>{$r.if_id}</td>
    <td>{$r.then_id}</td>
    <td>{if $r.type == 2}запрещено{elseif $r.type == 1}обязательно{else}возможно{/if}</td>
    <td>{if $r.auto}Да{else}Нет{/if}</td>
    <td><a href="?act=del_restr&amp;id={$r.id}" onclick="return confirm('Вы уверены?')">x</a></td>
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
