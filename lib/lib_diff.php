<?php

/**
    Diff implemented in pure php, written from scratch.
    Copyright (C) 2003  Daniel Unterberger <diff.phpnet@holomind.de>
    Copyright (C) 2005  Nils Knappmeier next version 
    Copyright (C) 2012  Igor Turchenko modify to table rows output
    
    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.
    
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
    
    http://www.gnu.org/licenses/gpl.html

    About:
    I searched a function to compare arrays and the array_diff()
    was not specific enough. It ignores the order of the array-values.
    So I reimplemented the diff-function which is found on unix-systems
    but this you can use directly in your code and adopt for your needs.
    Simply adopt the formatline-function. with the third-parameter of arr_diff()
    you can hide matching lines. Hope someone has use for this.

    Contact: d.u.diff@holomind.de <daniel unterberger>
**/

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
    // split the source text into arrays of lines
    $t1 = explode("\n",$old);
    $x=array_pop($t1); 
    if ($x>'') $t1[]="$x\n";
    $t2 = explode("\n",$new);
    $x=array_pop($t2); 
    if ($x>'') $t2[]="$x\n";

    // build a reverse-index array using the line as key and line number as value
    // don't store blank lines, so they won't be targets of the shortest distance
    // search
    foreach($t1 as $i=>$x) if ($x>'') $r1[$x][]=$i;
    foreach($t2 as $i=>$x) if ($x>'') $r2[$x][]=$i;

    $a1=0; $a2=0;   // start at beginning of each list
    $actions=array();

    // walk this loop until we reach the end of one of the lists
    while ($a1<count($t1) && $a2<count($t2)) {
        // if we have a common element, save it and go to the next
        if ($t1[$a1]==$t2[$a2]) { $actions[]=EQUAL; $a1++; $a2++; continue; } 

        // otherwise, find the shortest move (Manhattan-distance) from the
        // current location
        $best1=count($t1); $best2=count($t2);
        $s1=$a1; $s2=$a2;
        while(($s1+$s2-$a1-$a2) < ($best1+$best2-$a1-$a2)) {
            $d=-1;
            foreach((array)@$r1[$t2[$s2]] as $n) 
                if ($n>=$s1) { $d=$n; break; }
            if ($d>=$s1 && ($d+$s2-$a1-$a2)<($best1+$best2-$a1-$a2)) { $best1=$d; $best2=$s2; }
            $d=-1;
            foreach((array)@$r2[$t1[$s1]] as $n) 
                if ($n>=$s2) { $d=$n; break; }
            if ($d>=$s2 && ($s1+$d-$a1-$a2)<($best1+$best2-$a1-$a2)) { $best1=$s1; $best2=$d; }
            $s1++; $s2++;
        }
        while ($a1<$best1) { $actions[]=DELETED; $a1++; }  // deleted elements
        while ($a2<$best2) { $actions[]=ADDED; $a2++; }  // added elements
    }

    // we've reached the end of one list, now walk to the end of the other
    while($a1<count($t1)) { $actions[]=DELETED; $a1++; }  // deleted elements
    while($a2<count($t2)) { $actions[]=ADDED; $a2++; }  // added elements

    // and this marks our ending point
    $actions[]=8;
      
    // now, let's follow the path we just took and report the added/deleted
    // elements into $out.
    $op = 0;
    $x0=$x1=0; $y0=$y1=0;
    $out_left = array(); // array for left table column
    $out_right = array(); // array for right table column
    foreach($actions as $act) {
        if ($act==DELETED) { $op|=$act; $x1++; continue; }
        if ($act==ADDED) { $op|=$act; $y1++; continue; }
        
        if ($op>0) {
            $xstr = $x1 - $x0; // count of left column line changes
            $ystr = $y1 - $y0; // count of right column line changes

            while ($x0<$x1) { // changes in left column
                if ($op==DELETED || $op==CHANGED) $out_left[] = handle_data($x0, $op, $t1[$x0]); // deleted OR changed
                $x0++; 
            } 

            while ($y0<$y1) { // changes in right column
                if ($op==ADDED || $op==CHANGED) $out_right[] = handle_data($y0, $op, $t2[$y0]); // added OR changed
                $y0++; 
            }

            // fill empty lines
            while ($xstr < $ystr) { $out_left[]  = handle_data(-1,EMPTIED,""); $xstr++; }
            while ($ystr < $xstr) { $out_right[] = handle_data(-1,EMPTIED,""); $ystr++; }
        } 
        
        if ($act==EQUAL) { // no changes, equal lines
            $out_left[]  = handle_data($x0, EQUAL, $t1[$x0]);
            $out_right[] = handle_data($y0, EQUAL, $t2[$y0]);
        }
        
        $x1++; $x0=$x1;
        $y1++; $y0=$y1;
        $op=0;
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

?>
