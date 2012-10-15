<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv='content' content='text/html;charset=utf-8'/>
        <!-- Bootstrap -->
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

        <link rel='stylesheet' type='text/css' href='<?php echo Yii::app()->baseurl;?>/css/main.css?4'/>
        <link rel='stylesheet' type='text/css' href='http://yandex.st/jquery-ui/1.8.16/themes/smoothness/jquery.ui.all.min.css'/>
        <script type="text/javascript" src="http://yandex.st/jquery-ui/1.8.16/jquery-ui.min.js"></script>
        <script src='<?php echo Yii::app()->baseurl;?>/js/main.js?3' type='text/javascript'></script>
        <script src='<?php echo Yii::app()->baseurl;?>/js/jquery.mousewheel.js' type='text/javascript'></script>
        <script src='<?php echo Yii::app()->baseurl;?>/js/jquery.autocomplete.js'></script>
        <title>OpenCorpora: открытый корпус русского языка</title>
    </head>
<body>
    <div id='wrap'>
        <?php $this->renderPartial('//layouts/_header');?>
        <?php // before_content goes here?>
        <?php /*{if $readonly == 1}
        <div class='alert alert-error'><div class="container">Система находится в режиме &laquo;только для чтения&raquo;.</div></div>
        {/if}*/?>
        <div id="container" class="container">
            <?php // game status & alerts go here?>
            <?php echo $content; ?>
        </div>
        <?php $this->renderPartial('//layouts/_footer');?>
    </div>
</body>
</html>