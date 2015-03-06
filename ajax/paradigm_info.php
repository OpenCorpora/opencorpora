<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_dict.php');

$para = get_word_paradigm($_POST['word']);
if ($para) {
    $result['lemma'] = array('gram' => $para['lemma_gram'], 'suffix' => $para['lemma_suffix_len']);
    $result['forms'] = array();
    foreach ($para['forms'] as $form) {
        $result['forms'][] = array('gram' => join(', ', $form['grm']), 'suffix' => $form['suffix']);
    }
}
else
    $result['error'] = 1;

log_timing(true);
die(json_encode($result));
?>
