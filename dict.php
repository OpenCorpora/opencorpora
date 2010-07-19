<?php
require('lib/header.php');
require('lib/lib_dict.php');
$action = $_GET['act'];
if (is_admin()) {
    switch($action) {
        case 'add_gg':
            $name = mysql_real_escape_string($_POST['g_name']);
            add_gramtype($name);
            break;
        case 'add_gram':
            $name = mysql_real_escape_string($_POST['g_name']);
            $group = (int)$_POST['group'];
            $aot_id = mysql_real_escape_string($_POST['aot_id']);
            $descr = mysql_real_escape_string($_POST['descr']);
            add_grammem($name, $group, $aot_id, $descr);
            break;
        case 'save':
            dict_save($_POST);
            break;
    }
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
if (is_admin()) {
    switch($action) {
        case 'gram':
            print dict_page_gram();
            break;
        case 'lemmata':
            print dict_page_lemmata();
            break;
        case 'edit':
            $lid = (int)$_GET['id'];
            print dict_page_lemma_edit($lid);
            break;
        default:
            print dict_page();
    }
} else {
    print $config['msg_notadmin'];
}
?>
</div>
<div id='rightcol'><?php require('include/_right.php'); ?></div>
</body>
</html>
