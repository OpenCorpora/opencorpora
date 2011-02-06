{* Smarty *}
{if $readonly == 1}
<div id='pre_header'>Система находится в режиме &laquo;только для чтения&raquo;.</div>
{/if}
<div id='header'>
<div id='lblock'><a href='{$web_prefix}/'>Home</a>&nbsp;&nbsp;&nbsp;<span class='small'>&alpha;-версия</span></div>
{strip}
<div id='rblock'>
{if $smarty.session.user_id}
    Вы &ndash; <b>{$smarty.session.user_name}</b>
    {if $smarty.session.user_group > 5}
        , администратор
        {if $smarty.session.debug_mode}
            &nbsp;[<a href='?debug=off'>debug off</a>]
        {else}
            &nbsp;[<a href='?debug=on'>debug on</a>]
        {/if}
        {if $smarty.session.user_group == 6}
            &nbsp;[<a href='?pretend=off'>перестать притворяться</a>]
        {else}
            &nbsp;[<a href='?pretend=on'>притвориться юзером</a>]
        {/if}
    {/if}
    &nbsp;<a title='Настройки' href='{$web_prefix}/options.php'><img src='{$web_prefix}/media/settings22.png' style='border:none; vertical-align: middle' alt='Настройки'/></a>
    &nbsp;<a title='Выйти' href='{$web_prefix}/login.php?act=logout'><img src='{$web_prefix}/media/exit22.png' style='border:none; vertical-align: middle' alt='Выйти'/></a>
{else}
    <a href='{$web_prefix}/login.php'>Вход/Регистрация</a>
{/if}
</div>
{/strip}
</div>
