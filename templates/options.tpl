{* Smarty *}
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='{$web_prefix}/css/main.css'/>
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
{foreach key=id item=option from=$smarty.session.options}
    <tr>
        <td>{$option.name}
        <td>
        {if $option.value_type == 'int'}
            <input name='options[{$id}]' value='{$option.value}'/>
        {else}
            <select name='options[{$id}]'>
                {html_options values=$option.value_type output=$option.value_type selected=$option.value}
            </select>   
        {/if}
    </tr>
{/foreach}
</table>
<input type='submit' value='Сохранить'/>&nbsp;&nbsp;<input type='reset' value='Отменить'/>
</form>
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
</body>
</html>
