<?php
require('lib/header.php');
require('lib/lib_xml.php');
require('lib/lib_annot.php');
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
} else {
    header('Location:index.php');
}
$action = $_GET['act'];
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
print sentence_page($id);
?>
</div>
<div id='rightcol'><?php require('include/_right.php'); ?></div>
</body>
</html>
