<?php
require_once('lib_xml.php');
require_once('lib_books.php');

function dict_page() {
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_gt FROM `gram_types`"));
    $cnt_gt = $r['cnt_gt'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_g FROM `gram`"));
    $cnt_g = $r['cnt_g'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_l FROM `dict_lemmata`"));
    $cnt_l = $r['cnt_l'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_f FROM `form2lemma`"));
    $cnt_f = $r['cnt_f'];
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_r FROM `dict_revisions` WHERE f2l_check=0"));
    $cnt_r = $r['cnt_r'];
    $out = sprintf("<p>Всего %d граммем в %d группах, %d лемм, %d форм в индексе (не проверено %d ревизий).</p>", $cnt_g, $cnt_gt, $cnt_l, $cnt_f, $cnt_r);
    $out .= '<p><a href="?act=gram">Редактор граммем</a><br/>';
    $out .= '<a href="?act=lemmata">Редактор лемм</a></p>';
    return $out;
}
function dict_page_gram() {
    $out = '<p><a href="?">&lt;&lt;&nbsp;назад</a></p>';
    $out .= '<h2>Группы граммем</h2>';
    $out .= '<b>Добавить группу</b>: <form action="?act=add_gg" method="post" class="inline"><input name="g_name" value="&lt;Название&gt;">&nbsp;<input type="submit" value="Добавить"/></form><br/><br/>';
    $out .= '<b>Добавить граммему</b>:<br/><form action="?act=add_gram" method="post" class="inline">ID <input name="g_name" value="grm" size="10" maxlength="20"/>, AOT_ID <input name="aot_id" value="грм" size="10" maxlength="20"/>, группа <select name="group">'.dict_get_select_gramtype().'</select>,<br/>полное название <input name="descr" size="40"/> <input type="submit" value="Добавить"/></form><br/>';
    $out .= '<br/><table border="1" cellspacing="0" cellpadding="2"><tr><th>Название<th>AOT_id<th>Описание</tr>';
    $res = sql_query("SELECT gt.*, g.* FROM `gram_types` gt LEFT JOIN `gram` g ON (gt.type_id = g.gram_type) ORDER BY gt.`orderby`, g.`gram_name`");
    $last_group = '';
    while($r = sql_fetch_array($res)) {
        if ($last_group != $r['type_id']) {
            if ($last_group)
                $out.="</tr>\n";
            $out .= '<tr><td colspan="2"><b>'.$r['type_name']."</b><td>[<a href='#'>вверх</a>] [<a href='#'>вниз</a>]</tr>\n";
            $last_group = $r['type_id'];
        }
        if ($r['gram_id']) {
            $out .= '<tr><td>'.$r['gram_name']."<td>".$r['aot_id']."<td>".$r['gram_descr']."</tr>\n";
        }
    }
    $out .= '</table>';
    return $out;
}
function dict_page_lemmata() {
    $out = '<p><a href="?">&lt;&lt;&nbsp;назад</a></p>';
    $out .= '<h2>Редактор морфологического словаря</h2>';
    $out .= "<form action='?act=lemmata' method='post'>Поиск леммы: <input name='search_lemma' size='25' maxlength='40' value='".(isset($_POST['search_lemma'])?htmlspecialchars($_POST['search_lemma']):'')."'/> <input type='submit' value='Искать'/></form>";
    $out .= "<form action='?act=lemmata' method='post'>Поиск формы: <input name='search_form' size='25' maxlength='40' value='".(isset($_POST['search_form'])?htmlspecialchars($_POST['search_form']):'')."'/> <input type='submit' value='Искать'/></form>";
    if (isset($_POST['search_lemma'])) {
        $out .= dict_block_search_lemma($_POST['search_lemma']);
    } elseif (isset($_POST['search_form'])) {
        $out .= dict_block_search_form($_POST['search_form']);
    }
    return $out;
}
function dict_page_lemma_edit($id) {
    $r = sql_fetch_array(sql_query("SELECT l.`lemma_text`, d.`rev_id`, d.`rev_text` FROM `dict_lemmata` l LEFT JOIN `dict_revisions` d ON (l.lemma_id = d.lemma_id) WHERE l.`lemma_id`=$id ORDER BY d.rev_id DESC LIMIT 1"));
    $out = '<p><a href="?act=lemmata">&lt;&lt;&nbsp;к поиску</a></p>';
    $arr = parse_dict_rev($r['rev_text']);
    $out .= '<form action="" method="post"><b>Лемма</b>:<br/><input name="lemma_text" value="'.$arr['lemma']['_a']['text'].'"/><br/><b>Формы:</b><br/><table cellpadding="3">';
    foreach($arr['form'] as $n=>$farr) {
        $out .= "<tr><td>".$farr['_a']['text']."<td>";
        foreach($farr['_c']['grm'] as $k=>$garr) {
            $out .= $garr['_a']['val'].', ';
        }
        $out .= '</tr>';
    }
    $out .= '</table></form>';
    $out .= '<b>Plain xml:</b><br/><textarea class="small" disabled cols="60" rows="10">'.htmlspecialchars($r['rev_text']).'</textarea>';
    return $out;
}
function addtext_page($txt) {
    $out = '<h3>Добавляем текст</h3>';
    $out .= '<form action="?act=check" method="post"><textarea cols="70" rows="20" name="txt"'.(!$txt?' onClick="this.innerHTML=\'\'; this.onClick=\'\'">':'>').($txt?$txt:'Товарищ, помни! Абзацы разделяются двойным переводом строки, предложения &ndash; одинарным; предложение должно быть токенизировано.').'</textarea><br/>';
    $out .= '<br/><input type="submit" value="Проверить"/></form>';
    return $out;
}
function split2paragraphs($txt) {
    return preg_split('/\r?\n\r?\n\r?/', $txt);
}
function split2sentences($txt) {
    return preg_split('/[\r\n]+/', $txt);
}
function addtext_check($txt) {
    $out = '<form action="?" method="post" class="inline"><textarea style="display: none" name="txt">'.htmlspecialchars($txt).'</textarea><a href="#" onClick="document.forms[0].submit()">Обратно к форме</a></form><ol type="I">';
    $pars = split2paragraphs($txt);
    foreach ($pars as $par) {
        $out .= '<li><ol>';
        $sents = split2sentences($par);
        foreach ($sents as $sent) {
            $out .= '<li>';
            $tokens = explode(' ', $sent);
            foreach ($tokens as $token) {
                $ex = form_exists($token);
                if ($ex == -1) {
                    $out .= "<span class='check_unpos'>$token</span> ";
                } elseif (!$ex) {
                    $out .= "<span class='check_noword'>$token</span> ";
                } else {
                    $out .= "$token ";
                }
            }
            $out .= '</li>';
        }
        $out .= "</ol></li>\n";
    }
    $out .= '</ol>';
    $out .= '<form action="?act=add" method="post">Добавляем в <select id="book0" name="book[]" onChange="changeSelectBook(0)"><option value="0">-- Не выбрано --</option>'.books_get_select(0).'</select>&nbsp;';
    $out .= '<select id="book1" name="book[]" disabled="disabled" onChange="changeSelectBook(1)"><option value="0">-- Не выбрано --</option></select>';
    $out .= '<br/><p id="lastpar_info">Надо выбрать книгу.</p>';
    $out .= '<textarea style="display: none" name="txt">'.htmlspecialchars($txt).'</textarea>';
    $out .= 'Счёт абзацев &ndash; с <input id="newpar" name="newpar" size="3" maxlength="3" value="1"/>&nbsp;<input id="submitter" type="submit" value="Добавить" disabled="disabled"/></form>';
    return $out;
}
function addtext_add($text, $book_id, $par_num) {
    $revset_id = create_revset();
    if (!$revset_id) return 0;
    $pars = split2paragraphs($text);
    foreach($pars as $par) {
        #adding a paragraph
        if (!sql_query("INSERT INTO `paragraphs` VALUES(NULL, '$book_id', '".($par_num++)."')")) return 0;
        $par_id = sql_insert_id();
        $sent_num = 1;
        $sents = split2sentences($par);
        foreach($sents as $sent) {
            #adding a sentence
            if (!sql_query("INSERT INTO `sentences` VALUES(NULL, '$par_id', '".($sent_num++)."', '0')")) return 0;
            $sent_id = sql_insert_id();
            $token_num = 1;
            #print "new sentence (pos = ".($sent_num++).")<br/>";
            $tokens = explode(' ', $sent);
            foreach ($tokens as $token) {
                #adding a textform
                if (!sql_query("INSERT INTO `text_forms` VALUES(NULL, '$sent_id', '".($token_num++)."', '".mysql_real_escape_string($token)."')")) return 0;
                $tf_id = sql_insert_id();
                #adding a revision
                if (!sql_query("INSERT INTO `tf_revisions` VALUES(NULL, '$revset_id', '$tf_id', '".mysql_real_escape_string(generate_tf_rev($token))."')")) return 0;
            }
        }
    }
    return 1;
}
function generate_tf_rev($token) {
    $out = '<tf_rev text="'.htmlspecialchars($token).'">';
    if (preg_match('/[А-Яа-яЁё]/u', $token)) {
        $res = sql_query("SELECT lemma_id, lemma_text, grammems FROM form2lemma WHERE form_text='$token'");
        if (sql_num_rows($res) > 0) {
            while($r = sql_fetch_array($res)) {
                $out .= '<var><lemma id="'.$r['lemma_id'].'" text="'.$r['lemma_text'].'">'.$r['grammems'].'</lemma></var>';
            }
        } else {
            $out .= '<var><lemma id="0" text="'.htmlspecialchars($token).'"><grm val="UnknownPOS"/></lemma></var>';
        }
    } elseif (preg_match('/[\,\.\:\;\-\(\)\'\"\[\]\?\!\/]/', $token)) {
        $out .= '<var><lemma id="0" text="'.htmlspecialchars($token).'"><grm val="PM"/></lemma></var>';
    } else {
        $out .= '<var><lemma id="0" text="'.htmlspecialchars($token).'"><grm val="UnknownPOS"/></lemma></var>';
    }
    $out .= '</tf_rev>';
    return $out;
}
function dict_block_search_lemma($q) {
    $q = mysql_real_escape_string($q);
    $out = '';
    $res = sql_query("SELECT lemma_id FROM `dict_lemmata` WHERE `lemma_text`='$q'");
    if (sql_num_rows($res) == 0) return "Ничего не найдено.";
    while($r = sql_fetch_array($res)) {
        $out .= '<a href="?act=edit&id='.$r['lemma_id']."\">[".$r['lemma_id']."] $q</a><br/>";
    }
    return $out;
}
function dict_block_search_form($q) {
    $q = mysql_real_escape_string($q);
    $out = '';
    $res = sql_query("SELECT DISTINCT dl.lemma_id, dl.lemma_text FROM `form2lemma` fl LEFT JOIN `dict_lemmata` dl ON (fl.lemma_id=dl.lemma_id) WHERE fl.`form_text`='$q'");
    if (sql_num_rows($res) == 0) return "Ничего не найдено.";
    while($r = sql_fetch_array($res)) {
        $out .= '<a href="?act=edit&id='.$r['lemma_id']."\">[".$r['lemma_id']."] ".$r['lemma_text']."</a><br/>";
    }
    return $out;
}
function add_gramtype($name) {
    $r = sql_fetch_array(sql_query("SELECT MAX(`orderby`) AS `m` FROM `gram_types`"));
    if (sql_query("INSERT INTO `gram_types` VALUES(NULL, '$name', '".($r['m']+1)."')")) {
        header("Location:dict.php?act=gram");
    } else {
        #some error message
    }
}
function add_grammem($name, $group, $aot_id, $descr) {
    if (sql_query("INSERT INTO `gram` VALUES(NULL, '$group', '$aot_id', '$name', '$descr')")) {
        header("Location:dict.php?act=gram");
    } else {
        #some error message
    }
}
function dict_get_select_gramtype() {
    $res = sql_query("SELECT `type_id`, `type_name` FROM `gram_types` ORDER by `type_name`");
    $out = '';
    while($r = sql_fetch_array($res)) {
        $out .= '<option value="'.$r['type_id'].'">'.$r['type_name'].'</option>';
    }
    return $out;
}
function parse_dict_rev($text) {
    $arr = xml2ary($text);
    return $arr['dict_rev']['_c'];
}
function form_exists($f) {
    $f = lc($f);
    if (!preg_match('/[А-Яа-я]/u', $f)) {
        return -1;
    }
    return sql_num_rows(sql_query("SELECT lemma_id FROM form2lemma WHERE form_text='".mysql_real_escape_string($f)."' LIMIT 1"));
}
?>
