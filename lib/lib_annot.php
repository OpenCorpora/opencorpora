<?php
function sentence_page($sent_id) {
    $tf_text = array();
    $tokens = array();
    $res = sql_query("SELECT tf_id, tf_text, dict_updated FROM text_forms WHERE sent_id=$sent_id ORDER BY `pos`");
    $j = 0; //token position, for further highlighting
    while($r = sql_fetch_array($res)) {
        array_push ($tf_text, '<span id="src_token_'.($j++).'">'.$r['tf_text'].'</span>');
        $rev = sql_fetch_array(sql_query("SELECT rev_text FROM tf_revisions WHERE tf_id=".$r['tf_id']." ORDER BY rev_id DESC LIMIT 1"));
        $tokens[$r['tf_id']] = $rev['rev_text'];
        $dict_updated[$r['tf_id']] = $r['dict_updated'];
    }
    $out = '<div id="source_text"><b>Исходный текст:</b> '.typo_spaces(implode(' ', $tf_text), 1);
    $out .= '</div><form method="post" action="?id='.$sent_id.'&act=save">';
    $out .= '<div id="main_scroller"><span id="scr_ll" onMouseDown="startScroll(-50)" onMouseUp="endScroll()">&lt;&lt;</span><span id="scr_l" onMouseDown="startScroll(-20)" onMouseUp="endScroll()">&lt;</span><div>';
    if (is_logged())
        $out .= '<button type="submit" disabled="disabled" id="submit_button">Сохранить</button>&nbsp;';
    $out .= '<button type="reset" onClick="window.location.reload()">Отменить правки</button>&nbsp;<button type="button" onClick="window.location.href=\'history.php?sent_id='.$sent_id.'\'">История</button></div><span id="scr_rr" onMouseDown="startScroll(50)" onMouseUp="endScroll()">&gt;&gt;</span><span id="scr_r" onMouseDown="startScroll(20)" onMouseUp="endScroll()">&gt;</span></div><br/><br/><div id="main_annot"><table><tr>';
    foreach($tokens as $tid=>$xml) {
        $arr = xml2ary($xml);
        $tf = $arr['tf_rev']['_a']['text'];
        $var = $arr['tf_rev']['_c']['var'];
        $out .= '<td id="var_'.$tid.'"><div class="tf">'.htmlspecialchars($tf);
        if ($dict_updated[$tid]) {
            $out .= '<a href="#" class="reload" title="Разобрать заново из словаря" onClick="dict_reload(this)">D</a>';
        }
        $out .= '</div>';
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
    $out = '<div class="var" id="var_'.$tf_id.'_'.$num.'"><img src="spacer.gif" width="100" height="1"/><input type="hidden" name="var_flag['.$tf_id.']['.$num.']" value="1"/>'.($lemma_attr['id']>0?'<a href="'.$config['web_prefix'].'/dict.php?id='.$lemma_attr['id'].'">'.$lemma_attr['text'].'</a>':'<span>'.$lemma_attr['text'].'</span>').'<a href="#" class="best_var" onClick="best_var(this.parentNode); return false">v</a><a href="#" class="del_var" onClick="del_var(this.parentNode); return false">x</a><br/>'.implode(', ', $grm_arr).'</div>';
    return $out;
}
function sentence_save() {
    $flag = $_POST['var_flag'];
    $dict = $_POST['dict_flag'];
    $sent_id = (int)$_GET['id'];
    $res = sql_query("SELECT tf_id, tf_text, `pos` FROM text_forms WHERE sent_id=$sent_id ORDER BY `pos`");
    while($r = sql_fetch_array($res)) {
        $rev = sql_fetch_array(sql_query("SELECT rev_text FROM tf_revisions WHERE tf_id=".$r['tf_id']." ORDER BY rev_id DESC LIMIT 1"));
        $tokens[$r['tf_id']] = array($r['tf_text'], $rev['rev_text']);
    }
    $matches = array();
    $all_changes = array();
    if(count($flag) != count($tokens)) {
        print("Internal error 1: Cannot save");
        if (is_admin()) {
            print "\nflag:\n".print_r($flag, 1);
            print "\ntokens:\n".print_r($tokens, 1);
        }
        exit(0);
    }
    foreach($tokens as $tf_id=>$v) {
        list($tf_text, $base_xml) = $v;
        //substitute the last revision's xml for one from dictionary if relevant
        if ($dict[$tf_id] == 1)
            $xml = generate_tf_rev($tf_text);
        else
            $xml = $base_xml;
        $new_xml = "<tf_rev text=\"$tf_text\">";
        //let's find all vars inside tf_text
        if (preg_match_all("/<var>(.+?)<\/var>/", $xml, $matches) !== false) {
            //flags quantity check
            if (count($matches[1]) != count($flag[$tf_id])) {
                print "Internal error 3: Cannot save\n";
                if (is_admin()) {
                    print "matches:\n".print_r($matches[1], true);
                    print "flag:\n".print_r($flag[$tf_id], true);
                }
                exit(0);
            }
            $not_empty = 0;
            foreach($flag[$tf_id] as $k=>$f) {
                if ($f == 1) {
                    $not_empty = 1;
                    $new_xml .= '<var>'.$matches[1][$k-1].'</var>'; //attention to -1
                }
            }
            //inserting UnknownPOS if no variants present
            if (!$not_empty) {
                $new_xml .= '<var><lemma id="0" text="'.htmlspecialchars(lc($tf_text)).'"><grm val="UnknownPOS"/></lemma></var>';
            }
            $new_xml .= '</tf_rev>';
            if ($base_xml != $new_xml) {
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
