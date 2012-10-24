<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv='content' content='text/html;charset=utf-8'/>
        <title>OpenCorpora: открытый корпус русского языка</title>
        <meta property="og:image" content="http://opencorpora.org/img/fb-pic.png"/>
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://opencorpora.org" />
        <meta property="og:title" content="OpenCorpora: открытый корпус русского языка" />

        <link rel="shortcut icon" href="{$web_prefix}/favicon.ico" />
        <!-- Custom Bootstrap css -->
        <?php Yii::app()->clientScript->registerCssFile(Yii::app()->baseurl . '/bootstrap/css/bootstrap.min.css');?>
        
        <?php Yii::app()->clientScript->registerCssFile(Yii::app()->baseurl . '/css/main.css?4');?>
        <?php Yii::app()->clientScript->registerCssFile('http://yandex.st/jquery-ui/1.8.16/themes/smoothness/jquery.ui.all.min.css');?>
        
        <?php Yii::app()->clientScript->registerScriptFile(Yii::app()->baseurl . '/js/main.js?3');?>
        <?php Yii::app()->clientScript->registerScriptFile(Yii::app()->baseurl . '/js/jquery.mousewheel.js');?>
        <?php Yii::app()->clientScript->registerScriptFile(Yii::app()->baseurl . '/js/jquery.autocomplete.js');?>
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