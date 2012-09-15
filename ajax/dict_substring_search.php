<?php
header('Content-type: text/html; charset=utf-8');
require_once('../lib/header.php');

$substring = mysql_real_escape_string($_GET['q']);
$res = sql_query("SELECT DISTINCT dl.lemma_id, dl.lemma_text,SUBSTR(grammems, 7, 4) AS gr FROM `form2lemma` fl LEFT JOIN `dict_lemmata` dl ON (fl.lemma_id=dl.lemma_id) WHERE fl.`form_text` like '$substring%' order by dl.lemma_text limit 10");
var_dump($substring);
var_dump(mysql_num_rows($res));
while($line = sql_fetch_assoc($res)) {
   echo $line['lemma_text'] . '|' . $line['gr'] . '|' . $line['lemma_id'] ."\n";
}
?>
