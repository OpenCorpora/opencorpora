<?php


function get_all_simple_groups() {
    $out = array();
    $res = sql_query_pdo("
        SELECT group_id, group_type, user_id, p.book_id as book_id, token_id, tf_text, head_id, tf.pos
        FROM anaphora_syntax_groups_simple sg
        JOIN anaphora_syntax_groups g USING (group_id)
        JOIN tokens tf ON (sg.token_id = tf.tf_id)
        JOIN sentences s ON (s.sent_id = tf.sent_id)
        JOIN paragraphs p ON (p.par_id = s.par_id)
        ORDER BY group_id, tf.pos
    ");

    $last_r = NULL;
    $token_ids = array();
    $token_texts = array();
    $token_pos = array();

    while ($r = sql_fetch_array($res)) {
        if ($last_r && $r['group_id'] != $last_r['group_id']) {
            $out[] = array(
                'id' => $last_r['group_id'],
                'type' => $last_r['group_type'],
                'user_id' => $last_r['user_id'],
                'tokens' => $token_ids,
                'token_texts' => $token_texts,
                'head_id' => $last_r['head_id'],
                'book_id' => $last_r['book_id'],
                'text' => join(' ', array_values($token_texts)),
                'start_pos' => min($token_pos),
                'end_pos' => max($token_pos)
            );
            $token_ids = $token_texts = $token_pos = array();
        }
        $token_ids[] = $r['token_id'];
        $token_pos[] = $r['pos'];
        $token_texts[$r['token_id']] = $r['tf_text'];
        $last_r = $r;
    }
    if (sizeof($token_ids) > 0) {
        $out[] = array(
            'id' => $last_r['group_id'],
            'type' => $last_r['group_type'],
            'user_id' => $last_r['user_id'],
            'tokens' => $token_ids,
            'token_texts' => $token_texts,
            'head_id' => $last_r['head_id'],
            'book_id' => $last_r['book_id'],
            'text' => join(' ', array_values($token_texts)),
            'start_pos' => min($token_pos)
        );
    }

    return $out;
}

function get_all_simple_groups_by_book($book_id) {
    $out = array();
    $res = sql_query_pdo("
        SELECT group_id, group_type, user_id, token_id, tf_text, head_id, tf.pos
        FROM anaphora_syntax_groups_simple sg
        JOIN anaphora_syntax_groups g USING (group_id)
        JOIN tokens tf ON (sg.token_id = tf.tf_id)
        JOIN sentences s ON (s.sent_id = tf.sent_id)
        JOIN paragraphs p ON (p.par_id = s.par_id)
        WHERE p.book_id = $book_id
        ORDER BY group_id, tf.pos
    ");

    $last_r = NULL;
    $token_ids = array();
    $token_texts = array();
    $token_pos = array();

    while ($r = sql_fetch_array($res)) {
        if ($last_r && $r['group_id'] != $last_r['group_id']) {
            $out[] = array(
                'id' => $last_r['group_id'],
                'type' => $last_r['group_type'],
                'user_id' => $last_r['user_id'],
                'tokens' => $token_ids,
                'token_texts' => $token_texts,
                'head_id' => $last_r['head_id'],
                'text' => join(' ', array_values($token_texts)),
                'start_pos' => min($token_pos),
                'end_pos' => max($token_pos)
            );
            $token_ids = $token_texts = $token_pos = array();
        }
        $token_ids[] = $r['token_id'];
        $token_pos[] = $r['pos'];
        $token_texts[$r['token_id']] = $r['tf_text'];
        $last_r = $r;
    }
    if (sizeof($token_ids) > 0) {
        $out[] = array(
            'id' => $last_r['group_id'],
            'type' => $last_r['group_type'],
            'user_id' => $last_r['user_id'],
            'tokens' => $token_ids,
            'token_texts' => $token_texts,
            'head_id' => $last_r['head_id'],
            'text' => join(' ', array_values($token_texts)),
            'start_pos' => min($token_pos)
        );
    }

    return $out;
}

function filter_groups($simple_groups, $uid, $SIMPLE_TYPES) {
   $groups_user = array_filter($simple_groups, function($group) use ($uid, $SIMPLE_TYPES) {
            return ($group['user_id'] == $uid) && in_array($group['type'], $SIMPLE_TYPES);
         });

   foreach ($groups_user as $i => &$group) {
      $tokens = get_group_tokens($group['id']);
      sort($tokens);
      $tokens = join(" ", $tokens);
      $group = array(
         'type' => $group['type'],
         'tokens' => $tokens
      );
   }

   return $groups_user;
}

function count_matching_groups($groups_user1, $groups_user2, $type1, $type2) {
   $cnt = 0;
   foreach ($groups_user1 as $group1) {
      if ($group1['type'] !== $type1) continue;
      foreach ($groups_user2 as $group2) {
         if ($group2['type'] !== $type2) continue;
         if ($group1['tokens'] === $group2['tokens']) $cnt++;
      }
   }

   return $cnt;
}

function count_matching_nones1($groups_user1, $groups_user2, $type1) {
   $cnt = 0;
   foreach ($groups_user1 as $group1) {
      if ($group1['type'] !== $type1) continue;

      $_cnt = 0;
      foreach ($groups_user2 as $group2) {
         if ($group1['tokens'] === $group2['tokens']) $_cnt++;
      }

      if ($_cnt === 0) $cnt++;
   }

   return $cnt;
}

function kappa_pairwise($categories, $agreement_matrix, $verbose = FALSE) {

   $horiz_totals = $vert_totals = array_fill(0, count($categories), 0);

   $observed_match = 0;

   foreach ($categories as $i => $role1) {
      foreach ($categories as $j => $role2) {
         $horiz_totals[$i] += $agreement_matrix[$role1][$role2];
         $vert_totals[$j] += $agreement_matrix[$role1][$role2];
         if ($role1 === $role2) {
            $observed_match += $agreement_matrix[$role1][$role2];
         }
      }
   }

   // print "Observed match $observed_match, overall ".array_sum($vert_totals).PHP_EOL;

   $observed_agr = $observed_match / array_sum($vert_totals);

   $sum = 0;
   for ($i = 0; $i < count($vert_totals); $i++) {
      $sum += $vert_totals[$i] * $horiz_totals[$i];
   }

   $agr_by_chance = $sum / pow(array_sum($vert_totals), 2);

   $k = ($observed_agr - $agr_by_chance) / (1 - $agr_by_chance);

   if (!$verbose)
      return $k;
   else
      return array(
         'kappa' => $k,
         'observed_agreement' => $observed_agr,
         'observed_match'=> $observed_match,
         'chance_agreement'=> $agr_by_chance,
         // 'horiz_totals'=> $horiz_totals,
         // 'vert_totals'=> $vert_totals
      );
}

function intersect($groups_user1, $groups_user2) {
   $cnt = 0;
   foreach ($groups_user1 as $group1) {
      foreach ($groups_user2 as $group2) {
         if ($group1['tokens'] === $group2['tokens']) $cnt++;
      }
   }

   return $cnt;
}

function print_matrix($matrix) {
   print("_____|_");
   foreach ($matrix as $id => $vals) {
      printf("%-'_4s_|_", $id);
   }
   print PHP_EOL;
   foreach ($matrix as $id => $vals) {
      printf("%-4s | ", $id);
      foreach ($vals as $id2 => $value) {
         printf("%-4s | ", $value);
      }
      print PHP_EOL;
   }

}

function sorted($array) {
  sort($array);
  return $array;
}

function simplify($group) {
  return array(
    'type' => $group['type'],
    'tokens' => join(' ', sorted(get_group_tokens($group['id'])))
    );
}

function kappa_sorter($k1, $k2, $f) {
   if ($k1[$f] > $k2[$f]) return 1;
   elseif ($k1[$f] < $k2[$f]) return -1;
   return 0;
}

function kappa_sorter_val($k1, $k2) {
    return kappa_sorter($k1, $k2, 'kappa');
}

function kappa_sorter_size($k1, $k2) {
    return kappa_sorter($k1, $k2, 'intersect');
}

function kappa_to_string($k) {
    return sprintf("%.4f | %d @%s: %d, @%s: %d [%s]\n", $k['kappa'],
        $k['intersect'], $k['user1'], $k['u1count'],
        $k['user2'], $k['u2count'], $k['book_title']);
}

function kappa_stats_with_filter($kappas, $filter) {
	$k = array_values(array_filter($kappas, $filter));

	if (count($k) === 0)
		return FALSE;

	$min = $k[0];
	$max = $k[0];
	$_s  = 0.0;

	foreach ($k as $_k) {
		if ($_k['kappa'] < $min['kappa'])
			$min = $_k;

		else if ($_k['kappa'] > $max['kappa'])
			$max = $_k;

		$_s += $_k['kappa'];
	}

	return array($min, $max, $_s / count($k));
}

