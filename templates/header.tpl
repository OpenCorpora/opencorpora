{* Smarty *}
{if $readonly == 1}
<div id='pre_header'>{t}Система находится в режиме &laquo;только для чтения&raquo;{/t}.</div>
{/if}
<div id='header'>
{strip}
<div id='lblock'>
    <a href='{$web_prefix}/'>Home</a>&nbsp;&nbsp;&nbsp;
    <span class='small'>{t}&alpha;-версия{/t}</span>&nbsp;&nbsp;&nbsp;
    {if $lang == 'ru'}
    <a href="?lang=en">English version</a></div>
    {else}
    <a href="?lang=ru">Русская версия</a></div>
    {/if}
<div id='rblock'>
{if isset($smarty.session.user_id)}
    {t}Вы &ndash;{/t} <b>{$smarty.session.user_name}</b>
    {if $smarty.session.user_permissions.perm_admin == 1}
        , {t}администратор{/t}
        {if isset($smarty.session.debug_mode)}
            &nbsp;[<a href='?debug=off'>debug off</a>]
        {else}
            &nbsp;[<a href='?debug=on'>debug on</a>]
        {/if}
        {if $smarty.session.user_permissions.pretend == 1}
            &nbsp;[<a href='?pretend=off'>{t}перестать притворяться{/t}</a>]
        {else}
            &nbsp;[<a href='?pretend=on'>{t}притвориться юзером{/t}</a>]
        {/if}
    {/if}
    &nbsp;[<a href='{$web_prefix}/options.php'>{t}настройки{/t}</a>]
    &nbsp;[<a href='{$web_prefix}/login.php?act=logout'>{t}выйти{/t}</a>]
{else}
    <a href='{$web_prefix}/login.php'>{t}Вход/Регистрация{/t}</a>
{/if}
</div>
{/strip}
</div>
