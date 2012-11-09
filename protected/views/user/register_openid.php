<?php
/* @var $this UserController */
?>
<h1>Регистрация</h1>

<p>Вы успешно вошли через аккаунт <strong><?php echo $model->user_name?></strong>.<br>
    Для завершения регистрации осталось согласиться с нашей лицензией.</p>
<?php /** @var BootActiveForm $form */
$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id'=>'register-form',
    'action'=>array('user/registeropenid'),
    'enableClientValidation'=>true,
    'enableAjaxValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
    ),
    'htmlOptions'=>array(),
)); ?>
    <?php echo $form->hiddenField($model, 'user_name'); ?>
    <?php echo $form->checkboxRow($model, 'is_license_accepted', array()); ?>
    
    <div class="controls">
        <br>
        <button type="submit" class="btn btn-primary btn-large">Зарегистрироваться</button>
    </div>

<?php $this->endWidget();?>