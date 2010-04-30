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
    $out .= '<br/><br/><form method="post" action="?id='.$sent_id.'&save=1"><div id="main_scroller"><span id="scr_ll" onClick="scroll_annot(-50)">&lt;&lt;</span><span id="scr_l" onClick="scroll_annot(-20)">&lt;</span><div><button type="submit" disabled="disabled" id="submit_button">Сохранить</button>&nbsp;<button type="reset" onClick="window.location.reload()">Отменить правки</button></div><span id="scr_rr" onClick="scroll_annot(50)">&gt;&gt;</span><span id="scr_r" onClick="scroll_annot(20)">&gt;</span></div><br/><div id="main_annot"><table><tr>';
    $tid = 0;
    foreach($tokens as $xml) {
        $arr = xml2ary($xml);
        $tf = $arr['tf_rev']['_a']['text'];
        $var = $arr['tf_rev']['_c']['var'];
        $out .= '<td><div class="tf">'.htmlspecialchars($tf).'</div>';
        if (is_array($var['_c'])) {
            #only one var
            $out .= generate_var_div($var, $tid++, 1);
        }
        else {
            $num = 1;
            #multiple vars
            foreach($var as $var_arr) {
                $out .= generate_var_div($var_arr, $tid, $num++);
            }
            $tid++;
        }
        $out.= '</td>';
    }
    $out .= '</tr></table></div></form>';
    return $out;
}
function generate_var_div($var_arr, $tf_id, $num) {
    global $config;
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
    $out = '<div class="var" id="var_'.$tf_id.'_'.$num.'"><img src="spacer.gif" width="100" height="1"/><input type="hidden" name="var_flag['.$tf_id.']['.$num.']" value="1"/>'.($lemma_attr['id']>0?'<a href="'.$config['web_prefix'].'/dict.php?id='.$lemma_attr['id'].'">'.$lemma_attr['text'].'</a>':$lemma_attr['text']).'<a href="#" class="best_var" onClick="best_var(this.parentNode); return false">v</a><a href="#" class="del_var" onClick="del_var(this.parentNode); return false">x</a><br/>'.implode(', ', $grm_arr).'</div>';
    return $out;
}
?>
