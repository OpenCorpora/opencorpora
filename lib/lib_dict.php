<?php
require_once('lib_xml.php');
function dict_page() {
    $r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_gt FROM `gram_types`"));
	$cnt_gt = $r['cnt_gt'];
	$r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_g FROM `gram`"));
	$cnt_g = $r['cnt_g'];
	$r = sql_fetch_array(sql_query("SELECT COUNT(*) AS cnt_l FROM `dict_lemmata`"));
	$cnt_l = $r['cnt_l'];
    $out = sprintf("<p>Всего %d граммем в %d группах и %d лемм.</p>", $cnt_g, $cnt_gt, $r['cnt_l']);
    $out .= '<p><a href="?act=gram">Редактор граммем</a><br/>';
	$out .= '<a href="?act=lemmata">Редактор лемм</a></p>';
    return $out;
}
function dict_page_gram() {
    $out = '<h2>Группы граммем</h2>';
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
    $out = '<h2>Редактор морфологического словаря</h2>';
    $out .= "<form action='?act=lemmata' method='post'>Поиск леммы: <input name='search_lemma' size='25' maxlength='40' value='".(isset($_POST['search_lemma'])?htmlspecialchars($_POST['search_lemma']):'')."'/> <input type='submit' value='Искать'/></form>";
    if (isset($_POST['search_lemma'])) {
        $out .= dict_block_search_lemma($_POST['search_lemma']);
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
    //print ('<pre>');
    //print_r($arr);
    //print ('</pre>');
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
?>
