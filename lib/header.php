<?php
if (!headers_sent()) {
    session_start();
    header("Content-type: text/html; charset=utf-8");
}

$config = parse_ini_file(__DIR__ . '/../config.ini', true);
require_once(__DIR__.'/../vendor/autoload.php'); // Smarty, something else which was installed via Composer
require_once('common.php');
require_once('constants.php');
require_once('lib_awards.php');
require_once('lib_achievements.php');
require_once('timer.php');

$smarty = new Smarty(); // no need to require the Smarty.php, it was autoloaded
$smarty->template_dir = $config['smarty']['template_dir'];
$smarty->compile_dir  = $config['smarty']['compile_dir'];
$smarty->config_dir   = $config['smarty']['config_dir'];
$smarty->cache_dir    = $config['smarty']['cache_dir'];

//database connect
$pdo_db = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8', $config['mysql']['host'], $config['mysql']['dbname']), $config['mysql']['user'], $config['mysql']['passwd']);
$pdo_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
if (
    is_logged()
    && in_array(PERM_ADMIN, $_SESSION['user_groups'])
    && isset($_GET['pretend'])
    && $pretend = $_GET['pretend']
) {
    if ($pretend == 'on')
        $_SESSION['noadmin'] = 1;
    elseif ($pretend == 'off')
        unset($_SESSION['noadmin']);
    header("Location:".$_SERVER['HTTP_REFERER']);
    exit;
}

//some globals
$smarty->assign('yandex_metrika_counter_id', $config['web']['yandex_metrika_counter_id']);
$smarty->assign('is_admin', is_admin() ? 1 : 0);
$smarty->assign('is_logged', is_logged() ? 1 : 0);
if (is_logged()) {
    $smarty->assign('is_openid', is_user_openid($_SESSION['user_id']) ? 1 : 0);
}
$smarty->assign('user_permission_dict', user_has_permission(PERM_DICT) ? 1 : 0);
$smarty->assign('user_permission_disamb', user_has_permission(PERM_DISAMB) ? 1 : 0);
$smarty->assign('user_permission_adder', user_has_permission(PERM_ADDER) ? 1 : 0);
$smarty->assign('user_permission_check_tokens', user_has_permission(PERM_CHECK_TOKENS) ? 1 : 0);
$smarty->assign('user_permission_check_morph', user_has_permission(PERM_MORPH_MODER) ? 1 : 0);
$smarty->assign('user_permission_merge', user_has_permission(PERM_MORPH_SUPERMODER) ? 1 : 0);
$smarty->assign('user_permission_syntax', user_has_permission(PERM_SYNTAX) ? 1 : 0);
$smarty->assign('user_permission_check_syntax', user_has_permission(PERM_SYNTAX_MODER) ? 1 : 0);
$smarty->assign('readonly', file_exists($config['project']['readonly_flag']) ? 1 : 0);
$smarty->assign('goals', $config['goals']);
$smarty->assign('game_is_on', 0);

//$smarty->configLoad(__DIR__.'/../templates/achievements/titles.conf', NULL);
// smarty->configLoad is a piece of shit which can not handle multiple sections at once.
// reverting to something much simplier.
$smarty->assign('achievements_titles', parse_ini_file(__DIR__.'/../templates/achievements/titles.conf', TRUE));

if (is_logged()) {
    if (game_is_on()) {
        $smarty->assign('game_is_on', 1);
        $am = new AchievementsManager($_SESSION['user_id']);
        $smarty->assign('achievements', $a = $am->pull_all());
        $smarty->assign('achievements_unseen', array_filter($a, function($e) {
            return !$e->seen;
        }));
    }
}

// alert messages
$smarty->assign('alerts',alert_getall());
?>
