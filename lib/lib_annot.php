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
    $out .= '<br/><br/><form method="post" action="?id='.$sent_id.'&act=save">';
    $out .= '<div id="main_scroller"><span id="scr_ll" onClick="scroll_annot(-50)">&lt;&lt;</span><span id="scr_l" onClick="scroll_annot(-20)">&lt;</span><div>';
    if (is_logged())
        $out .= '<button type="submit" disabled="disabled" id="submit_button">Сохранить</button>&nbsp;';
    $out .= '<button type="reset" onClick="window.location.reload()">Отменить правки</button></div><span id="scr_rr" onClick="scroll_annot(50)">&gt;&gt;</span><span id="scr_r" onClick="scroll_annot(20)">&gt;</span></div><br/><div id="main_annot"><table><tr>';
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
function sentence_save() {
    $flag = $_POST['var_flag'];
    $sent_id = (int)$_GET['id'];
    # todo: we may instead need to grab the variants from the dictionary
    $res = sql_query("SELECT tf_id, tf_text, `pos` FROM text_forms WHERE sent_id=$sent_id ORDER BY `pos`");
    while($r = sql_fetch_array($res)) {
        $rev = sql_fetch_array(sql_query("SELECT rev_text FROM tf_revisions WHERE tf_id=".$r['tf_id']." ORDER BY rev_id DESC LIMIT 1"));
        $tokens[$r['pos']-1] = array($r['tf_id'], $r['tf_text'], $rev['rev_text']);
    }
    $matches = array();
    $all_changes = array();
    if(count($flag) != count($tokens))
        die("Internal error 1: Cannot save");
    foreach($tokens as $i=>$v) {
        list($tf_id, $tf_text, $xml) = $v;
        $new_xml = "<tf_rev text=\"$tf_text\">";
        #let's find all vars inside tf_text
        if (preg_match_all("/<var>(.+?)<\/var>/", $xml, $matches) !== false) {
            if (count($matches[1]) != count($flag[$i]))
                die("Internal error 3: Cannot save");
            foreach($flag[$i] as $k=>$f) {
                if ($f == 1) {
                    $new_xml .= '<var>'.$matches[1][$k-1].'</var>'; #attention to -1
                }
            }
            $new_xml .= '</tf_rev>';
            if ($xml != $new_xml) {
                //something's changed
                array_push($all_changes, array($tf_id, $new_xml));
            }
        } else {
            die ("Internal error 2: Cannot save");
        }
    }
    if (count($all_changes)>0) {
        $revset_id = create_revset();
        if (!$revset_id)
            die ("Cannot create revset");
        foreach ($all_changes as $v) {
            if (!sql_query("INSERT INTO `tf_revisions` VALUES(NULL, '$revset_id', '$v[0]', '".mysql_real_escape_string($v[1])."')"))
                die ("Internal error 4: Cannot save");
        }
    }
    header("Location:sentence.php?id=$sent_id");
}
?>
