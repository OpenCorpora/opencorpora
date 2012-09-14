{* Smarty *}
{extends file='common.tpl'}
{block name=content}
{if $action == 'error'}
    {t}Пользователь с указанной комбинацией логина и пароля не найден.{/t} {t}Попробуйте, пожалуйста,{/t} <a href='?'>{t}ещё раз{/t}</a>.
{elseif $action == 'register'}
    {literal}
    <script type="text/javascript">
        $(document).ready(function(){
            $('#reg_button').click(function() {
                submit_with_readonly_check($(this).closest('form'));
                $(this).attr('disabled', 'disabled');
            });
        });
    </script>
    {/literal}
    <form action="?act=reg_done" method='post' id='login_form'>
    <table cellspacing='5'>
    <tr><td>{t}Имя пользователя{/t}<td><input type='text' name='login' size='40' maxlength='50'/></tr>
    <tr><td>{t}Пароль{/t}<td><input type='password' name='passwd' size='40' maxlength='50'/></tr>
    <tr><td>{t}Пароль ещё раз{/t}<td><input type='password' name='passwd_re' size='40' maxlength='50'/></tr>
    <tr><td valign='top'>Email<td><input type='text' name='email' size='40' maxlength='50' onkeyup="$('#chb_subscribe').removeAttr('disabled')"/><br/><span class='small'>({t}необязательно, но без него вы не сможете восстановить пароль{/t})</span></tr>
    <tr><td colspan='2'><label><input type='checkbox' name='agree' onclick="$('#reg_button').attr('disabled', !$(this).attr('checked'))"/> Я согласен на неотзывную публикацию всех вносимых мной изменений в соответствии с лицензией <a href="http://creativecommons.org/licenses/by-sa/3.0/deed.ru">Creative Commons Attribution/Share-Alike 3.0</a></label>
    <tr><td colspan='2'><label><input type='checkbox' id='chb_subscribe' name='subscribe' disabled='disabled'/> Подписаться на рассылку новостей проекта</label>
    <tr><td colspan='2'><input type='button' id='reg_button' disabled='disabled' value='{t}Зарегистрироваться{/t}'/></tr>
    </table>
    </form>
{elseif $action == 'lost_pwd'}
    <form action="?act=generate_passwd" method='post'><p>
    {t}Введите адрес электронной почты, указанный вами при регистрации{/t}:<br/>
    <input name='email' size='40' maxlength='50'/><br/>
    <input type='submit' value='{t}Прислать новый пароль{/t}'/>
    </p></form>
{elseif $action == 'generate_passwd'}
    {if $gen_status == 1}
        {t}Новый пароль отправлен на указанный электронный адрес.{/t}
    {elseif $gen_status == 2}
        {t}Пользователь с таким электронным адресом не зарегистрирован.{/t}
    {elseif $gen_status == 3}
        {t}Ошибка при отправке сообщения.{/t}
    {else}
        {t}Ошибка{/t} :(
    {/if}
{elseif $action == 'reg_done'}
    {if $reg_status == 1}
        {* registration ok *}
        {t}Спасибо, регистрация успешно завершена.{/t} {t}Теперь вы можете{/t} <a href='?'>{t}войти{/t}</a> {t}под своим именем пользователя{/t}.
    {elseif $reg_status == 2}
        {* passwords don't coincide *}
        {t}Введённые пароли не совпадают.{/t} {t}Попробуйте, пожалуйста,{/t} <a href='?act=register'>{t}ещё раз{/t}</a>.
    {elseif $reg_status == 3}
        {* username is not unique *}
        {t}Такое имя пользователя уже существует.{/t} {t}Попробуйте, пожалуйста,{/t} <a href='?act=register'>{t}ещё раз{/t}</a>.
    {elseif $reg_status == 4}
        {* email is not unique *}
        {t}Такой адрес электронной почты уже существует.{/t} {t}Попробуйте, пожалуйста,{/t} <a href='?act=register'>{t}ещё раз{/t}</a>.
    {elseif $reg_status == 5}
        {* a blank field *}
        {t}Пустое имя пользователя или пароль.{/t} {t}Попробуйте, пожалуйста,{/t} <a href='?act=register'>{t}ещё раз{/t}</a>.
    {elseif $reg_status == 6}
        {* bad login *}
        {t}Недопустимые символы в имени пользователя{/t} ({t}допустимыми являются все латинские символы, цифры и знаки &laquo;<b>-</b>&raquo; и &laquo;<b>_</b>&raquo;{/t}). {t}Попробуйте, пожалуйста,{/t} <a href='?act=register'>{t}ещё раз{/t}</a>.
    {elseif $reg_status == 7}
        {* bad passwd *}
        {t}Недопустимые символы в пароле{/t} ({t}допустимыми являются все латинские символы, цифры и знаки &laquo;<b>-</b>&raquo; и &laquo;<b>_</b>&raquo;{/t}). {t}Попробуйте, пожалуйста,{/t} <a href='?act=register'>{t}ещё раз{/t}</a>.
    {elseif $reg_status == 8}
        {* bad email *}
        {t}Неверный адрес электронной почты (если вы вводите верный адрес &mdash; напишите нам об этой ошибке).{/t} {t}Попробуйте, пожалуйста,{/t} <a href='?act=register'>{t}ещё раз{/t}</a>.
    {else}
        {* another error *}
        {t}Ошибка{/t} :(
    {/if}
{elseif $action == 'change_pw'}
    {if $change_status == 1}
        {t}Пароль успешно изменён.{/t}
    {elseif $change_status == 2}
        {t}Старый пароль введён неверно.{/t} {t}Попробуйте, пожалуйста,{/t} <a href='{$web_prefix}/options.php'>{t}ещё раз{/t}</a>.
    {elseif $change_status == 3}
        {t}Введённые пароли не совпадают.{/t} {t}Попробуйте, пожалуйста,{/t} <a href='{$web_prefix}/options.php'>{t}ещё раз{/t}</a>.
    {elseif $change_status == 4}
        {t}Недопустимые символы в пароле{/t} ({t}допустимыми являются все латинские символы, цифры и знаки &laquo;<b>-</b>&raquo; и &laquo;<b>_</b>&raquo;{/t}). {t}Попробуйте, пожалуйста,{/t} <a href='{$web_prefix}/options.php'>{t}ещё раз{/t}</a>.
    {else}
        {t}Ошибка{/t} :(
    {/if}
{elseif $action == 'change_email'}
    {if $change_status == 1}
        {t}Адрес электронной почты успешно изменён.{/t}
    {elseif $change_status == 2}
        {t}Пароль введён неверно.{/t} {t}Попробуйте, пожалуйста,{/t} <a href='{$web_prefix}/options.php'>{t}ещё раз{/t}</a>.
    {elseif $change_status == 3}
        {t}Неверный адрес электронной почты (если вы вводите верный адрес &mdash; напишите нам об этой ошибке).{/t} {t}Попробуйте, пожалуйста,{/t} <a href='{$web_prefix}/options.php'>{t}ещё раз{/t}</a>.
    {elseif $change_status == 4}
        {t}Такой адрес электронной почты уже существует.{/t} {t}Попробуйте, пожалуйста,{/t} <a href='{$web_prefix}/options.php'>{t}ещё раз{/t}</a>.
    {else}
        {t}Ошибка{/t} :(
    {/if}
{elseif $action == 'change_name'}
    {if $change_status == 1}
        {t}Имя успешно изменено.{/t}
    {elseif $change_status == 2}
        {t}Недопустимые символы в имени или слишком короткое имя.{/t} {t}Попробуйте, пожалуйста,{/t} <a href='{$web_prefix}/options.php'>{t}ещё раз{/t}</a>.
    {else}
        {t}Ошибка{/t} :(
    {/if}
{else}
    <ol>
    <script src="http://s1.loginza.ru/js/widget.js" type="text/javascript"></script>
    <li><a href="https://loginza.ru/api/widget?token_url=http%3A%2F%2F{$smarty.server.HTTP_HOST}{$web_prefix|urlencode}%2Flogin.php?act=login_openid&amp;lang={$lang}" class="loginza">{t}Войти с помощью Facebook / Twitter / Яндекс / Google...{/t}</a></li>
    <li>{t}или{/t} {t}ввести регистрационные данные{/t}:</li>
    <form action="?act=login" method="post" id='login_form'>    
    <table cellspacing='2'>
    <tr><td>{t}Имя пользователя{/t}</td><td><input type='text' name='login' size='20' maxlength='50'/></td></tr>
    <tr><td>{t}Пароль{/t}</td><td><input type='password' name='passwd' size='20' maxlength='50'/> <a href='?act=lost_pwd'>{t}я забыл пароль{/t}</a></td></tr>
    <tr><td></td><td><input type='submit' value='{t}Войти{/t}'/></td></tr>
    </table>
    </form>
    <li>{t}или{/t} <a href='?act=register'>{t}зарегистрироваться{/t}</a></li>
    </ol>
{/if}
{/block}
