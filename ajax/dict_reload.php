<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_dict.php');
require_once('../lib/lib_annot.php');

if (isset($_POST['tf_id'])) {
    $res = sql_pe("SELECT tf_text FROM tokens WHERE tf_id=? LIMIT 1", array($_POST['tf_id']));
    $r = $res[0];
    $arr = xml2ary(generate_tf_rev($r['tf_text']));
    $vars = get_morph_vars($arr['tfr']['_c']['v']);

    $result['xml'] =  "<tfr t=\"".htmlspecialchars($arr['tfr']['_a']['t'])."\">";
    foreach($vars as $var) {
        $result['xml'] .= '<v><l id="'.$var['lemma_id'].'" t="'.htmlspecialchars($var['lemma_text']).'">';
        foreach($var['gram_list'] as $gram) {
            if (isset($_SESSION['options']) && $_SESSION['options'][1] == 1) {
                $result['xml'] .= '<g v="'.$gram['outer'].'" d="'.$gram['descr'].'"/>';
            } else {
                $result['xml'] .= '<g v="'.$gram['inner'].'" d="'.$gram['descr'].'"/>';
            }
        }
        $result['xml'] .= '</l></v>';
    }
    $result['xml'] .= '</tfr>';
}
log_timing(true);
die(json_encode($result));
?>
