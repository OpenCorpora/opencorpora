<?php
// set_include_path(".:/Users/alex/pear/share/pear:/Users/alex/Code/php/smarty/libs");

// register_shutdown_function(function() { debug_print_backtrace(); });

require_once('lib/header.php');
require_once('lib/lib_syntax.php');
require_once('lib/lib_users.php');
require_once('kappa_fn.php');

$books = get_books_with_syntax();

$simple_groups = get_all_simple_groups();
$types = get_syntax_group_types();

$SIMPLE_TYPES = array(1, 6, 2, 3, 5, 15, 4, 7);
$COMPLEX_TYPES = array(13, 14, 9, 12, 11, 10, 8);

$KAPPA_BEST = array();
$matrixes = array();

foreach ($books['books'] as $book) {
   $simple_groups = get_all_simple_groups_by_book($book['id']);
   if (count($simple_groups) === 0) continue;
   $users = array();

    if ($book['syntax_moder_id'] === 0) {
      $mod = "none";
   }
   else {
      $mod = get_user_shown_name($book['syntax_moder_id']);
   }

   foreach ($simple_groups as $gr) {
      $users[$gr['user_id']]++;
      $USERS[] = $gr['user_id'];
      $USERS = array_values(array_unique($USERS));
   }

   if (count($users) < 2) continue;

   // print "{$book['name']}, annotators: ".count($users).PHP_EOL;

   foreach (array_keys($users) as $index => $uid) {
      foreach (array_slice(array_keys($users), $index + 1) as $uid2) {
         // all possible combinations of two users
         $u1 = get_user_shown_name($uid);
         $u2 = get_user_shown_name($uid2);
         // print "$u1 $u2\n";

         $complex_groups1 = array_map('simplify', get_complex_groups_by_simple($simple_groups, $uid));
         $complex_groups2 = array_map('simplify', get_complex_groups_by_simple($simple_groups, $uid2));

         if (count($complex_groups1) === 0 or count($complex_groups2) === 0) continue;
         // print count($complex_groups1)." ".count($complex_groups2)." ".intersect($complex_groups1, $complex_groups2).PHP_EOL;

         // print count($groups_user1)." ".count($groups_user2)." ".intersect($groups_user1, $groups_user2).PHP_EOL;
         $matrix = array();

         $matrix['NONE']['NONE'] = 0;
         foreach ($COMPLEX_TYPES as $id1) {
            foreach ($COMPLEX_TYPES as $id2) {
               $matrix[$id1][$id2] = count_matching_groups($complex_groups1, $complex_groups2, $id1, $id2);
            }

            $matrix[$id1]['NONE'] = count_matching_nones1($complex_groups1, $complex_groups2, $id1);
            $matrix['NONE'][$id1] = count_matching_nones1($complex_groups2, $complex_groups1, $id1);
         }

         $k = kappa_pairwise(array_merge($COMPLEX_TYPES, array('NONE')), $matrix, TRUE);

         $matrixes[$k['kappa']] = $matrix;

         $kappas[] = array_merge($k, array(
            'user1' => $u1,
            'user2' => $u2,
            'book_id' => $book['id'],
            'book_title' => $book['name'],
            'u1count' => count($complex_groups1),
            'u2count' => count($complex_groups2),
            'with_m' => ($u1 === $mod or $u2 === $mod),
            'intersect' => intersect($complex_groups1, $complex_groups2)));
         // print PHP_EOL;
      }
   }
}

print "<h1>Cohen's kappa, complex groups</h1>";
print "<h2>All annotators and moderator</h2>";

foreach ($USERS as $user1) {
   $user1 = get_user_shown_name($user1);

  print "{$user1} with mod <br />";
  $stats = kappa_stats_with_filter($kappas, function($e) use ($user1) {
        return ($e['user1'] == $user1 && $e['with_m']) or
           ($e['user2'] == $user1 && $e['with_m']);
     });

  if ($stats) {
     list($min, $max, $avg) = $stats;
     print sprintf("%.4f (%s, %s) [%.4f] %.4f (%s, %s)<br />", $min['kappa'], $min['user1'], $min['user2'], $avg, $max['kappa'], $max['user1'], $max['user2']);
     print '<br />';
  }
}

print "<h2>All pairs of annotators, without moderator</h2>";

foreach ($USERS as $user1) {
  $user1 = get_user_shown_name($user1);
  foreach ($USERS as $user2) {
    $user2 = get_user_shown_name($user2);

    if ($user1 === $user2)
      continue;

     $stats = kappa_stats_with_filter($kappas, function($e) use ($user1, $user2) {
           return ($e['user1'] === $user1 && $e['user2'] === $user2 && !$e['with_m']) or
              ($e['user2'] === $user1 && $e['user1'] === $user2 && !$e['with_m']);
        });

     if ($stats) {
        print "{$user1} vs {$user2}</br>";

        list($min, $max, $avg) = $stats;
        print sprintf("%.4f (%s, %s) [%.4f] %.4f (%s, %s)<br />", $min['kappa'], $min['user1'], $min['user2'], $avg, $max['kappa'], $max['user1'], $max['user2']);
        print '<br />';
     }
}
}

print '<h2>All books (without moderator, >=2 annotators)</h2>';

foreach ($books['books'] as $book) {
  $stats = kappa_stats_with_filter($kappas, function($e) use ($book) {
        return !$e['with_m'] && $e['book_title'] === $book['name'];
     });

  if ($stats) {
     print "{$book['name']}<br />";
     list($min, $max, $avg) = $stats;
     print sprintf("%.4f (%s, %s) [%.4f] %.4f (%s, %s)<br />", $min['kappa'], $min['user1'], $min['user2'], $avg, $max['kappa'], $max['user1'], $max['user2']);
     print '<br />';
  }

}