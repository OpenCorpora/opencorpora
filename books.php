<?php
require('lib/header.php');
require('lib/lib_books.php');
$action = $_GET['act'];
if (is_admin()) {
    if($action=='add') {
        $book_name = mysql_real_escape_string($_POST['book_name']);
        $book_parent = (int)$_POST['book_parent'];
        books_add($book_name, $book_parent);
    } elseif ($action=='rename') {
        $name = mysql_real_escape_string($_POST['new_name']);
        $book_id = (int)$_POST['book_id'];
        books_rename($book_id, $name);
    } elseif ($action=='move') {
        $book_id = (int)$_POST['book_id'];
        $book_to = (int)$_POST['book_to'];
        books_move($book_id, $book_to);
    } elseif ($action=='add_tag') {
        $book_id = (int)$_POST['book_id'];
        $tag_name = mysql_real_escape_string($_POST['tag_name']);
        books_add_tag($book_id, $tag_name);
    } elseif ($action=='del_tag') {
        $book_id = (int)$_GET['book_id'];
        $tag_name = mysql_real_escape_string($_GET['tag_name']);
        books_del_tag($book_id, $tag_name);
    }
} else {
    print $config['msg_notadmin'];
    exit;
}
?>
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='<?php echo $config['web_prefix']?>/css/main.css'/>
<script language='JavaScript' src='<?php echo $config['web_prefix']?>/js/main.js'></script>
</head>
<body>
<?php require('include/_header.php'); ?>
<div id='content'>
<?php
//admin options
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
