<?php
require('lib/header.php');
require('lib/lib_books.php');
$action = $_GET['act'];
if($action=='add' && is_admin()) {
    $book_name = mysql_real_escape_string($_POST['book_name']);
    $book_parent = (int)$_POST['book_parent'];
    books_add($book_name, $book_parent);
} elseif ($action=='rename' && is_admin()) {
    $name = mysql_real_escape_string($_POST['new_name']);
    $book_id = (int)$_POST['book_id'];
    books_rename($book_id, $name);
} elseif ($action=='move' && is_admin()) {
    $book_id = (int)$_POST['book_id'];
    $book_to = (int)$_POST['book_to'];
    books_move($book_id, $book_to);
}
?>
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='<?=$config['web_prefix']?>/css/main.css'/>
<script language='JavaScript' src='<?=$config['web_prefix']?>/js/main.js'></script>
</head>
<body>
<?php require('include/_header.php'); ?>
<div id='content'>
<?php
//административные опции
if (is_admin()) {
    if($book_id = (int)$_GET['book_id']) {
        print books_page($book_id);
    } else {
        print books_mainpage();
    }
} else {
    print $config['msg_notadmin'];
}
?>
</div>
<div id='rightcol'><?php require('include/_right.php'); ?></div>
</body>
</html>
