{* Smarty *}
{if $readonly == 1}
<div id='pre_header'>The system is in read-only mode.</div>
{/if}
<div id='header'>
<div id='lblock'><a href='{$web_prefix}/'>Home</a>&nbsp;&nbsp;&nbsp;<span class='small'>&alpha;-version</span></div>
{strip}
<div id='rblock'>
{if $smarty.session.user_id}
    You are <b>{$smarty.session.user_name}</b>
    {if $smarty.session.user_group > 5}
        , admin
        {if $smarty.session.debug_mode}
            &nbsp;[<a href='?debug=off'>debug off</a>]
        {else}
            &nbsp;[<a href='?debug=on'>debug on</a>]
        {/if}
        {if $smarty.session.user_group == 6}
            &nbsp;[<a href='?pretend=off'>stop pretending</a>]
        {else}
            &nbsp;[<a href='?pretend=on'>pretend a user</a>]
        {/if}
    {/if}
    &nbsp;<a title='Options' href='{$web_prefix}/options.php'><img src='{$web_prefix}/media/settings22.png' style='border:none; vertical-align: middle' alt='Options'/></a>
    &nbsp;<a title='Log off' href='{$web_prefix}/login.php?act=logout'><img src='{$web_prefix}/media/exit22.png' style='border:none; vertical-align: middle' alt='Log off'/></a>
{else}
    <a href='{$web_prefix}/login.php'>Login/register</a>
{/if}
</div>
{/strip}
</div>
