<?php

require_once('common.php');

function get_generator_status() {
    global $config;

    $stat = stat($config['generator']['status']);
    $current_status = safe_read($config['generator']['status'], 32);
    $current_tag = safe_read($config['generator']['current_tag'], 32);
    $next_tag = safe_read($config['generator']['next_tag'], 32);

    if ($current_status === FALSE) {
        $current_status = 'error';
    }

    if ($current_tag === FALSE) {
        $current_tag = 'n/a';
        $current_status = 'error';
    }

    if (!$next_tag || $next_tag === $current_tag) {
        $next_tag = 'n/a';
    }

    return array(
        'since'  => date('d-m-Y H:i:s', $stat['mtime']),
        'status' => $current_status,
        'tag'    => $current_tag,
        'next'   => $next_tag
    );
}

function set_generator_status($new_status) {
    global $config;

    safe_write($config['generator']['status'], 'w', $new_status);
}

function toggle_generator_status() {
    $current = get_generator_status();

    switch ($current['status']) {
        case 'enabled':
            set_generator_status('disabled');
            break;
        case 'disabled':
            set_generator_status('enabled');
            break;
    }

    return get_generator_status();
}

function run_generator() {
    global $config;

    $pieces = array(
        $config['project']['perl'],
        $config['generator']['gen_script'],
        '--config=' . __DIR__ . '/../config.ini',
        '--output_dir=' . $config['generator']['tmp_dir'],
        '--data_dir=' . __DIR__ . '/../scripts/tokenizer',
        '2>&1'
    );
    $cmd = implode(' ', $pieces);

    $output = array();
    exec($cmd, $output, $retval);
    $output = preg_grep('/^(?!perl|\s+(?:L|are supported))/', $output);

    return array(
        'success' => (bool)!$retval,
        'output'  => implode("\n", $output)
    );
}

function run_test() {
    global $config;

    $pieces = array(
        $config['project']['perl'],
        '-I' . $config['generator']['perl_lib'],
        $config['generator']['test_script'],
        '--config=' . __DIR__ . '/../config.ini',
        '--data_dir=' . $config['generator']['tmp_dir'],
        '--threshold=0.878', # graph_metrics.pl @ 2011-11-12
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

function publish_update() {
    global $config;

    $next_tag = safe_read($config['generator']['next_tag'], 32);
    if ($next_tag === FALSE) {
        return array(
            'success' => FALSE,
            'output'  => 'No tag to publish',
        );
    }

    $tag_dir = $config['generator']['data_dir'] . '/' . $next_tag;
    if (!file_exists($tag_dir)) {
        mkdir($tag_dir, 0755, TRUE);
    }

    $dp = opendir($config['generator']['tmp_dir']);
    while (($file = readdir($dp)) !== FALSE) {
        if ($file !== '.' && $file !== '..') {
            $tmp = $config['generator']['tmp_dir'] . '/' . $file;
            $production = $tag_dir . '/' . $file;

            if (rename($tmp, $production) == FALSE) {
                return array(
                    'success' => FALSE,
                    'output'  => 'Failed moving ' . $tmp . ' to ' . $production,
                );
            }
        }
    }
    closedir($dp);

    safe_write($config['generator']['current_tag'], 'w', $next_tag);

    return array(
        'success' => TRUE,
        'output'  => 'Tag "' . $next_tag . '" has been published'
    );
}

?>
