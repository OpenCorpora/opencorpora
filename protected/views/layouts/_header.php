<div id="header" class="navbar navbar-static-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a href="{$web_prefix}/" class="brand">OpenCorpora</a>
            <div class="nav-collapse">
                <?php /*<ul class="nav">
                    {if !isset($active_page)}{$active_page=''}{/if}
                    <li {if $active_page=="tasks"}class="active"{/if}><a href="{$web_prefix}/tasks.php">Разметка</a></li>
                    <li {if $active_page=="dict"}class="active"{/if}><a href="{$web_prefix}/dict.php">Словарь</a></li>
                    <!--li {if $sname=="$web_prefix/books.php"}class="active"{/if}><a href="{$web_prefix}/books.php">Тексты</a></li-->
                    <li {if $active_page=="stats"}class="active"{/if}><a href="{$web_prefix}/?page=stats">Статистика</a></li>
                    <!--li><a href="#">Свежие правки</a></li>
                    <li><a href="#">Downloads</a></li-->
                    <li {if $active_page=="downloads"}class="active"{/if}><a href="{$web_prefix}/?page=downloads">Скачать</a></li>
                    <li {if $active_page=="about"}class="active"{/if}><a href="{$web_prefix}/?page=about">О проекте</a></li>
                </ul>
                <ul class="nav pull-right">
                    <li>
                        {if $lang == 'ru'}
                            <a href="?lang=en" class="lang-switcher lang-switcher-en" title="English version">En</a>
                        {else}
                            <a href="?lang=ru" class="lang-switcher lang-switcher-ru" title="Русская версия">Ру</a>
                        {/if}
                    </li>
                    <li class="dropdown">
                        {if isset($smarty.session.user_id)}
                        <a href="{$web_prefix}/options.php" class="dropdown-toggle login-corner-user" data-toggle="dropdown" data-target="#">
                        {if $game_is_on == 1}<span class="badge badge-star" title="Ваш текущий уровень">{$smarty.session.user_level}</span>{/if}
                        {if mb_strlen($smarty.session.user_name) > 20}{$smarty.session.user_name|mb_substr:0:20}…{else}{$smarty.session.user_name}{/if}
                        <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="{$web_prefix}/options.php">Настройки</a></li>
                            {if $smarty.session.user_permissions.perm_admin == 1}
                                {if isset($smarty.session.debug_mode)}
                                    <li><a href='?debug=off'>Debug off</a></li>
                                {else}
                                    <li><a href='?debug=on'>Debug on</a></li>
                                {/if}
                                {if isset($smarty.session.user_permissions.pretend)}
                                    <li><a href='?pretend=off'>{t}Перестать притворяться{/t}</a></li>
                                {else}
                                    <li><a href='?pretend=on'>{t}Притвориться юзером{/t}</a></li>
                                {/if}
                            {/if}
                            <li><a href="{$web_prefix}/login.php?act=logout">Выход</a></li>
                        </ul>
                        {else}
                            <a href="{$web_prefix}/login.php" class="dropdown-toggle" data-toggle="dropdown" data-target="#">Войти <b class="caret"></b></a>
                            <div class="dropdown-menu">
                                <div class="login-corner-openid"><a href="https://loginza.ru/api/widget?token_url=http%3A%2F%2F{$smarty.server.HTTP_HOST}{$web_prefix|urlencode}%2Flogin.php?act=login_openid&amp;lang={$lang}" class="loginza">Войти через <img src="http://loginza.ru/img/providers/yandex.png" alt="Yandex" title="Yandex"> <img src="http://loginza.ru/img/providers/google.png" alt="Google" title="Google Accounts"> <img src="http://loginza.ru/img/providers/vkontakte.png" alt="Вконтакте" title="Вконтакте"> <img src="http://loginza.ru/img/providers/mailru.png" alt="Mail.ru" title="Mail.ru"> <img src="http://loginza.ru/img/providers/twitter.png" alt="Twitter" title="Twitter"> и др.</a></div>
                                <div class="divider"></div>
                                <div class="login-corner-block">
                                    <form action="{$web_prefix}/login.php?act=login" method="POST">
                                        <input type="text" name="login" placeholder="Логин">
                                        <input type="password" name="passwd" placeholder="Пароль">
                                        <small><a href="{$web_prefix}/login.php?act=lost_pwd" class="forgot-link">Забыли пароль?</a></small>
                                        <button type="submit" class="btn btn-primary">Войти</button> <a href="{$web_prefix}/login.php?act=register" class="reg-link">Зарегистрироваться</a>
                                    </form>
                                </div>
                            </div>
                        {/if}
                    </li>
                </ul>*/?>
            </div>
        </div>
    </div>
</div>
