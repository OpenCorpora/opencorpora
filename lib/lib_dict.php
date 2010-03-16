<?php
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
?>
