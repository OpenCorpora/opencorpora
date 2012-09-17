{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h1>{t}Ограничения на граммемы{/t}</h1>
{if $user_permission_dict}
<form id='addform' method='post' action='?act=add_restr'>
{t}Новое правило{/t}:
&laquo;{t}Если у{/t}
<select name='if_type'>
<option value='0'>{t}леммы{/t}</option>
<option value='2'>{t}формы{/t}</option>
</select>
{t}есть граммема{/t}
<select name='if'>
<option value='0'>({t}любая{/t})</option>
{html_options options=$restrictions.gram_options}
</select>,
{t}то у{/t}
<select name='then_type'>
<option value='0'>{t}леммы{/t}</option>
<option value='1'>{t}формы{/t}</option>
</select>
<select name='rtype'>
<option value='0'>{t}может быть{/t}</option>
<option value='1'>{t}должна быть{/t}</option>
<option value='2'>{t}должна отсутствовать{/t}</option>
</select>
{t}граммема{/t}
<select name='then'>
{html_options options=$restrictions.gram_options}
</select>
&raquo;.<br/>
<input type='submit' value='{t}Добавить{/t}'/>
</form>
{/if}
<p>
{if $user_permission_dict}
<a href="?act=update_restr">{t}Пересчитать{/t}</a> |
{/if}
{if isset($smarty.get.hide_auto)}
<a href="?act=gram_restr">{t}Показать выведенные{/t}</a>
{else}
<a href="?act=gram_restr&amp;hide_auto">{t}Скрыть выведенные{/t}</a>
{/if}
</p>
<table class="table">
<tr>
    <th>{t}Если{/t}</th>
    <th>{t}То{/t}</th>
    <th>{t}Действует на{/t}</th>
    <th>{t}Тип{/t}</th>
    <th>{t}Выведено?{/t}</th>
    {if $user_permission_dict}<th>&nbsp;</th>{/if}
</tr>
{foreach item=r from=$restrictions.list}
{if $r.type == 2}
<tr style='background-color: #FF6699'>
{elseif $r.type == 1}
<tr style='background-color: #CCFFCC'>
{else}
<tr>
{/if}
    <td>{if $r.if_id}{$r.if_id}{else}{t}Что угодно{/t}{/if}</td>
    <td>{$r.then_id}</td>
    <td>
        {if $r.obj_type == 0}{t}лемма{/t} &rarr; {t}лемма{/t}
        {elseif $r.obj_type == 1}{t}лемма{/t} &rarr; {t}форма{/t}
        {elseif $r.obj_type == 2}{t}форма{/t} &rarr; {t}лемма{/t}
        {else}{t}форма{/t} &rarr; {t}форма{/t}
        {/if}
    </td>
    <td>{if $r.type == 2}{t}запрещено{/t}{elseif $r.type == 1}{t}обязательно{/t}{else}{t}возможно{/t}{/if}</td>
    <td>{if $r.auto}{t}Да{/t}{else}{t}Нет{/t}{/if}</td>
    {if $user_permission_dict}<td><a href="?act=del_restr&amp;id={$r.id}" onclick="return confirm('{t}Вы уверены?{/t}')">x</a></td>{/if}
</tr>
{/foreach}
</table>
{/block}
