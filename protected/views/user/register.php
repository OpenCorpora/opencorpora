<?php
/* @var $this UserController */
?>
<h1><?php echo $this->pageTitle; ?></h1>

<?php /** @var BootActiveForm $form */
$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id'=>'register-form',
    'enableClientValidation'=>true,
    'enableAjaxValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'afterValidateAttribute' => 'js:function(form, attribute, data, hasError){ if(attribute.inputID == "User_user_email" && !hasError) {$("#User_is_subscribe_checked").removeAttr("disabled");}; }'
    ),
    'htmlOptions'=>array(),
)); ?>

    <?php echo $form->textFieldRow($model, 'user_name', array('class'=>'span3')); ?>
    <?php echo $form->passwordFieldRow($model, 'user_passwd', array('class'=>'span3')); ?>
    <?php echo $form->textFieldRow($model, 'user_email', array('class'=>'span3','hint'=>'(необязательно, но без него вы не сможете восстановить пароль)')); ?>
    <?php echo $form->checkboxRow($model, 'is_license_accepted', array()); ?>
    <?php echo $form->checkboxRow($model, 'is_subscribe_checked', array('disabled' => 'disabled')); ?>
    
    <div class="controls">
        <br>
        <button type="submit" class="btn btn-primary btn-large">Зарегистрироваться</button>
    </div>

<?php $this->endWidget();?>