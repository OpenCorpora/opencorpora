{* Smarty *}
<div id="header" class="navbar navbar-static-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a href="/" class="brand">OpenCorpora</a>
            <div class="nav-collapse">
                <ul class="nav">
                    {if !isset($active_page)}{$active_page=''}{/if}
                    <li class="dropdown {if $active_page=="tasks"}active{/if}">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Разметка <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="/tasks.php">Морфология</a></li>
                            <li><a href="/ner.php">Сущности</a></li>
                        </ul>
                    </li>
                    <li {if $active_page=="dict"}class="active"{/if}><a href="/dict.php">Словарь</a></li>
                    <li {if $active_page=="stats"}class="active"{/if}><a href="/?page=stats&weekly">Статистика</a></li>
                    <li {if $active_page=="downloads"}class="active"{/if}><a href="/?page=downloads">Скачать</a></li>
                    <li {if $active_page=="about"}class="active"{/if}><a href="/?page=about">О проекте</a></li>
                    {if $game_is_on || !isset($smarty.session.user_id)}
                    <li {if $active_page=="achievements"}class="active"{/if}><a href="/?page=achievements">Бейджи</a></li>
                    {/if}
                </ul>
                <ul class="nav pull-right">
                    <li class="dropdown">
                        {if isset($smarty.session.user_id)}
                            <a href="/options.php" class="dropdown-toggle login-corner-user{if in_array(1, $smarty.session.user_groups)} login-corner-admin{/if}" data-toggle="dropdown" data-target="#">
                            {*<span class="badge badge-star" title="Ваш текущий уровень">{$smarty.session.user_level}</span>*}
                            {if mb_strlen($smarty.session.user_name) > 20}{$smarty.session.user_name|mb_substr:0:20}…{else}{$smarty.session.user_name}{/if}
                            <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="/user.php?id={$smarty.session.user_id}">Мои успехи</a></li>
                                <li><a href="/options.php">Настройки</a></li>
                                {if in_array(1, $smarty.session.user_groups)}
                                    {if isset($smarty.session.debug_mode)}
                                        <li><a href='?debug=off'>Debug off</a></li>
                                    {else}
                                        <li><a href='?debug=on'>Debug on</a></li>
                                    {/if}
                                    {if isset($smarty.session.noadmin)}
                                        <li><a href='?pretend=off'>Перестать притворяться</a></li>
                                    {else}
                                        <li><a href='?pretend=on'>Притвориться юзером</a></li>
                                    {/if}
                                {/if}
                                <li><a href="/login.php?act=logout">Выход</a></li>
                            </ul>
                        {else}
                            <a href="/login.php" class="dropdown-toggle" data-toggle="dropdown" data-target="#">Войти <b class="caret"></b></a>
                            <div class="dropdown-menu">
                                <div class="login-corner-openid"><a href="https://loginza.ru/api/widget?token_url=http%3A%2F%2F{$smarty.server.HTTP_HOST}%2Flogin.php?act=login_openid&amp;lang=ru" class="loginza">Войти через <img src="http://loginza.ru/img/providers/yandex.png" alt="Yandex" title="Yandex"> <img src="http://loginza.ru/img/providers/google.png" alt="Google" title="Google Accounts"> <img src="http://loginza.ru/img/providers/vkontakte.png" alt="Вконтакте" title="Вконтакте"> <img src="http://loginza.ru/img/providers/mailru.png" alt="Mail.ru" title="Mail.ru"> <img src="http://loginza.ru/img/providers/twitter.png" alt="Twitter" title="Twitter"> и др.</a></div>
                                <div class="divider"></div>
                                <div class="login-corner-block">
                                    <form action="/login.php?act=login" method="POST">
                                        <input type="text" name="login" placeholder="Логин">
                                        <input type="password" name="passwd" placeholder="Пароль">
                                        <small><a href="/login.php?act=lost_pwd" class="forgot-link">Забыли пароль?</a></small>
                                        <button type="submit" class="btn btn-primary">Войти</button> <a href="/login.php?act=register" class="reg-link">Зарегистрироваться</a>
                                    </form>
                                </div>
                            </div>
                        {/if}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
