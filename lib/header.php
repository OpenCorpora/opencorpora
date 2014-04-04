<?php
if (!headers_sent()) {
    session_start();
    header("Content-type: text/html; charset=utf-8");
}

$config = parse_ini_file(dirname(__FILE__) . '/../config.ini', true);

require_once('common.php');
require_once('lib_awards.php');

//init Smarty
require_once('Smarty.class.php');

$smarty = new Smarty();
$smarty->template_dir = $config['smarty']['template_dir'];
$smarty->compile_dir  = $config['smarty']['compile_dir'];
$smarty->config_dir   = $config['smarty']['config_dir'];
$smarty->cache_dir    = $config['smarty']['cache_dir'];

//database connect
$db = mysql_connect($config['mysql']['host'], $config['mysql']['user'], $config['mysql']['passwd']) or die ("Unable to connect to mysql server");
if (!sql_query("USE ".$config['mysql']['dbname'], 0, 1)) {
    die ("Unable to open mysql database");
}
sql_query("SET names utf8", 0, 1);

// another connect (temporary) for PDO
$pdo_db = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', $config['mysql']['host'], $config['mysql']['dbname']), $config['mysql']['user'], $config['mysql']['passwd']);
$pdo_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$pdo_db->query("SET NAMES utf8");

$transaction_counter = 0;
$nested_transaction_counter = 0;
$total_time = 0;
$total_queries = 0;

set_exception_handler('oc_exception_handler');

//cookie check
if (!is_logged() && !isset($_SESSION['user_pending']) && isset($_COOKIE['auth'])) {
    if ($user_id = check_auth_cookie()) {
        if (user_login('', '', $user_id, $_COOKIE['auth'])) {
            header("Location:".$_SERVER['REQUEST_URI']);
            exit;
        }
    }
}

//debug mode
if (is_admin() && isset($_GET['debug']) && $debug = $_GET['debug']) {
    if ($debug == 'on' && !isset($_SESSION['debug_mode'])) {
        $_SESSION['debug_mode'] = 1;
    } elseif ($debug == 'off' && $_SESSION['debug_mode']) {
        unset ($_SESSION['debug_mode']);
    }
    header("Location:".$_SERVER['HTTP_REFERER']);
    exit;
}

//admin pretends that he is a user
if (is_logged() && isset($_SESSION['user_permissions']['perm_admin']) && $_SESSION['user_permissions']['perm_admin'] == 1 && isset($_GET['pretend']) && $pretend = $_GET['pretend']) {
    if ($pretend == 'on')
        $_SESSION['user_permissions']['pretend'] = 1;
    elseif ($pretend == 'off')
        unset($_SESSION['user_permissions']['pretend']);
    header("Location:".$_SERVER['HTTP_REFERER']);
    exit;
}

//some globals
$smarty->assign('web_prefix', $config['web']['prefix']);
$smarty->assign('is_admin', is_admin() ? 1 : 0);
$smarty->assign('is_logged', is_logged() ? 1 : 0);
if (is_logged()) {
    $smarty->assign('is_openid', is_user_openid($_SESSION['user_id']) ? 1 : 0);
}
$smarty->assign('user_permission_dict', user_has_permission('perm_dict') ? 1 : 0);
$smarty->assign('user_permission_disamb', user_has_permission('perm_disamb') ? 1 : 0);
$smarty->assign('user_permission_adder', user_has_permission('perm_adder') ? 1 : 0);
$smarty->assign('user_permission_check_tokens', user_has_permission('perm_check_tokens') ? 1 : 0);
$smarty->assign('user_permission_check_morph', user_has_permission('perm_check_morph') ? 1 : 0);
$smarty->assign('user_permission_merge', user_has_permission('perm_merge') ? 1 : 0);
$smarty->assign('user_permission_syntax', user_has_permission('perm_syntax') ? 1 : 0);
$smarty->assign('user_permission_check_syntax', user_has_permission('perm_check_syntax') ? 1 : 0);
$smarty->assign('readonly', file_exists('/var/lock/oc_readonly.lock') ? 1 : 0);
$smarty->assign('svn_revision', @file_get_contents(__DIR__.'/../.svnrevision'));
$smarty->assign('goals', $config['goals']);
$smarty->assign('game_is_on', 0);

if (is_logged()) {
    if (game_is_on()) {
        $smarty->assign('game_is_on', 1);
        $new_badge = check_user_badges($_SESSION['user_id']);
        //$new_level = check_user_level($_SESSION['user_id']);

        if ($new_badge) {
            $smarty->assign('new_badge', $new_badge);
            mark_shown_badge($_SESSION['user_id'], $new_badge['id']);
        }
        //if ($new_level)
        //    update_user_level($new_level);
        //if ($new_level > 1)
        //    $smarty->assign('new_level', $new_level);
    }
}

// alert messages
$smarty->assign('alerts',alert_getall());
?>
