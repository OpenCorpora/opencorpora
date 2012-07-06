{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>{t}Настройки{/t}</h1>
{if isset($smarty.get.saved)}
<p>{t}Настройки сохранены.{/t}</p>
{/if}
<form action="?act=save" method="post">
<table cellpadding='5'>
{foreach key=id item=option from=$meta}
    <tr>
        <td>{$option.name}</td>
        <td>
        {if $option.value_type == 1}
            <label><input type='radio' name='options[{$id}]' value='1' {if $smarty.session.options.$id == 1}checked='checked'{/if}/> {t}да{/t}</label>
            <label><input type='radio' name='options[{$id}]' value='0' {if $smarty.session.options.$id == 0}checked='checked'{/if}/> {t}нет{/t}</label>
        {else}
            <select name='options[{$id}]'>{html_options options=$option.values selected=$smarty.session.options.$id}</select>
        {/if}
        </td>
    </tr>
{/foreach}
</table>
<input type='button' onclick="submit_with_readonly_check(document.forms[0])" value='{t}Сохранить{/t}'/>&nbsp;&nbsp;<input type='reset' value='{t}Отменить{/t}'/>
</form>
<h2>{t}Регистрационные данные{/t}</h2>
{if !$is_openid}
<form action='{$web_prefix}/login.php?act=change_pw' method='post'>
<h3>{t}Изменить пароль{/t}</h3>
{t}Старый пароль{/t} <input type='password' name='old_pw'/><br/>
{t}Новый пароль{/t} <input type='password' name='new_pw'/><br/>
{t}Новый пароль ещё раз{/t} <input type='password' name='new_pw_re'/><br/>
<input type='button' onclick="submit_with_readonly_check(document.forms[1])" value="{t}Изменить пароль{/t}"/>
</form>
{/if}
<form action='{$web_prefix}/login.php?act=change_name' method='post' id='change_name'>
<h3>Изменить отображаемое имя</h3>
<input name='shown_name' value='{$current_name|htmlspecialchars}' maxlength='120' size='20'/>
<button onclick="submit_with_readonly_check($('#change_name'))">Изменить имя</button>
</form>
<form action='{$web_prefix}/login.php?act=change_email' method='post' id='change_email'>
<h3>{t}Изменить адрес электронной почты{/t}</h3>
{t}Текущий адрес{/t}: <b>{if $current_email}{$current_email}{else}({t}отсутствует{/t}){/if}</b><br/>
{t}Новый адрес{/t} <input name='email'/><br/>
{if !$is_openid}{t}Пароль{/t} <input type='password' name='passwd'/><br/>{/if}
<input type='button' onclick="submit_with_readonly_check($('#change_email'))" value="{t}Изменить адрес{/t}"/>
</form>
{if $is_admin}
<h2>Readonly</h2>
<input type='button' value='{t}Включить{/t}' onClick="if (confirm('{t}Вы уверены?{/t}')) location.href='?act=readonly_on'" {if $readonly}disabled='disabled'{/if}/>
<input type='button' value='{t}Выключить{/t}' onClick="if (confirm('{t}Вы уверены?{/t}')) location.href='?act=readonly_off'" {if $readonly == 0}disabled='disabled'{/if}/>
{/if}
{/block}
