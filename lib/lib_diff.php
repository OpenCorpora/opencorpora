<?php

/**
    Paul's Simple Diff Algorithm v 0.1
    (C) Paul Butler 2007 <http://www.paulbutler.org/>
    May be used and distributed under the zlib/libpng license.
    
    (C) Igor Turchenko 2013
    Modify code for the needs of OpenCorpora project    
    
    // --------------------------------
    
    Original code has been obtained from https://github.com/paulgb/simplediff/blob/master/php/simplediff.php
*/

DEFINE ("EMPTIED", 0);
DEFINE ("DELETED", 1);
DEFINE ("ADDED", 2);
DEFINE ("CHANGED", DELETED | ADDED);
DEFINE ("EQUAL", 4);

/**
 *  Generate colorized (by clients CSS) table rows like popular file-merge programms. 
 *  @var: $old (string) after htmlspecialchars() function. Old version to compare
 *  @var: $new (string) after htmlspecialchars() function. New version to compare
 *
 *  @return: array[old_info, new_info], where old_info and new_info is array of lines array [line_number, action_code, text]
 */
function php_diff($old,$new) 
{
    $diff = diff(explode(PHP_EOL, $old), explode(PHP_EOL, $new));
    
    $x=0; $y=0; // line counters
    $out_left = array(); // array for left table column
    $out_right = array(); // array for right table column
    
    foreach($diff as $k){
        if(is_array($k)) {    // have difference
            $action = 0;
            if (!empty($k['d'])) $action |= DELETED;
            if (!empty($k['i'])) $action |= ADDED;
            
            if (!empty($k['d'])) foreach($k['d'] as $str) {
                $out_left[] = handle_data($x++, $action, $str);
            }
            if (!empty($k['i'])) foreach($k['i'] as $str) {
                $out_right[] = handle_data($y++, $action, $str); 
            }
            
            // equalize arrays length
            while (count($out_left) < count($out_right)) { $out_left[]  = handle_data(-1,EMPTIED,""); }
            while (count($out_right) < count($out_left)) { $out_right[] = handle_data(-1,EMPTIED,""); }
        } else {  // no difference            
            $out_left[]  = handle_data($x++, EQUAL, $k);
            $out_right[] = handle_data($y++, EQUAL, $k);
        }
    }
    
    $out = array();
    $out[] = $out_left;
    $out[] = $out_right;
    
    return $out;
}

function handle_data($line_number, $action_code, $text) {
    $result = array();
    
    $result[] = $line_number;
    $result[] = $action_code;
    $result[] = $text;
    
    return $result;
}

function get_td_class($action_code) {
    switch ($action_code) {
        case EMPTIED: return "diffe";
        case DELETED: return "diffd";
        case ADDED:   return "diffa";
        case CHANGED: return "diffc";
        case EQUAL:   return"diff";
    }
    
    return ""; // some default value
}

/** Original diff function by Paul Butler */
function diff($old, $new){
    $matrix = array();
    $maxlen = 0;
    foreach($old as $oindex => $ovalue){
        $nkeys = array_keys($new, $ovalue);
        foreach($nkeys as $nindex){
            $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
            if($matrix[$oindex][$nindex] > $maxlen){
                $maxlen = $matrix[$oindex][$nindex];
                $omax = $oindex + 1 - $maxlen;
                $nmax = $nindex + 1 - $maxlen;
            }
        }    
    }
    if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
    return array_merge(
        diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
        array_slice($new, $nmax, $maxlen),
        diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
}

// substitutes array_diff, this returns real difference
function arr_diff($a1, $a2) {
    $diff = array();
    foreach ($a1 as $k => $v) {
        $dv = array();
        if (is_int($k)) {
            // Compare values
            if (array_search($v, $a2) === false) $dv = $v;
            else if (is_array($v)) $dv = arr_diff($v, $a2[$k]);
            if ($dv) $diff[] = $dv;
        }
        else {
        // Compare noninteger keys
            if (!$a2[$k]) $dv = $v;
            else if (is_array($v)) $dv = arr_diff($v, $a2[$k]);
            if ($dv) $diff[$k] = $dv;
        }
    }
    return $diff;
}
?>
