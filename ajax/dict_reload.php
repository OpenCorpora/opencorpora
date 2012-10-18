<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_dict.php');
require_once('../lib/lib_annot.php');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><response>';
if (isset($_GET['tf_id'])) {
    $tf_id = (int)$_GET['tf_id'];
    $r = sql_fetch_array(sql_query("SELECT tf_text FROM text_forms WHERE tf_id=$tf_id LIMIT 1"));
    $arr = xml2ary(generate_tf_rev($r['tf_text']));
    $vars = get_morph_vars($arr['tfr']['_c']['v']);

    echo "<tfr t=\"".htmlspecialchars($arr['tfr']['_a']['t'])."\">";
    foreach($vars as $var) {
        echo '<v><l id="'.$var['lemma_id'].'" t="'.htmlspecialchars($var['lemma_text']).'">';
        foreach($var['gram_list'] as $gram) {
            if (isset($_SESSION['options']) && $_SESSION['options'][1] == 1) {
                echo '<g v="'.$gram['outer'].'" d="'.$gram['descr'].'"/>';
            } else {
                echo '<g v="'.$gram['inner'].'" d="'.$gram['descr'].'"/>';
            }
        }
        echo '</l></v>';
    }
    echo '</tfr>';
}
echo '</response>';
?>
