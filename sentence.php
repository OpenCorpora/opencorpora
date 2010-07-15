<?php
require('lib/header.php');
require('lib/lib_xml.php');
require('lib/lib_annot.php');
require('lib/lib_dict.php');
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
} else {
    header('Location:index.php');
}
if (isset($_GET['act'])) {
    $action = $_GET['act'];
    switch($action) {
        case 'save':
            if (is_logged()) {
                sentence_save();
            } else {
                die ($config['msg_notlogged']);
            }
            break;
    }
}
?>
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='<?=$config['web_prefix']?>/css/main.css'/>
<script language='JavaScript' src='<?=$config['web_prefix']?>/js/main.js'></script>
</head>
<body onload="highlight_source(); document.onkeyup=checkKeyUp; document.onkeydown=checkKeyDown; document.onmouseup=endScroll;">
<?php require('include/_header.php'); ?>
<div id='content'>
<?php
print sentence_page($id);
?>
</div>
<div id='rightcol'><?php require('include/_right.php'); ?></div>
</body>
</html>
