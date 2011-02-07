{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='english/header.tpl'}
<div id='content'>
<h1>Settings</h1>
{if $smarty.get.saved == 1}
<p>Your settings have been saved.</p>
{/if}
<form action="?act=save" method="post">
<table cellpadding='5'>
{foreach key=id item=option from=$meta}
    <tr>
        <td>{$option.name}</td>
        <td>
        {if $option.value_type == 1}
            <label><input type='radio' name='options[{$id}]' value='1' {if $smarty.session.options.$id == 1}checked='checked'{/if}/> yes</label>
            <label><input type='radio' name='options[{$id}]' value='0' {if $smarty.session.options.$id == 0}checked='checked'{/if}/> no</label>
        {else}
            <select name='options[{$id}]'>{html_options options=$option.values selected=$smarty.session.options.$id}</select>
        {/if}
        </td>
    </tr>
{/foreach}
</table>
<input type='button' onclick="submit_with_readonly_check(document.forms[0])" value='Save'/>&nbsp;&nbsp;<input type='reset' value='Cancel'/>
</form>
<h2>Registration data</h2>
<form action='{$web_prefix}/login.php?act=change_pw' method='post'>
<h3>Change password</h3>
Old password <input type='password' name='old_pw'/><br/>
New password <input type='password' name='new_pw'/><br/>
Repeat new password <input type='password' name='new_pw_re'/><br/>
<input type='button' onclick="submit_with_readonly_check(document.forms[1])" value="Change password"/>
</form>
<form action='{$web_prefix}/login.php?act=change_email' method='post'>
<h3>Change email address</h3>
Current email: <b>{$current_email|default:'(none)'}</b><br/>
New email <input name='email'/><br/>
Password <input type='password' name='passwd'/><br/>
<input type='button' onclick="submit_with_readonly_check(document.forms[2])" value="Change email address"/>
</form>
{if $is_admin}
<h2>Readonly</h2>
<input type='button' value='Turn on' onClick="if (confirm('Are you sure?')) location.href='?act=readonly_on'" {if $readonly}disabled='disabled'{/if}/>
<input type='button' value='Turn off' onClick="if (confirm('Are you sure?')) location.href='?act=readonly_off'" {if $readonly == 0}disabled='disabled'{/if}/>
{/if}
</div>
<div id='rightcol'>
{include file='english/right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
