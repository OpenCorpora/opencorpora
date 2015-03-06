<?php
require_once('../lib/header_ajax.php');

$result['cats'] = array('geo' => array(), 'topic' => array());
if (isset($_POST['cat'])) {
    $categ = explode('|', $_POST['cat']);
    foreach ($categ as $cat) {
        $cat = str_replace('Категория:', '', $cat);
        if (in_array($cat, array('Опубликовано'))) {
            continue;
        }
        if (preg_match('/^(\d+) (\S+) (\d\d\d\d)$/', $cat, $matches)) {
            $result['cats']['date'] = sprintf("%02s", $matches[1]).'/'.month_to_number($matches[2]);
            $result['cats']['year'] = $matches[3];
        }
        else {
            if (check_for_geo($cat)) {
                $result['cats']['geo'][] = $cat;
            }
            else {
                $result['cats']['topic'][] = $cat;
            }
        }
    }
}

function check_for_geo($s) {
    $res = sql_query("SELECT tag_name FROM book_tags WHERE tag_name = ? LIMIT 1", array('Гео:ВикиКатегория:'.$s));
    return sizeof($res);
}
function month_to_number($s) {
    $months = array(
        'января' => '01',
        'февраля' => '02',
        'марта' => '03',
        'апреля' => '04',
        'мая' => '05',
        'июня' => '06',
        'июля' => '07',
        'августа' => '08',
        'cентября' => '09', //latin 'c'
        'сентября' => '09', //cyrillic 'с'
        'октября' => '10',
        'ноября' => '11',
        'декабря' => '12'
    );
    return $months[$s];
}

log_timing(true);
die(json_encode($result));
