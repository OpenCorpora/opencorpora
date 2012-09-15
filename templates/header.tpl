{* Smarty *}
{*if $readonly == 1}
<div id='pre_header'>{t}Система находится в режиме &laquo;только для чтения&raquo;{/t}.</div>
{/if*}
{*if isset($smarty.session.user_id)}
    {t}Вы &ndash;{/t} <b>{$smarty.session.user_name}</b>
    {if $smarty.session.user_permissions.perm_admin == 1}
        , {t}администратор{/t}
        {if isset($smarty.session.debug_mode)}
            &nbsp;[<a href='?debug=off'>debug off</a>]
        {else}
            &nbsp;[<a href='?debug=on'>debug on</a>]
        {/if}
        {if isset($smarty.session.user_permissions.pretend)}
            &nbsp;[<a href='?pretend=off'>{t}перестать притворяться{/t}</a>]
        {else}
            &nbsp;[<a href='?pretend=on'>{t}притвориться юзером{/t}</a>]
        {/if}
    {/if}
    &nbsp;[<a href='{$web_prefix}/options.php'>{t}настройки{/t}</a>]
    &nbsp;[<a href='{$web_prefix}/login.php?act=logout'>{t}выйти{/t}</a>]
{else}
    <a href='{$web_prefix}/login.php'>{t}Вход/Регистрация{/t}</a>
{/if*}
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
                            <ul class="nav">
                                {$sname = $smarty.server.SCRIPT_NAME}{$ruri = $smarty.server.REQUEST_URI}
                                <li {if $sname=="$web_prefix/tasks.php"}class="active"{/if}><a href="{$web_prefix}/tasks.php">Разметка</a></li>
                                <li {if $sname=="$web_prefix/dict.php"}class="active"{/if}><a href="{$web_prefix}/dict.php">Словарь</a></li>
                                <li {if $sname=="$web_prefix/books.php"}class="active"{/if}><a href="{$web_prefix}/books.php">Тексты</a></li>
                                <li {if $ruri=="$web_prefix/?page=stats" || $ruri=="$web_prefix/?page=tag_stats"}class="active"{/if}><a href="{$web_prefix}/?page=stats">Статистика</a></li>
                                <!--li><a href="#">Свежие правки</a></li>
                                <li><a href="#">Downloads</a></li-->
                                <li {if $ruri=="$web_prefix/?page=about"}class="active"{/if}><a href="{$web_prefix}/?page=about">О проекте</a></li>
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
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Войти <b class="caret"></b></a>
                                    <div class="dropdown-menu">
                                        <div class="login-corner-openid"><a href="#">Войти через OpenID</a></div>
                                        <div class="divider"></div>
                                        <div class="login-corner-block">
                                            <form action="{$web_prefix}/login.php">
                                                <input type="text" placeholder="Логин">
                                                <input type="password" placeholder="Пароль">
                                                <small><a href="#" class="forgot-link">Забыли пароль?</a></small>
                                                <button type="submit" class="btn btn-primary">Войти</button> <a href="#" class="reg-link">Зарегистрироваться</a>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
