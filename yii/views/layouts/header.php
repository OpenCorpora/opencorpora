<div id="header" class="navbar navbar-static-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a href="<?php echo Yii::app()->request->baseUrl; ?>/" class="brand">OpenCorpora</a>
            <div class="nav-collapse">
                <ul class="nav">
                    <li <?php /* if ($active_page == "tasks"): ?>class="active"<?php endif; ?>><a href="<?php echo Yii::app()->request->baseUrl; ?>/tasks.php">Разметка</a></li>
                    <li <?php if ($active_page == "dict"): ?>class="active"<?php endif; ?>><a href="<?php echo Yii::app()->request->baseUrl; ?>/dict.php">Словарь</a></li>
                    <!--li {if $sname=="$web_prefix/books.php"}class="active"{/if}><a href="{$web_prefix}/books.php">Тексты</a></li-->
                    <li <?php if ($active_page == "stats"): ?>class="active"<?php endif; ?>><a href="<?php echo Yii::app()->request->baseUrl; ?>/?page=stats">Статистика</a></li>
                    <!--li><a href="#">Свежие правки</a></li>
                    <li><a href="#">Downloads</a></li-->
                    <li <?php if ($active_page == "downloads"): ?>class="active"<?php endif; ?>><a href="<?php echo Yii::app()->request->baseUrl; ?>/?page=downloads">Скачать</a></li>
                    <li <?php if ($active_page == "about"): ?>class="active"<?php endif; */ ?>><a href="<?php echo Yii::app()->request->baseUrl; ?>/?page=about">О проекте</a></li>
                </ul>
                <ul class="nav pull-right">
                    <li>
                        <!--{if $lang == 'ru'}
                            <a href="?lang=en" class="lang-switcher lang-switcher-en" title="English version">En</a>
                        {else}
                            <a href="?lang=ru" class="lang-switcher lang-switcher-ru" title="Русская версия">Ру</a>
                        {/if}-->
                    </li>
                    <li class="dropdown">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo Yii::app()->request->baseUrl; ?>/options.php" class="dropdown-toggle login-corner-user" data-toggle="dropdown" data-target="#">
                        <?php if (isset($game_is_on) && $game_is_on): ?><span class="badge badge-star" title="Ваш текущий уровень"><?php echo $_SESSION['user_level']; ?></span><?php endif; ?>
                        <?php if (mb_strlen($_SESSION['user_name']) > 20) echo mb_substr($_SESSION['user_name'], 0, 20) . '…'; else echo $_SESSION['user_name']; ?>
                        <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo Yii::app()->request->baseUrl; ?>/options.php">Настройки</a></li>
                            <?php if ($_SESSION['user_permissions']['perm_admin'] == 1): ?>
                                <?php if(isset($_SESSION['debug_mode'])):?>
                                    <li><a href='?debug=off'>Debug off</a></li>
                                <?php else: ?>
                                    <li><a href='?debug=on'>Debug on</a></li>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['user_permissions']['pretend'])): ?>
                                    <li><a href='?pretend=off'>Перестать притворяться</a></li>
                                <?php else: ?>
                                    <li><a href='?pretend=on'>Притвориться юзером</a></li>
                                <?php endif; ?>
                            <?php endif; ?>
                            <li><a href="<?php echo Yii::app()->request->baseUrl; ?>/login.php?act=logout">Выход</a></li>
                        </ul>
                        <?php else: ?>
                            <a href="<?php echo Yii::app()->request->baseUrl; ?>/login.php" class="dropdown-toggle" data-toggle="dropdown" data-target="#">Войти <b class="caret"></b></a>
                            <div class="dropdown-menu">
                                <div class="login-corner-openid"><a href="https://loginza.ru/api/widget?token_url=http%3A%2F%2F<?php echo $_SERVER['HTTP_HOST'] . urlencode(Yii::app()->request->baseUrl); ?>%2Flogin.php?act=login_openid&amp;lang=ru" class="loginza">Войти через <img src="http://loginza.ru/img/providers/yandex.png" alt="Yandex" title="Yandex"> <img src="http://loginza.ru/img/providers/google.png" alt="Google" title="Google Accounts"> <img src="http://loginza.ru/img/providers/vkontakte.png" alt="Вконтакте" title="Вконтакте"> <img src="http://loginza.ru/img/providers/mailru.png" alt="Mail.ru" title="Mail.ru"> <img src="http://loginza.ru/img/providers/twitter.png" alt="Twitter" title="Twitter"> и др.</a></div>
                                <div class="divider"></div>
                                <div class="login-corner-block">
                                    <form action="<?php echo Yii::app()->request->baseUrl; ?>/login.php?act=login" method="POST">
                                        <input type="text" name="login" placeholder="Логин">
                                        <input type="password" name="passwd" placeholder="Пароль">
                                        <small><a href="<?php echo Yii::app()->request->baseUrl; ?>/login.php?act=lost_pwd" class="forgot-link">Забыли пароль?</a></small>
                                        <button type="submit" class="btn btn-primary">Войти</button> <a href="<?php echo Yii::app()->request->baseUrl; ?>/login.php?act=register" class="reg-link">Зарегистрироваться</a>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
