{* Smarty *}
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='{$web_prefix}/css/main.css'/>
<script language='JavaScript' src='{$web_prefix}/js/main.js'></script>
</head>
<body>
{include file='header.tpl'}
<div id='content'>
<h1>Настройки</h1>
{if $smarty.get.saved == 1}
<p>Настройки сохранены.</p>
{/if}
<form action="?act=save" method="post">
<table cellpadding='5'>
{foreach key=id item=option from=$meta}
    <tr>
        <td>{$option.name}
        <td>
        {if $option.value_type == 'string'}
            <input name='options[{$id}]' value='{$smarty.session.options[$id]|htmlspecialchars}'/>
        {else}
            <select name='options[{$id}]'>
                {html_options values=$option.values output=$option.values selected=$smarty.session.options[$id]}
            </select>
        {/if}
    </tr>
{/foreach}
</table>
<input type='submit' value='Сохранить'/>&nbsp;&nbsp;<input type='reset' value='Отменить'/>
</form>
{if $is_admin}
<h2>Readonly</h2>
<input type='button' value='Включить' onClick="if (confirm('Are you sure?')) location.href='?act=readonly_on'" {if $readonly}disabled='disabled'{/if}/>
<input type='button' value='Выключить' onClick="if (confirm('Are you sure?')) location.href='?act=readonly_off'" {if $readonly == 0}disabled='disabled'{/if}/>
<h2>Настройки настроек</h2>
<form action="?act=save_meta" method="post">
<table cellpadding='5' id='tbl_meta_options'>
{foreach key=id item=option from=$meta}
    <tr>
    <td><input name='option_names[{$id}]' value='{$option.name|htmlspecialchars}'/>
    <td><input name='option_values[{$id}]' value='{$option.value_type|htmlspecialchars}'/>
    <td><input name='option_default[{$id}]' type='hidden'/>
    </tr>
{/foreach}
</table>
<input type='button' value='Добавить опцию' onClick='add_meta_option();'>&nbsp;
<input type='submit' value='Сохранить'/>&nbsp;
<input type='reset' value='Отменить'/>
</form>
{/if}
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
</body>
</html>
