<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_dict.php');
require_once('../lib/lib_annot.php');

if (isset($_POST['tf_id'])) {
    $res = sql_pe("SELECT tf_text FROM tokens WHERE tf_id=? LIMIT 1", array($_POST['tf_id']));
    $r = $res[0];
    $pset = new MorphParseSet(false, $r['tf_text']);

    $result['xml'] = "<tfr>";
    foreach($pset->parses as $parse) {
        $result['xml'] .= '<v><l id="'.$parse->lemma_id.'" t="'.htmlspecialchars($parse->lemma_text).'">';
        foreach($parse->gramlist as $gram) {
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
