{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='english/header.tpl'}
<div id='content'>
{if $smarty.get.act == 'error'}
    Not found any users with these login and password. Please, <a href='?'>try again</a>.
{elseif $smarty.get.act == 'register'}
    <form action="?act=reg_done" method='post' id='login_form'>
    <table cellspacing='5'>
    <tr><td>Username<td><input type='text' name='login' size='40' maxlength='50'/></tr>
    <tr><td>Password<td><input type='password' name='passwd' size='40' maxlength='50'/></tr>
    <tr><td>Password again<td><input type='password' name='passwd_re' size='40' maxlength='50'/></tr>
    <tr><td valign='top'>Email<td><input type='text' name='email' size='40' maxlength='50'/><br/><span class='small'>(optional, but without it you won't be able to restore your password)</span></tr>
    <tr><td colspan='2' align='right'><input type='button' onclick='submit_with_readonly_check(document.forms[0])' value='Register'/></tr>
    </table>
    </form>
{elseif $smarty.get.act == 'reg_done'}
    {if $reg_status == 1}
        {* registration ok *}
        Thank you, the registration process has been completed. Now you can <a href='?'>login</a> using your username.
    {elseif $reg_status == 2}
        {* passwords don't coincide *}
        The passwords you entered are different. Please, <a href='?act=register'>try again</a>.
    {elseif $reg_status == 3}
        {* username is not unique *}
        A user with such name already exists. Please, <a href='?act=register'>try again</a>.
    {elseif $reg_status == 4}
        {* email is not unique *}
        This email address is already used by someone else, please <a href='?act=register'>try again</a>.
    {elseif $reg_status == 5}
        {* a blank field *}
        The username or the password you provided is blank. Please, <a href='?act=register'>try again</a>.
    {elseif $reg_status == 6}
        {* bad login *}
        Wrong characters in your username (good characters are all latin letters, numbers, "<b>-</b>"; and "<b>_</b>" symbols). Please, <a href='?act=register'>try again</a>.
    {elseif $reg_status == 7}
        {* bad passwd *}
        Wrong characters in your password (good characters are all latin letters, numbers, "<b>-</b>"; and "<b>_</b>" symbols). Please, <a href='?act=register'>try again</a>.
    {elseif $reg_status == 8}
        {* bad email *}
        Wrong email address (if you are sure that the address you enter is valid, please report this error). Please, <a href='?act=register'>try again</a>.
    {else}
        {* another error *}
        Error :(
    {/if}
{elseif $smarty.get.act == 'change_pw'}
    {if $change_status == 1}
        Password changed successfully.
    {elseif $change_status == 2}
        Old password is wrong. Please, <a href='{$web_prefix}/options.php'>try again</a>.
    {elseif $change_status == 3}
        The passwords you entered are different. Please, <a href='{$web_prefix}/options.php'>try again</a>.
    {elseif $change_status == 4}
        Wrong characters in your password (good characters are all latin letters, numbers, "<b>-</b>"; and "<b>_</b>" symbols). Please, <a href='{$web_prefix}/options.php'>try again</a>.
    {else}
        Error :(
    {/if}
{elseif $smarty.get.act == 'change_email'}
    {if $change_status == 1}
        Email address changed successfully.
    {elseif $change_status == 2}
        Wrong password. Please, <a href='{$web_prefix}/options.php'>try again</a>.
    {elseif $change_status == 3}
        Wrong email address (if you are sure that the address you enter is valid, please report this error). Please, <a href='{$web_prefix}/options.php'>try again</a>.
    {else}
        Error :(
    {/if}
{else}
    <form action="?act=login" method="post" id='login_form'>    
    <table cellspacing='2'>
    <tr><td>Username</td><td><input type='text' name='login' size='20' maxlength='50'/></td></tr>
    <tr><td>Password</td><td><input type='password' name='passwd' size='20' maxlength='50'/></td></tr>
    <tr><td></td><td><input type='submit' value='Login'/></td></tr>
    <tr><td colspan='2'>or <a href='?act=register'>register</a></td></tr>
    </table>
    </form>
{/if}
</div><div id='rightcol'>
{include file='english/right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
