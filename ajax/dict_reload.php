<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_dict.php');
require_once('../lib/lib_annot.php');
require_once('../lib/lib_users.php');

try {
    $res = sql_pe("SELECT tf_text FROM tokens WHERE tf_id=? LIMIT 1", array(POST('tf_id')));
    $r = $res[0];
    $pset = new MorphParseSet(false, $r['tf_text'], false, true);

    $result['xml'] = "<tfr>";
    foreach ($pset->parses as $parse) {
        $result['xml'] .= '<v><l id="'.$parse->lemma_id.'" t="'.htmlspecialchars($parse->lemma_text).'">';
        foreach($parse->gramlist as $gram) {
            if (OPTION(OPT_GRAMNAMES) == 1) {
                $result['xml'] .= '<g v="'.$gram['outer'].'" d="'.$gram['descr'].'"/>';
            } else {
                $result['xml'] .= '<g v="'.$gram['inner'].'" d="'.$gram['descr'].'"/>';
            }
        }
        $result['xml'] .= '</l></v>';
    }
    $result['xml'] .= '</tfr>';
}
catch (Exception $e) {
    $result['error'] = 1;
}

log_timing(true);
die(json_encode($result));
?>
