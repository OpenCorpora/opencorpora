{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h1>Ограничения на граммемы</h1>
<ul class="breadcrumb">
    <li><a href="{$web_prefix}/dict.php">Словарь</a> <span class="divider">/</span></li>
    <li>Ограничения на граммемы</li>
</ul>
{if $user_permission_dict}
    <form id='addform' method='post' action='?act=add_restr' class="form-inline">
        <p><strong>Новое правило:</strong></p>
        <p><label for="if_type">&laquo;Если у</label>
        <select name='if_type'>
            <option value='0'>леммы</option>
            <option value='2'>формы</option>
        </select>
        <label for="if">есть граммема</label>
        <select name='if'>
            <option value='0'>(любая)</option>
            {html_options options=$restrictions.gram_options}
        </select>,</p>
        <p><label for="then_type">то у</label>
        <select name='then_type'>
            <option value='0'>леммы</option>
            <option value='1'>формы</option>
        </select>
        <select name='rtype'>
            <option value='0'>может быть</option>
            <option value='1'>должна быть</option>
            <option value='2'>должна отсутствовать</option>
        </select>
        <label for="then">граммема</label>
        <select name='then'>
            {html_options options=$restrictions.gram_options}
        </select>
        &raquo;.</p>
        <button type='submit' class="btn btn-primary">Добавить</button>
    </form>
{/if}
<p class="pull-right">
    {if $user_permission_dict}
        <a href="?act=update_restr" class="btn">Пересчитать</a> 
    {/if}
    {if isset($smarty.get.hide_auto)}
        <a href="?act=gram_restr" class="btn">Показать выведенные</a>
    {else}
        <a href="?act=gram_restr&amp;hide_auto" class="btn">Скрыть выведенные</a>
    {/if}
</p>
<table class="table">
<tr>
    <th>Если</th>
    <th>То</th>
    <th>Действует на</th>
    <th>Тип</th>
    <th>Выведено?</th>
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
    <td>{if $r.if_id}{$r.if_id}{else}Что угодно{/if}</td>
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
    {if $user_permission_dict}<td><a href="?act=del_restr&amp;id={$r.id}" onclick="return confirm('Вы уверены?')"><i class="icon-trash"></i></a></td>{/if}
</tr>
{/foreach}
</table>
{/block}
