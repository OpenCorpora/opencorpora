<?php
require('lib/header.php');
?>
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='css/main.css'/>
</head>
<body>
<?php require('include/_header.php'); ?>
<div id='content'>
<?php
//административные опции
if (is_admin()) {
    ?>
    <a href='books.php'>Редактор источников</a><br/>
    <a href='#'>Редактор словаря</a>
    <?
}
?>
</div>
<div id='rightcol'><?php require('include/_right.php'); ?></div>
</body>
</html>
