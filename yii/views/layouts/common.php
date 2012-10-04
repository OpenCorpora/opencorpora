<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv='content' content='text/html;charset=utf-8'/>
        <!-- Bootstrap -->
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

        <link rel='stylesheet' type='text/css' href='<?php echo Yii::app()->request->baseUrl; ?>/css/main.css?4'/>
        <link rel='stylesheet' type='text/css' href='http://yandex.st/jquery-ui/1.8.16/themes/smoothness/jquery.ui.all.min.css'/>
        <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-1.8.1.min.js"></script>
        <script type="text/javascript" src="http://yandex.st/jquery-ui/1.8.16/jquery-ui.min.js"></script>
        <script src='<?php echo Yii::app()->request->baseUrl; ?>/js/main.js?3' type='text/javascript'></script>
        <script src='<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.mousewheel.js' type='text/javascript'></script>
        <script src='<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.autocomplete.js'></script>
        <script src="bootstrap/js/bootstrap.min.js"></script>
        <title>OpenCorpora: открытый корпус русского языка</title>
    </head>
<?php if (isset($body)) echo $body; else echo "<body>\n"; ?>
<div id='wrap'>
<?php include('header.php'); ?>
<?php if (isset($before_content)) echo $before_content; ?>
<?php if (isset($readonly) && $readonly): ?>
<div class='alert alert-error'><div class="container">Система находится в режиме &laquo;только для чтения&raquo;.</div></div>
<?php endif; ?>
<div id="container" class="container">
<?php if (isset($game_is_on) && $game_is_on): include('user_splash.php'); endif; ?>
<div id="alert_wrap"><?php if (isset($alerts)): foreach ($alerts as $type => $message): ?><div class="alert alert-<?php echo $type; ?>"><?php echo $message; ?></div><?php endforeach; ?><script>setTimeout('$("#alert_wrap .alert").fadeOut()',3000);</script><?php endif; ?>
</div>
<?php echo $content; ?>
</div>
<?php include('footer.php'); ?>
</div>
</body>
</html>
