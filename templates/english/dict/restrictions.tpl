{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='english/header.tpl'}
<div id='content'>
<h1>Ограничения на граммемы</h1>
{if $is_admin}
<form id='addform' method='post' action='?act=add_restr'>
Новое правило:
&laquo;Если у
<select name='if_type'>
<option value='0'>леммы</option>
<option value='2'>формы</option>
</select>
есть граммема
<select name='if'>
<option value='0'>(любая)</option>
{html_options options=$restrictions.gram_options}
</select>,
то у
<select name='then_type'>
<option value='0'>леммы</option>
<option value='1'>формы</option>
</select>
<select name='rtype'>
<option value='0'>может быть</option>
<option value='1'>должна быть</option>
<option value='2'>должна отсутствовать</option>
</select>
граммема
<select name='then'>
{html_options options=$restrictions.gram_options}
</select>
&raquo;.<br/>
<input type='submit' value='Добавить'/>
</form>
{/if}
<p>
{if $is_admin}
<a href="?act=update_restr">Пересчитать</a> |
{/if}
{if isset($smarty.get.hide_auto)}
<a href="?act=gram_restr">Показать выведенные</a>
{else}
<a href="?act=gram_restr&amp;hide_auto">Скрыть выведенные</a>
{/if}
</p>
<table border='1' cellspacing='0' cellpadding='2'>
<tr>
    <th>Если</th>
    <th>То</th>
    <th>Действует на</th>
    <th>Тип</th>
    <th>Выведено?</th>
    {if $is_admin}<th>&nbsp;</th>{/if}
</tr>
{foreach item=r from=$restrictions.list}
{if $r.type == 2}
<tr style='background-color: #FF6699'>
{elseif $r.type == 1}
<tr style='background-color: #CCFFCC'>
{else}
<tr>
{/if}
    <td>{$r.if_id|default:'Что угодно'}</td>
    <td>{$r.then_id}</td>
    <td>
        {if $r.obj_type == 0}лемма &rarr; лемма
        {elseif $r.obj_type == 1}лемма &rarr; форма
        {elseif $r.obj_type == 2}форма &rarr; лемма
        {else}форма &rarr; форма
        {/if}
    </td>
    <td>{if $r.type == 2}запрещено{elseif $r.type == 1}обязательно{else}возможно{/if}</td>
    <td>{if $r.auto}Да{else}Нет{/if}</td>
    {if $is_admin}<td><a href="?act=del_restr&amp;id={$r.id}" onclick="return confirm('Вы уверены?')">x</a></td>{/if}
</tr>
{/foreach}
</table>
</div>
<div id='rightcol'>
{include file='english/right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
