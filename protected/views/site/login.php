<?php 
/* @var $this SiteController */
/* @var $model LoginForm */
?>
<h1>Авторизация</h1>
<div class="help-block">Войдите через один из этих сайтов:</div>

<script src="http://s1.loginza.ru/js/widget.js" type="text/javascript"></script>
<iframe src="http://loginza.ru/api/widget?overlay=loginza&token_url=<?php echo urldecode(Yii::app()->createAbsoluteUrl('site/loginzaauth'));?>" style="width:350px; height:170px; margin-left:-15px;" scrolling="no" frameborder="no"></iframe>

<div class="help-block">Или через учётную запись OpenCorpora:</div>
<?php /** @var BootActiveForm $form */
$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id'=>'login-form',
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
    ),
    'htmlOptions'=>array(),
)); ?>

    <?php echo $form->textFieldRow($model, 'login', array('class'=>'span3')); ?>
    <?php echo $form->passwordFieldRow($model, 'password', array('class'=>'span3')); ?>
    <label><small><?php echo CHtml::link('Забыли пароль?',array('site/forgotpassword'),array('class'=>'forgot-link'));?></small></label>
    <div class="controls">
        <button type="submit" class="btn btn-primary">Войти</button> <?php echo CHtml::link('Зарегистрироваться',array('site/register'),array('class'=>'reg-link'));?>
    </div>

<?php $this->endWidget();?>