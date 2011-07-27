<?php
/*
Since this configuration file is environment-dependent,
it should be written manually on checkout. You should
copy this template as config.php and change the appropriate
values. You should NOT svn add it.
*/
require_once('config_msg.php');

$config['web_prefix'] = ''; # '' or '/dir'

$config['mysql_host']   = 'localhost';
$config['mysql_dbname'] = '';
$config['mysql_user']   = '';
$config['mysql_passwd'] = '';

$config['smarty_template_dir'] = '/var/www/templates/';
$config['smarty_compile_dir'] = '/var/www/smarty_dir/templates_c/';
$config['smarty_config_dir'] = '/var/www/smarty_dir/configs/';
$config['smarty_cache_dir'] = '/var/www/smarty_dir/cache/';

$config['goals']['total_words'] = 1000000;
$config['goals']['wikipedia_words'] = 100000;
$config['goals']['chaskor_words'] = 250000;
$config['goals']['wikinews_words'] = 250000;
?>
