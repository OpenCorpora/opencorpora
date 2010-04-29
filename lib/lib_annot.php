<?php
function sentence_page($sent_id) {
    $tf_text = array();
    $tokens = array();
    $res = sql_query("SELECT tf_id, tf_text FROM text_forms WHERE sent_id=$sent_id ORDER BY `pos`");
    while($r = sql_fetch_array($res)) {
        array_push ($tf_text, $r['tf_text']);
        $rev = sql_fetch_array(sql_query("SELECT rev_text FROM tf_revisions WHERE tf_id=".$r['tf_id']." ORDER BY rev_id DESC LIMIT 1"));
        $tokens[$r['tf_id']] = $rev['rev_text'];
    }
    $out = '<b>Исходный текст:</b> '.implode(' ', $tf_text);
    $out .= '<br/><br/><div id="main_scroller"><span id="scr_ll" onClick="scroll_annot(-50)">&lt;&lt;</span><span id="scr_l" onClick="scroll_annot(-20)">&lt;</span><span id="scr_rr" onClick="scroll_annot(50)">&gt;&gt;</span><span id="scr_r" onClick="scroll_annot(20)">&gt;</span></div><br/><div id="main_annot"><table><tr>';
    foreach($tokens as $tid=>$xml) {
        $arr = xml2ary($xml);
        $tf = $arr['tf_rev']['_a']['text'];
        $var = $arr['tf_rev']['_c']['var'];
        $out .= '<td><div class="tf">'.htmlspecialchars($tf).'</div>';
        if (is_array($var['_c'])) {
            #only one var
            $out .= generate_var_div($var, $tid, 1);
        }
        else {
            $num = 1;
            #multiple vars
            foreach($var as $var_arr) {
                $out .= generate_var_div($var_arr, $tid, $num++);
            }
        }
        $out.= '</td>';
    }
    $out .= '</tr></table></div>';
    return $out;
}
function generate_var_div($var_arr, $tf_id, $num) {
    $lemma_attr = $var_arr['_c']['lemma']['_a'];
    $lemma_grm = $var_arr['_c']['lemma']['_c']['grm'];
    $grm_arr = array();
    if (is_array($lemma_grm['_a'])) {
        array_push($grm_arr, $lemma_grm['_a']['val']);
    } else {
        foreach($lemma_grm as $t) {
            array_push($grm_arr, $t['_a']['val']);
        }
    }
    $out = '<div class="var"><img src="spacer.gif" width="100" height="1"/><input name="var_flag['.$tf_id.']['.$num.']" value="1"/><a href="'.$config['web_prefix'].'/dict.php?id='.$lemma_attr['id'].'">'.$lemma_attr['text'].'</a><a href="#" class="del_var">x</a><br/>'.implode(', ', $grm_arr).'</div>';
    return $out;
}
?>
