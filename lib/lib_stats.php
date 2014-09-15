<?php
require_once('constants.php');

function get_common_stats() {
    global $config;
    $stats = array();

    $res = sql_query("SELECT * FROM stats_param WHERE is_active=1 AND param_id NOT IN(SELECT DISTINCT param_id FROM user_stats)");
    $res1 = sql_prepare("SELECT param_value FROM stats_values WHERE param_id=? ORDER BY `timestamp` DESC LIMIT 1");
    while ($r = sql_fetch_array($res)) {
        sql_execute($res1, array($r['param_id']));
        $arr = sql_fetch_array($res1);
        $stats[$r['param_name']] = array('value' => $arr['param_value']);
    }

    foreach (array('total', 'chaskor', 'chaskor_news', 'wikipedia', 'wikinews', 'blogs', 'fiction', 'nonfiction', 'law', 'misc') as $src) {
        if (isset($config['goals'][$src.'_words'])) {
            $stats['goals'][$src.'_words'] = $config['goals'][$src.'_words'];
            $stats['percent_words'][$src] = floor($stats[$src.'_words']['value'] / $config['goals'][$src.'_words'] * 100);
        }
        else {
            $stats['goals'][$src.'_words'] = 0;
            $stats['percent_words'][$src] = 0;
        }
    }

    return $stats;
}
function get_sentence_adders_stats($last_week=false, $team=0) {
    if ($last_week)
        $param = 7;
    else
        $param = 6;

    $out = array();
    $res = sql_pe("
        SELECT user_shown_name AS user_name, param_value
        FROM user_stats
        LEFT JOIN users USING (user_id)
        WHERE param_id=$param
        ".($team ? "AND user_team = ?" : "")."
        ORDER BY param_value DESC
    ", $team ? array($team) : array());
    foreach ($res as $r) {
        $out[] = array('user_name' => $r['user_name'], 'value' => $r['param_value']);
    }
    return $out;
}
function get_word_stats_for_chart() {
    $chart = array();
    $t = array();
    $tchart = array();
    $time = time();

    $param_set = array(53, 49, 57, 32, 27, 23, 19, 15, 11);

    $res = sql_prepare("SELECT timestamp, param_value FROM stats_values WHERE timestamp > ? AND param_id = ? ORDER BY timestamp");
    foreach ($param_set as $param_id) {
        sql_execute($res, array($time - 90 * SEC_PER_DAY, $param_id));
        while ($r = sql_fetch_array($res)) {
            $day = intval($r['timestamp'] / SEC_PER_DAY);
            $t[$day][$param_id] = $r['param_value'];
        }
    }
    ksort($t);

    // we need two cycles for cases when a parameter was measured more than once a day
    // we suppose that all parameters were measured simultaneously

    foreach ($t as $day => $ar) {
        $sum = 0;
        foreach ($param_set as $param_id) {
            if (isset($ar[$param_id]))
                $sum += $ar[$param_id];
            $tchart[$param_id][] = '['.($day * MSEC_PER_DAY).','.$sum.']';
        }
    }

    $chart['chaskor_words'] = join(',', $tchart[11]);
    $chart['wikinews_words'] = join(',', $tchart[15]);
    $chart['wikipedia_words'] = join(',', $tchart[19]);
    $chart['blogs_words'] = join(',', $tchart[23]);
    $chart['chaskor_news_words'] = join(',', $tchart[27]);
    $chart['fiction_words'] = join(',', $tchart[32]);
    $chart['nonfiction_words'] = join(',', $tchart[57]);
    $chart['law_words'] = join(',', $tchart[49]);
    $chart['misc_words'] = join(',', $tchart[53]);

    return $chart;
}
function get_ambiguity_stats_for_chart() {
    $chart = array();
    $t = array();
    $tchart=  array();
    $time = time();
    
    $param_set = array(5, 35, 36, 37, 41, 45);

    $res = sql_prepare("SELECT timestamp, param_value FROM stats_values WHERE timestamp > ? AND param_id = ? ORDER BY timestamp");
    foreach ($param_set as $param_id) {
        sql_execute($res, array($time - (90 * SEC_PER_DAY), $param_id));
        while ($r = sql_fetch_array($res)) {
            $day = intval($r['timestamp'] / SEC_PER_DAY);
            $t[$day][$param_id] = $r['param_value'];
        }
    }
    ksort($t);

    foreach ($t as $day => $ar) {
        $tchart['disamb_sentences'][] = '['.($day * MSEC_PER_DAY).','.$ar[41].']';
        if ($ar[45])
            $tchart['disamb_sent_length'][] = '['.($day * MSEC_PER_DAY).','.sprintf("%.3f", $ar[45] / $ar[41]).']';
        if ($ar[35] == 0)
            continue;
        $tchart['avg_parses'][] = '['.($day * MSEC_PER_DAY).','.sprintf("%.3f", $ar[35] / $ar[5]).']';
        $tchart['non_ambig'][] = '['.($day * MSEC_PER_DAY).','.sprintf("%.3f", $ar[37] / $ar[5] * 100).']';
        $tchart['unknown'][] = '['.($day * MSEC_PER_DAY).','.sprintf("%.3f", $ar[36] / $ar[5] * 100).']';
        $tchart['total_words'][] = '['.($day * MSEC_PER_DAY).','.$ar[5].']';
    }

    foreach ($tchart as $name => $ar) {
        $chart[$name] = join(',', $ar);
    }

    return $chart;
}
function get_pools_stats() {
    $stats = array();
    $total = 0;
    $plan = 1333000;

    $res = sql_query("
        SELECT COUNT(sample_id) cnt, status
        FROM morph_annot_samples
        LEFT JOIN morph_annot_pools p
        USING (pool_id)
        GROUP BY status
    ");
    
    while ($r = sql_fetch_array($res)) {
        $stats[$r['status']] = $r['cnt'];
        $total += $r['cnt'];
    }

    $stats[MA_POOLS_STATUS_NOT_STARTED] += ($plan - $total);
    
    return $stats;
}
function get_annot_stats_for_chart() {
    $stats = array();

    $res = sql_query("
        SELECT
            FLOOR(timestamp / ".SEC_PER_DAY.") * ".SEC_PER_DAY." AS day,
            COUNT(DISTINCT user_id) AS users,
            COUNT(sample_id) AS samples
        FROM morph_annot_click_log
        WHERE clck_type < 10
        AND FLOOR(timestamp / ".SEC_PER_DAY.") > FLOOR(UNIX_TIMESTAMP() / ".SEC_PER_DAY.") - 30
        GROUP BY FLOOR(timestamp / ".SEC_PER_DAY.")
    ");
    
    while ($r = sql_fetch_array($res)) {
        $stats['users'][] = '['.($r['day'] * 1000).','.$r['users'].']';
        $stats['samples'][] = '['.($r['day'] * 1000).','.$r['samples'].']';
    }

    return array(
        'users' => join(',', $stats['users']),
        'samples' => join(',', $stats['samples'])
    );
}
function get_tag_stats() {
    $out = array();
    $res = sql_query("SELECT prefix, value, texts, words FROM tag_stats ORDER BY prefix, texts DESC, words DESC");
    
    while ($r = sql_fetch_array($res)) {
        $out[$r['prefix']][] = array('value' => $r['value'], 'texts' => $r['texts'], 'words' => $r['words']);
    }
    return $out;
}
function get_user_stats($weekly=false, $team=0) {
    if ($weekly) {
        $start_time = time() - (7 * SEC_PER_DAY);
        $counter_param = 58;
        $params = array(59, 60, 61);
    } else {
        $start_time = 0;
        $counter_param = 33;
        $params = array(34, 38, 39);
    }
    
    $annotators = array();
    // team info
    $uid2team = array();
    $res = sql_query("SELECT user_id, user_team FROM users WHERE user_team > 0");
    while ($r = sql_fetch_array($res))
        $uid2team[$r['user_id']] = $r['user_team'];
    $teams = get_team_list();
    foreach ($teams as $i => $v) {
        if ($v['num_users'] == 0) {
            unset($teams[$i]);
            continue;
        }
        $teams[$i]['total'] = $teams[$i]['moderated'] = $teams[$i]['correct'] = $teams[$i]['active_users'] = 0;
    }

    $uid2sid = array();
    $res = sql_pe("
        SELECT user_id, COUNT(*) AS cnt, FLOOR(user_rating10 / 10) AS rating
        FROM morph_annot_instances
        LEFT JOIN users USING(user_id)
        WHERE answer > 0
            AND ts_finish > ?
            ".($team ? "AND user_team = ?" : "")."
        GROUP BY user_id
        ORDER BY ".($weekly ? 'cnt' : 'rating')." DESC
    ", $team ? array($start_time, $team) : array($start_time));
    foreach ($res as $r) {
        $annotators[] = array(
            'total' => number_format($r['cnt'], 0, '', ' '),
            'user_id' => $r['user_id'],
            'rating' => number_format($r['rating'], 0, '', ' ')
        );
        $uid2sid[$r['user_id']] = sizeof($annotators) - 1;
        if (isset($uid2team[$r['user_id']])) {
            $teams[$uid2team[$r['user_id']]]['total'] += $r['cnt'];
            $teams[$uid2team[$r['user_id']]]['active_users'] += 1;
        }
    }

    uasort($teams, function($a, $b) {
        if ($a['total'] > $b['total'])
            return -1;
        if ($a['total'] < $b['total'])
            return 1;
        return 0;
    });

    // last activity info
    $last_click = array();
    $res = sql_query("
        SELECT user_id, MAX(timestamp) AS last_time
        FROM morph_annot_click_log
        WHERE timestamp > UNIX_TIMESTAMP(NOW()) - ".SEC_PER_DAY." * 60
        GROUP BY user_id
    ");
    while ($r = sql_fetch_array($res)) {
        $last_click[$r['user_id']] = $r['last_time'];
    }

    // divergence and moderation info
    $divergence = array();
    $moderated = array();
    $correct = array();

    $res = sql_query("SELECT user_id, param_id, param_value FROM user_stats WHERE param_id IN (".join(', ', $params).")");
    while ($r = sql_fetch_array($res)) {
        switch ($r['param_id']) {
            case 34:
            case 59:
                $divergence[$r['user_id']] = $r['param_value'];
                break;
            case 38:
            case 60:
                $moderated[$r['user_id']] = $r['param_value'];
                if (isset($uid2team[$r['user_id']]))
                    $teams[$uid2team[$r['user_id']]]['moderated'] += $r['param_value'];
                break;
            case 39:
            case 61:
                $correct[$r['user_id']] = $r['param_value'];
                if (isset($uid2team[$r['user_id']]))
                    $teams[$uid2team[$r['user_id']]]['correct'] += $r['param_value'];
        }
    }

    foreach ($teams as $i => $v) {
        if ($v['total'] == 0) {
            unset($teams[$i]);
            continue;
        }
        if ($v['moderated'])
            $teams[$i]['error_rate'] = get_error_rate($v['moderated'], $v['correct']);
        else
            $teams[$i]['error_rate'] = 0;
    }

    $res = sql_query("SELECT u.user_id, u.user_shown_name AS user_name, param_value FROM user_stats s LEFT JOIN users u ON (s.user_id=u.user_id) WHERE param_id=$counter_param ORDER BY param_value DESC");
    while ($r = sql_fetch_array($res)) {
        $t = array(
            'user_id' => $r['user_id'],
            'user_name' => $r['user_name'],
            'value' => number_format($r['param_value'], 0, '', ' '),
            'divergence' => $divergence[$r['user_id']] / $r['param_value'] * 100,
            'last_active' => isset($last_click[$r['user_id']]) ? $last_click[$r['user_id']] : 0,
            'moderated' => isset($moderated[$r['user_id']]) ? $moderated[$r['user_id']] : 0,
            'error_rate' => isset($correct[$r['user_id']]) ? get_error_rate($moderated[$r['user_id']], $correct[$r['user_id']]) : 0
        );
        if (isset($uid2team[$r['user_id']])) {
            $t['team_id'] = $uid2team[$r['user_id']];
            $t['team_name'] = $teams[$t['team_id']]['name'];
        }
        if (isset($uid2sid[$r['user_id']]))
            $annotators[$uid2sid[$r['user_id']]]['fin'] = $t;
    }

    foreach ($annotators as $k => $v) {
        if (!isset($v['fin']['user_name'])) {
            $annotators[$k]['fin']['user_id'] = $v['user_id'];
            $annotators[$k]['fin']['user_name'] = get_user_shown_name($v['user_id']);
            $annotators[$k]['fin']['last_active'] = isset($last_click[$v['user_id']]) ? $last_click[$v['user_id']] : 0;
            $annotators[$k]['fin']['moderated'] = isset($moderated[$v['user_id']]) ? $moderated[$v['user_id']] : 0;
            $annotators[$k]['fin']['error_rate'] = isset($moderated[$v['user_id']]) ? get_error_rate($moderated[$v['user_id']], $correct[$v['user_id']]) : 0;
        }
    }

    $timestamp_yesterday = ($timestamp_today = mktime(0, 0, 0)) - SEC_PER_DAY;

    return array(
        'annotators' => $annotators,
        'teams' => $teams,
        'timestamp_today' => $timestamp_today,
        'timestamp_yesterday' => $timestamp_yesterday,
        'added_sentences' => get_sentence_adders_stats($weekly, $team)
    );
}
function get_error_rate($num_moderated, $num_correct) {
    return $num_moderated == 0 ? 0 : (1 - $num_correct / $num_moderated) * 100;
}
function get_extended_pools_stats() {
    $status_text = array(
        MA_POOLS_STATUS_NOT_STARTED => 'Не опубликованы',
        MA_POOLS_STATUS_IN_PROGRESS => 'Размечаются',
        MA_POOLS_STATUS_ANSWERED => 'Размечены',
        MA_POOLS_STATUS_MODERATED => 'На модерации',
        MA_POOLS_STATUS_ARCHIVED => 'Готовы'
    );

    $total = array();
    $total_by_status = array();
    $res = sql_query("
        SELECT status, pool_type, COUNT(s.sample_id) AS cnt
        FROM morph_annot_samples s
        LEFT JOIN morph_annot_pools USING (pool_id)
        GROUP BY status, pool_type
        ORDER BY status, pool_type
    ");
    $t = array();
    while ($r = sql_fetch_array($res)) {
        if (in_array($r['status'], array(MA_POOLS_STATUS_MODERATION, MA_POOLS_STATUS_TO_MERGE, MA_POOLS_STATUS_MERGING)))
            $r['status'] = MA_POOLS_STATUS_MODERATED;
        if (!isset($t[$r['status']][$r['pool_type']]))
            $t[$r['status']][$r['pool_type']] = 0;
        $t[$r['status']][$r['pool_type']] += $r['cnt'];
        if (!isset($total[$r['pool_type']]))
            $total[$r['pool_type']] = 0;
        $total[$r['pool_type']] += $r['cnt'];

        if (!isset($total_by_status[$r['status']]))
            $total_by_status[$r['status']] = 0;
        $total_by_status[$r['status']] += $r['cnt'];
    }

    // sort in ascending order (and renumber)
    asort($total);
    $new_order = array_flip(array_keys($total));

    $ticks = array();
    $res = sql_query("SELECT type_id, grammemes FROM morph_annot_pool_types ORDER BY type_id");
    $max_type_id = 0;
    while ($r = sql_fetch_array($res)) {
        if (isset($new_order[$r['type_id']]))
            $ticks[] = sprintf("[%d, '%s']", $new_order[$r['type_id']], $r['grammemes']);
        $max_type_id = $r['type_id'];
    }

    // add zeros for correct look
    $tt = array();
    $tt2 = array();
    foreach ($t as $status => $data) {
        for ($i = 1; $i <= $max_type_id; ++$i) {
            if (isset($data[$i])) {
                $tt[$status][] = sprintf("[%d, %d]", $data[$i], $new_order[$i]);
                $tt2[$status][] = sprintf("[%.3f, %d]", $data[$i] / $total[$i], $new_order[$i]);
            }
            else {
                $tt[$status][] = sprintf("[%d, %d]", 0, isset($new_order[$i]) ? $new_order[$i] : 0);
                $tt2[$status][] = sprintf("[%d, %d]", 0, isset($new_order[$i]) ? $new_order[$i] : 0);
            }
        }
    }

    $out = array();
    $out2 = array();
    ksort($tt);
    ksort($tt2);
    foreach ($tt as $status => $data) {
        $out[] = '{ label: "'.$status_text[$status].'", data: [' . join(', ', $data) . '] }';
        $out2[] = '{ label: "'.$status_text[$status].'", data: [' . join(', ', $tt2[$status]) . '] }';
    }

    return array(
        'data' => '[' . join(",\n    ", $out) . ']',
        'data2' => '[' . join(",\n    ", $out2) . ']',
        'ticks' => '[' . join(', ', $ticks) . ']',
        'total' => $total_by_status
    );
}
function get_moderation_stats() {
    $res = sql_query("
        SELECT moderator_id, pool_type, grammemes, status, has_focus, COUNT(pool_id) AS cnt, u.user_shown_name AS username
        FROM morph_annot_pools p
        LEFT JOIN morph_annot_pool_types t
        ON (p.pool_type = t.type_id)
        LEFT JOIN users u
        ON (p.moderator_id = u.user_id)
        WHERE status >= ".MA_POOLS_STATUS_ANSWERED."
        GROUP BY pool_type, moderator_id, status
        ORDER BY moderator_id, pool_type, status
    ");
    $t = array();
    $type2name = array();
    $mod2name = array();
    $mod_total = array('total' => array('total' => 0));

    while ($r = sql_fetch_array($res)) {
        $user_id = $r['moderator_id'];
        $type = $r['pool_type'];
        $status = $r['status'];

        $t[$user_id][$type][$status] = $r['cnt'];
        $type2name[$type] = array($r['grammemes'], 0, $r['has_focus']);
        if ($user_id > 0) {
            if (!isset($t['total'][$type][$status]))
                $t['total'][$type][$status] = 0;
            $t['total'][$type][$status] += $r['cnt'];
            $mod2name[$user_id] = $r['username'];
            if (!isset($mod_total[$user_id]))
                $mod_total[$user_id] = array('total' => 0);
            if (!isset($mod_total[$user_id][$type]))
                $mod_total[$user_id][$type] = 0;
            if (!isset($mod_total['total'][$type]))
                $mod_total['total'][$type] = 0;
            $mod_total[$user_id][$type] += $r['cnt'];
            $mod_total['total'][$type] += $r['cnt'];
            $mod_total[$user_id]['total'] += $r['cnt'];
            $mod_total['total']['total'] += $r['cnt'];
        }
    }

    foreach ($t as $mod => $mdata) {
        foreach ($mdata as $type => $tdata) {
            foreach ($tdata as $st => $sdata) {
                if ($sdata > 0) {
                    if (!isset($t[$mod]['total'][$st]))
                        $t[$mod]['total'][$st] = array(0, 0);

                    if (isset($mod_total[$mod]) && $mod_total[$mod][$type] > 0)
                        $share = $sdata / $mod_total[$mod][$type];
                    else
                        $share = 0;
                    $t[$mod][$type][$st] = array($sdata, intval($share * 100));

                    $t[$mod]['total'][$st][0] += $sdata;
                    if (isset($mod_total[$mod]) && $mod_total[$mod]['total'] > 0)
                        $share = $sdata / $mod_total[$mod]['total'];
                    else
                        $share = 0;
                    $t[$mod]['total'][$st][1] += $share * 100;
                }
                if ($mod == 0 && $st == MA_POOLS_STATUS_ANSWERED)
                    $type2name[$type][1] = $sdata;
            }
        }
    }

    uasort($type2name, function($a, $b) {
        if ($a[1] == $b[1])
            return 0;
        return $a[1] < $b[1] ? 1 : -1;
    });

    return array(
        'types' => $type2name,
        'moderators' => $mod2name,
        'data' => $t
    );
}
?>
