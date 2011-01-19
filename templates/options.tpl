{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
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
        <td>{$option.name}</td>
        <td>
        {if $option.value_type == 'string'}
            <input name='options[{$id}]' value='{$smarty.session.options[$id]|htmlspecialchars}'/>
        {else}
            <select name='options[{$id}]'>
                {html_options values=$option.values output=$option.values selected=$smarty.session.options[$id]}
            </select>
        {/if}
        </td>
    </tr>
{/foreach}
</table>
<input type='submit' value='Сохранить'/>&nbsp;&nbsp;<input type='reset' value='Отменить'/>
</form>
<h2>Регистрационные данные</h2>
<form action='{$web_prefix}/login.php?act=change_pw' method='post'>
<h3>Изменить пароль</h3>
Старый пароль <input type='password' name='old_pw'/><br/>
Новый пароль <input type='password' name='new_pw'/><br/>
Новый пароль ещё раз <input type='password' name='new_pw_re'/><br/>
<input type='button' onclick="submit_with_readonly_check(document.forms[1])" value="Изменить пароль"/>
</form>
<form action='{$web_prefix}/login.php?act=change_email' method='post'>
<h3>Изменить адрес электронной почты</h3>
Текущий адрес: <b>{$current_email|default:'(отсутствует)'}</b><br/>
Новый адрес <input name='email'/><br/>
Пароль <input type='password' name='passwd'/><br/>
<input type='button' onclick="submit_with_readonly_check(document.forms[2])" value="Изменить адрес"/>
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
    <td><input name='option_names[{$id}]' value='{$option.name|htmlspecialchars}'/></td>
    <td><input name='option_values[{$id}]' value='{$option.value_type|htmlspecialchars}'/></td>
    <td><input name='option_default[{$id}]' type='hidden'/></td>
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
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
