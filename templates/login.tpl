{* Smarty *}
{extends file='common.tpl'}
{block name=content}
{if $action == 'error'}
    Пользователь с указанной комбинацией логина и пароля не найден. Попробуйте, пожалуйста, <a href='?'>ещё раз</a>.
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
    <h1>Регистрация</h1>
    <form action="?act=reg_done" method='post' id='login_form' class="">
        <div class="control-group">
            <label for="login" class="control-label">Имя пользователя</label>
            <div class="controls">
                <input type='text' name='login' class="span3" maxlength='50'>
            </div>
        </div>
        <div class="control-group">
            <label for="passwd" class="control-label">Пароль</label>
            <div class="controls">
                <input type='password' name='passwd' class="span3" maxlength='50'>
            </div>
        </div>
        <div class="control-group">
            <label for="passwd_re" class="control-label">Пароль ещё раз</label>
            <div class="controls">
                <input type='password' name='passwd_re' class="span3" maxlength='50'>
            </div>
        </div>
        <div class="control-group">
            <label for="email" class="control-label">Email</label>
            <div class="controls">
                <input type='text' name='email' class="span3" maxlength='50' onkeyup="$('#chb_subscribe').removeAttr('disabled')"> <span class='help-block'>(необязательно, но без него вы не сможете восстановить пароль)</span>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <label class="checkbox"><input type='checkbox' name='agree' onclick="$('#reg_button').attr('disabled', !$(this).is(':checked'))"> Я согласен на неотзывную публикацию всех вносимых мной изменений в соответствии с лицензией <a href="http://creativecommons.org/licenses/by-sa/3.0/deed.ru">Creative Commons Attribution/Share-Alike 3.0</a></label>
                <label class="checkbox"><input type='checkbox' id='chb_subscribe' name='subscribe' disabled='disabled'> Подписаться на рассылку новостей проекта</label>
                <br>
                <input type='button' id='reg_button' class="btn btn-primary btn-large" disabled='disabled' value='Зарегистрироваться'>
            </div>
        </div>
    </form>
{elseif $action == 'lost_pwd'}
    <h1>Восстановление пароля</h1>
    <form action="?act=generate_passwd" method='post'>
        <div class="control-group">
            <label for="email" class="control-label">Введите адрес электронной почты, указанный вами при регистрации:</label>
            <div class="controls">
                <input name='email' type="text" class="span3" maxlength='50'>
            </div>
        </div>
        <input type='submit' class="btn btn-primary btn-large" value='Прислать новый пароль'/></p>
    </form>
{elseif $action == 'generate_passwd'}
    {if $gen_status == 1}
        <div class="alert alert-success">Новый пароль отправлен на указанный электронный адрес.</div>
    {elseif $gen_status == 2}
        <div class="alert alert-error">Пользователь с таким электронным адресом не зарегистрирован.</div>
    {elseif $gen_status == 3}
        <div class="alert alert-error">Ошибка при отправке сообщения. Попробуйте ещё раз через несколько минут.</div>
    {elseif $gen_status != ""}
        <div class="alert alert-warning">Пользователь с этим электронным адресом зарегистрирован через <b>{$gen_status}</b>, без ввода логина и пароля. <a href='?'>Войти ещё раз</a>.</div>
    {else}
        <div class="alert alert-error">Ошибка :(</div>
    {/if}
{elseif $action == 'reg_done'}
    {if $reg_status == 2}
        {* passwords don't coincide *}
        <div class="alert alert-error"><h4>Введённые пароли не совпадают.</h4>Попробуйте, пожалуйста, <a href='?act=register'>ещё раз</a>.</div>
    {elseif $reg_status == 3}
        {* username is not unique *}
        <div class="alert alert-error"><h4>Такое имя пользователя уже существует.</h4>Попробуйте, пожалуйста, <a href='?act=register'>ещё раз</a>.</div>
    {elseif $reg_status == 4}
        {* email is not unique *}
        <div class="alert alert-error"><h4>Такой адрес электронной почты уже существует.</h4>Попробуйте, пожалуйста, <a href='?act=register'>ещё раз</a>.</div>
    {elseif $reg_status == 5}
        {* a blank field *}
        <div class="alert alert-error"><h4>Пустое имя пользователя или пароль.</h4>Попробуйте, пожалуйста, <a href='?act=register'>ещё раз</a>.</div>
    {elseif $reg_status == 6}
        {* bad login *}
        <div class="alert alert-error"><h4>Недопустимые символы в имени пользователя</h4>(допустимыми являются все латинские символы, цифры и знаки &laquo;<b>-</b>&raquo; и &laquo;<b>_</b>&raquo;). Попробуйте, пожалуйста, <a href='?act=register'>ещё раз</a>.</div>
    {elseif $reg_status == 7}
        {* bad passwd *}
        <div class="alert alert-error"><h4>Недопустимые символы в пароле</h4>(допустимыми являются все латинские символы, цифры и знаки &laquo;<b>-</b>&raquo; и &laquo;<b>_</b>&raquo;). Попробуйте, пожалуйста, <a href='?act=register'>ещё раз</a>.</div>
    {elseif $reg_status == 8}
        {* bad email *}
        <div class="alert alert-error"><h4>Неверный адрес электронной почты</h4>(если вы вводите верный адрес &mdash; напишите нам об этой ошибке). Попробуйте, пожалуйста, <a href='?act=register'>ещё раз</a>.</div>
    {else}
        {* another error *}
        <div class="alert alert-error">Ошибка :(</div>
    {/if}
{elseif $action == 'change_pw'}
    {if $change_status == 1}
        <div class="alert alert-success">Пароль успешно изменён.</div>
    {elseif $change_status == 2}
        <div class="alert alert-error"><h4>Старый пароль введён неверно.</h4>Попробуйте, пожалуйста, <a href='{$web_prefix}/options.php'>ещё раз</a>.</div>
    {elseif $change_status == 3}
        <div class="alert alert-error"><h4>Введённые пароли не совпадают.</h4>Попробуйте, пожалуйста, <a href='{$web_prefix}/options.php'>ещё раз</a>.</div>
    {elseif $change_status == 4}
        <div class="alert alert-error"><h4>Недопустимые символы в пароле</h4>(допустимыми являются все латинские символы, цифры и знаки &laquo;<b>-</b>&raquo; и &laquo;<b>_</b>&raquo;). Попробуйте, пожалуйста, <a href='{$web_prefix}/options.php'>ещё раз</a>.</div>
    {else}
        <div class="alert alert-error">Ошибка :(</div>
    {/if}
{elseif $action == 'change_email'}
    {if $change_status == 1}
        <div class="alert alert-success">Адрес электронной почты успешно изменён.</div>
    {elseif $change_status == 2}
        <div class="alert alert-error"><h4>Пароль введён неверно.</h4>Попробуйте, пожалуйста, <a href='{$web_prefix}/options.php'>ещё раз</a>.</div>
    {elseif $change_status == 3}
        <div class="alert alert-error"><h4>Неверный адрес электронной почты</h4>(если вы вводите верный адрес &mdash; напишите нам об этой ошибке). Попробуйте, пожалуйста, <a href='{$web_prefix}/options.php'>ещё раз</a>.</div>
    {elseif $change_status == 4}
        <div class="alert alert-error"><h4>Такой адрес электронной почты уже существует.</h4>Попробуйте, пожалуйста, <a href='{$web_prefix}/options.php'>ещё раз</a>.</div>
    {else}
        <div class="alert alert-error">Ошибка :(</div>
    {/if}
{elseif $action == 'change_name'}
    {if $change_status == 1}
        <div class="alert alert-error">Имя успешно изменено.</div>
    {elseif $change_status == 2}
        <div class="alert alert-error"><h4>Недопустимые символы в имени или слишком короткое имя.</h4>Попробуйте, пожалуйста, <a href='{$web_prefix}/options.php'>ещё раз</a>.</div>
    {else}
        <div class="alert alert-error">Ошибка :(</h4>
    {/if}
{else}
    <h1>Авторизация</h1>
    <div class="help-block">Войдите через один из этих сайтов:</div>
    <script src="http://s1.loginza.ru/js/widget.js" type="text/javascript"></script>
    <iframe src="http://loginza.ru/api/widget?overlay=loginza&token_url=http%3A%2F%2F{$smarty.server.HTTP_HOST}{$web_prefix|urlencode}%2Flogin.php?act=login_openid&amp;lang=ru" style="width:350px; height:170px; margin-left:-15px;" scrolling="no" frameborder="no"></iframe>
    <div class="help-block">Или через учётную запись OpenCorpora:</div>
    <form action="{$web_prefix}/login.php?act=login" method="POST">
        <label for="login">Логин</label>
        <input type="text" name="login">
        <label for="passwd">Пароль</label>
        <input type="password" name="passwd">
        <label><small><a href="{$web_prefix}/login.php?act=lost_pwd" class="forgot-link">Забыли пароль?</a></small></label>
        <div class="controls">
            <button type="submit" class="btn btn-primary">Войти</button> <a href="{$web_prefix}/login.php?act=register" class="reg-link">Зарегистрироваться</a>
        </div>
    </form>
{/if}
{/block}
