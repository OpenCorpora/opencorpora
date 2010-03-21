<?php
require('lib/header.php');
require('lib/lib_dict.php');
$action = $_GET['act'];
if (is_admin()) {
    switch($action) {
    }
}
?>
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='<?=$config['web_prefix']?>/css/main.css'/>
</head>
<body>
<?php require('include/_header.php'); ?>
<div id='content'>
<?php
if (is_admin()) {
    switch($action) {
        case 'check':
            print addtext_check($_POST['txt']);
            break;
        default:
            print addtext_page();
    }
} else {
    print $config['msg_notadmin'];
}
?>
</div>
<div id='rightcol'><?php require('include/_right.php'); ?></div>
</body>
</html>
