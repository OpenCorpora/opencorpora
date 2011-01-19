{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
{if $smarty.get.act == 'error'}
    Пользователь с указанной комбинацией логина и пароля не найден. Попробуйте, пожалуйста, <a href='?'>ещё раз</a>.
{elseif $smarty.get.act == 'register'}
    <form action="?act=reg_done" method='post' id='login_form'>
    <table cellspacing='5'>
    <tr><td>Имя пользователя<td><input type='text' name='login' size='40' maxlength='50'/></tr>
    <tr><td>Пароль<td><input type='password' name='passwd' size='40' maxlength='50'/></tr>
    <tr><td>Пароль ещё раз<td><input type='password' name='passwd_re' size='40' maxlength='50'/></tr>
    <tr><td valign='top'>Email<td><input type='text' name='email' size='40' maxlength='50'/><br/><span class='small'>(необязательно, но без него вы не сможете восстановить пароль)</span></tr>
    <tr><td colspan='2' align='right'><input type='button' onclick='submit_with_readonly_check(document.forms[0])' value='Зарегистрироваться'/></tr>
    </table>
    </form>
{elseif $smarty.get.act == 'reg_done'}
    {if $reg_status == 1}
        {* registration ok *}
        Спасибо, регистрация успешно завершена. Теперь вы можете <a href='?'>войти</a> под своим именем пользователя.
    {elseif $reg_status == 2}
        {* passwords don't coincide *}
        Введённые пароли не совпадают. Попробуйте, пожалуйста, <a href='?act=register'>ещё раз</a>.
    {elseif $reg_status == 3}
        {* username is not unique *}
        Такое имя пользователя уже существует. Попробуйте, пожалуйста, <a href='?act=register'>ещё раз</a>.
    {elseif $reg_status == 4}
        {* email is not unique *}
        Такой адрес электронной почты уже существует. Попробуйте, пожалуйста, <a href='?act=register'>ещё раз</a>.
    {elseif $reg_status == 5}
        {* a blank field *}
        Пустое имя пользователя или пароль. Попробуйте, пожалуйста, <a href='?act=register'>ещё раз</a>.
    {elseif $reg_status == 6}
        {* bad login *}
        Недопустимые символы в имени пользователя (допустимыми являются все латинские символы, цифры и знаки &laquo;<b>-</b>&raquo; и &laquo;<b>_</b>&raquo;). Попробуйте, пожалуйста, <a href='?act=register'>ещё раз</a>.
    {elseif $reg_status == 7}
        {* bad passwd *}
        Недопустимые символы в пароле (допустимыми являются все латинские символы, цифры и знаки &laquo;<b>-</b>&raquo; и &laquo;<b>_</b>&raquo;). Попробуйте, пожалуйста, <a href='?act=register'>ещё раз</a>.
    {elseif $reg_status == 8}
        {* bad email *}
        Неверный адрес электронной почты (если вы вводите верный адрес &mdash; напишите нам об этой ошибке). Попробуйте, пожалуйста, <a href='?act=register'>ещё раз</a>.
    {else}
        {* another error *}
        Ошибка :(
    {/if}
{elseif $smarty.get.act == 'change_pw'}
    {if $change_status == 1}
        Пароль успешно изменён.
    {elseif $change_status == 2}
        Старый пароль введён неверно. Попробуйте, пожалуйста, <a href='{$web_prefix}/options.php'>ещё раз</a>.
    {elseif $change_status == 3}
        Введённые пароли не совпадают. Попробуйте, пожалуйста, <a href='{$web_prefix}/options.php'>ещё раз</a>.
    {elseif $change_status == 4}
        Недопустимые символы в пароле (допустимыми являются все латинские символы, цифры и знаки &laquo;<b>-</b>&raquo; и &laquo;<b>_</b>&raquo;). Попробуйте, пожалуйста, <a href='{$web_prefix}/options.php'>ещё раз</a>.
    {else}
        Ошибка :(
    {/if}
{elseif $smarty.get.act == 'change_email'}
    {if $change_status == 1}
        Адрес электронной почты успешно изменён.
    {elseif $change_status == 2}
        Пароль введён неверно. Попробуйте, пожалуйста, <a href='{$web_prefix}/options.php'>ещё раз</a>.
    {elseif $change_status == 3}
        Неверный адрес электронной почты (если вы вводите верный адрес &mdash; напишите нам об этой ошибке). Попробуйте, пожалуйста, <a href='{$web_prefix}/options.php'>ещё раз</a>.
    {else}
        Ошибка :(
    {/if}
{else}
    <form action="?act=login" method="post" id='login_form'>    
    <table cellspacing='2'>
    <tr><td>Имя пользователя</td><td><input type='text' name='login' size='20' maxlength='50'/></td></tr>
    <tr><td>Пароль</td><td><input type='password' name='passwd' size='20' maxlength='50'/></td></tr>
    <tr><td></td><td><input type='submit' value='Войти'/></td></tr>
    <tr><td colspan='2'>или <a href='?act=register'>зарегистрироваться</a></td></tr>
    </table>
    </form>
{/if}
</div><div id='rightcol'>
{include file='right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
