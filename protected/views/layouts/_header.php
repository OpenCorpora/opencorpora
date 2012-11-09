<?php 
/* @var $this SiteController */
/* @var $model LoginForm */
?>
<div id="header" class="navbar navbar-static-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a href="<?php echo Yii::app()->baseUrl;?>/" class="brand">OpenCorpora</a>
            <div class="nav-collapse">
                <?php $this->widget('bootstrap.widgets.TbMenu',array(
                    'items' => array(
                        array(
                            'label' => 'Разметка',
                            'url' => Yii::app()->baseUrl . '/tasks.php',
                        ),
                        array(
                            'label' => 'Словарь',
                            'url' => Yii::app()->baseUrl . '/dict.php',
                        ),
                        array(
                            'label' => 'Статистика',
                            'url' => Yii::app()->baseUrl . '/?page=stats',
                        ),
                        array(
                            'label' => 'Скачать',
                            'url' => Yii::app()->baseUrl . '/?page=downloads',
                        ),
                        array(
                            'label' => 'О проекте',
                            'url' => array('site/page','view'=>'about')
                        ),
                    )
                ));?>
                <ul class="nav pull-right">
                    <li class="dropdown">
                        <?php if(!Yii::app()->user->isGuest):?>
                        <a href="{$web_prefix}/options.php" class="dropdown-toggle login-corner-user<?php if(Yii::app()->user->isAdmin):?> login-corner-admin<?php endif;?>" data-toggle="dropdown" data-target="#">
                        <?php /*{if $game_is_on == 1}<span class="badge badge-star" title="Ваш текущий уровень">{$smarty.session.user_level}</span>{/if}*/?>
                        <?php if(mb_strlen(Yii::app()->user->model->name) > 20): echo mb_substr(Yii::app()->user->model->name,0,20) . '…'; else: echo Yii::app()->user->model->name; endif;?>
                        <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo Yii::app()->baseUrl;?>/options.php">Настройки</a></li>
                            <?php /*{if $smarty.session.user_permissions.perm_admin == 1}
                                {if isset($smarty.session.debug_mode)}
                                    <li><a href='?debug=off'>Debug off</a></li>
                                {else}
                                    <li><a href='?debug=on'>Debug on</a></li>
                                {/if}
                                {if isset($smarty.session.user_permissions.pretend)}
                                    <li><a href='?pretend=off'>Перестать притворяться</a></li>
                                {else}
                                    <li><a href='?pretend=on'>Притвориться юзером</a></li>
                                {/if}
                            {/if}*/?>
                            <li><?php echo CHtml::link('Выход',array('site/logout'));?></li>
                        </ul>
                        <?php elseif($this->id != 'site' || $this->action->id != 'login'):?>
                            <a href="<?php echo Yii::app()->baseUrl;?>/login.php" class="dropdown-toggle" data-toggle="dropdown" data-target="#">Войти <b class="caret"></b></a>
                            <div class="dropdown-menu">
                                <div class="login-corner-openid"><a href="https://loginza.ru/api/widget?token_url=<?php echo urldecode(Yii::app()->createAbsoluteUrl('site/loginzaauth'));?>" class="loginza">Войти через <img src="http://loginza.ru/img/providers/yandex.png" alt="Yandex" title="Yandex"> <img src="http://loginza.ru/img/providers/google.png" alt="Google" title="Google Accounts"> <img src="http://loginza.ru/img/providers/vkontakte.png" alt="Вконтакте" title="Вконтакте"> <img src="http://loginza.ru/img/providers/mailru.png" alt="Mail.ru" title="Mail.ru"> <img src="http://loginza.ru/img/providers/twitter.png" alt="Twitter" title="Twitter"> и др.</a></div>
                                <div class="divider"></div>
                                <div class="login-corner-block">
                                    <?php $loginModel = new LoginForm('login');
                                    /** @var BootActiveForm $form */
                                    $form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
                                        'action' => array('site/login'),
                                        'htmlOptions' => array(),
                                    )); ?>

                                        <?php echo $form->textField($loginModel, 'login', array('placeholder'=>'Логин')); ?>
                                        <?php echo $form->passwordField($loginModel, 'password', array('placeholder'=>'Пароль')); ?>
                                        <label><small><?php echo CHtml::link('Забыли пароль?',array('site/forgotpassword'),array('class'=>'forgot-link'));?></small></label>
                                        
                                        <button type="submit" class="btn btn-primary">Войти</button> <?php echo CHtml::link('Зарегистрироваться',array('user/register'),array('class'=>'reg-link'));?>
                                    <?php $this->endWidget();?>
                                </div>
                            </div>
                        <?php endif;?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
