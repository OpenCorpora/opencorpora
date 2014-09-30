<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_dict.php');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><response>';
$para = get_word_paradigm($_GET['word']);
if ($para) {
    echo '<lemma gram="'.join(', ', $para['lemma_gram']).'" suffix="'.$para['lemma_suffix_len'].'"/>';
    foreach ($para['forms'] as $form) {
        echo '<form gram="'.join(', ', $form['grm']).'" suffix="'.$form['suffix'].'"/>';
    }
}
echo '</response>';
log_timing(true);
?>
