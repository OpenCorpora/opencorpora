{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>{t}Настройки{/t}</h1>
{if isset($smarty.get.saved)}
<p>{t}Настройки сохранены.{/t}</p>
{/if}
<form action="?act=save" method="post">
    {foreach key=id item=option from=$meta}
        <div class="control-group">
            {$option.name}
            <div class="controls">
            {if $option.value_type == 1}
                <label class="radio inline"><input type='radio' name='options[{$id}]' value='1' {if $smarty.session.options.$id == 1}checked='checked'{/if}/> {t}да{/t}</label>
                <label class="radio inline"><input type='radio' name='options[{$id}]' value='0' {if $smarty.session.options.$id == 0}checked='checked'{/if}/> {t}нет{/t}</label>
            {else}
                <select name='options[{$id}]'>{html_options options=$option.values selected=$smarty.session.options.$id}</select>
            {/if}
            </div>
        </div>
    {/foreach}
    <div class="controls">
        <input type='button' class="btn btn-primary" onclick="submit_with_readonly_check($(this).closest('form'))" value='{t}Сохранить{/t}'>&nbsp;&nbsp;<input type='reset' value='{t}Отменить{/t}' class="btn">
    </div>
</form>
<h2>{t}Регистрационные данные{/t}</h2>
{if !$is_openid}
    <form action='{$web_prefix}/login.php?act=change_pw' method='post'>
        <h3>{t}Изменить пароль{/t}</h3>
        <label for="old_pwd">{t}Старый пароль{/t}</label>
        <input type='password' name='old_pw'/>
        <label for="new_pw">{t}Новый пароль{/t}</label>
        <input type='password' name='new_pw'/>
        <label for="new_pw_re">{t}Новый пароль ещё раз{/t}</label>
        <input type='password' name='new_pw_re'/>
        <div class="controls">
            <input type='button' class="btn" onclick="submit_with_readonly_check($(this).closest('form'))" value="{t}Изменить пароль{/t}">
        </div>
    </form>
{/if}
<form action='{$web_prefix}/login.php?act=change_name' method='post' id='change_name' class="">
    <h3>Изменить отображаемое имя</h3>
    <input name='shown_name' value='{$current_name|htmlspecialchars}' maxlength='120' type="text" class="span3">
    <div class="controls">
        <button onclick="submit_with_readonly_check($('#change_name'))" class="btn">Изменить имя</button>
    </div>
</form>
<form action='{$web_prefix}/login.php?act=change_email' method='post' id='change_email'>
    <h3>{t}Изменить адрес электронной почты{/t}</h3>
    <label>{t}Текущий адрес{/t}: <b>{if $current_email}{$current_email}{else}({t}отсутствует{/t}){/if}</b></label>
    <label for="email">{t}Новый адрес{/t}</label>
    <input name='email' type="text">
    {if !$is_openid}
        <label for="passwd">{t}Пароль{/t}</label>
        <input type='password' name='passwd'>
    {/if}
    <div class=controls"">
        <input type='button' onclick="submit_with_readonly_check($('#change_email'))" value="{t}Изменить адрес{/t}" class="btn">
    </div>
</form>
{if $is_admin}
<h2>Readonly</h2>
    <input type='button' value='{t}Включить{/t}' onClick="if (confirm('{t}Вы уверены?{/t}')) location.href='?act=readonly_on'" {if $readonly}disabled='disabled'{/if}/>
    <input type='button' value='{t}Выключить{/t}' onClick="if (confirm('{t}Вы уверены?{/t}')) location.href='?act=readonly_off'" {if $readonly == 0}disabled='disabled'{/if}/>
{/if}
{/block}
