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
&laquo;Если у
<select name='obj'>
<option value='0'>формы</option>
<option value='1'>леммы</option>
</select>
есть граммемы
<select name='if1'>
<option value='0'>(любая)</option>
{$restrictions.gram_options}
</select>
и
<select name='if2'>
<option value='0'>(любая)</option>
{$restrictions.gram_options}
</select>,
то у неё
<select name='necess'>
<option value='0'>может быть</option>
<option value='1'>должна быть</option>
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
    <th colspan='2'>Если</th>
    <th>То</th>
    <th>Объект</th>
    <th>Обязательно?</th>
    <th>Выведено?</th>
    <th>&nbsp;</th>
</tr>
{foreach item=r from=$restrictions.list}
<tr>
    <td>{$r.id}</td>
    <td>{$r.if1_id|default:'&ndash;'}</td>
    <td>{$r.if2_id|default:'&ndash;'}</td>
    <td>{$r.then_id}</td>
    <td>{if $r.object}Лемма{else}Форма{/if}</td>
    <td>{if $r.necessary}Да{else}Нет{/if}</td>
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
