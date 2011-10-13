<?php

function get_generator_status() {
    global $config;

    $fp = fopen($config['generator']['status'], 'r');
    flock($fp, LOCK_SH);
    $fstat = fstat($fp);
    $current_status = rtrim(fread($fp, 32));
    flock($fp, LOCK_UN);
    fclose($fp);

    if(!$current_status) {
        $current_status = 'error';
    }

    return array('status' => $current_status, 'since' => date('d-m-Y H:i:s', $fstat['mtime']));
}

function set_generator_status($new_status) {
    global $config;

    $fp = fopen($config['generator']['status'], 'w');
    flock($fp, LOCK_EX);
    fwrite($fp, $new_status);
    flock($fp, LOCK_UN);
    fclose($fp);
}

function toggle_generator_status() {
    $current = get_generator_status();

    switch($current['status']) {
        case 'enabled':
            set_generator_status('disabled');
            break;
        case 'disabled':
            set_generator_status('enabled');
            break;
    }

    return get_generator_status();
}

function run_generator($tag) {
    global $config;

    $pieces = array(
        'perl',
        $config['generator']['script'],
        $tag,
        dirname(__FILE__) . '/../config.ini',
        $config['generator']['data_dir'],
        '2>&1'
    );
    $cmd = implode(' ', $pieces);

    $output = array();
    exec($cmd, $output, $retval);

    return array(
        'success' => (bool)!$retval,
        'output'  => implode("\n", $output)
    );
}

?>
