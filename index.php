<?php
require('lib/header.php');
if (isset($_GET['rand'])) {
    $r = sql_fetch_array(sql_query("SELECT sent_id FROM sentences ORDER BY RAND() LIMIT 1", 0));
    header("Location:sentence.php?id=".$r['sent_id']);
}
?>
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='<?php echo $config['web_prefix']?>/css/main.css'/>
</head>
<body>
<?php require('include/_header.php'); ?>
<div id='content'>
<?php
//admin options
if (is_admin()) {
    ?>
    <a href='<?php echo $config['web_prefix']?>/books.php'>Редактор источников</a><br/>
    <a href='<?php echo $config['web_prefix']?>/dict.php'>Редактор словаря</a><br/><br/>
    <a href='<?php echo $config['web_prefix']?>/add.php'>Добавить текст</a><br/>
    <br/>
    <?php
}
?>
<a href='?rand'>Случайное предложение</a><br/>
</div>
<div id='rightcol'><?php require('include/_right.php'); ?></div>
</body>
</html>
