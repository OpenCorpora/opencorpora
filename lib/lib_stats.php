<?php
function get_common_stats() {
    global $config;
    $stats = array();

    $res = sql_query("SELECT * FROM stats_param WHERE is_active=1 AND param_id NOT IN(SELECT DISTINCT param_id FROM user_stats)");
    while ($r = sql_fetch_array($res)) {
        $arr = sql_fetch_array(sql_query("SELECT param_value FROM stats_values WHERE param_id=".$r['param_id']." ORDER BY `timestamp` DESC LIMIT 1"));
        $stats[$r['param_name']] = array('value' => $arr['param_value']);
    }

    foreach (array('total', 'chaskor', 'chaskor_news', 'wikipedia', 'wikinews', 'blogs', 'fiction') as $src) {
        $stats['goals'][$src.'_words'] = $config['goals'][$src.'_words'];
        $stats['percent_words'][$src] = floor($stats[$src.'_words']['value'] / $config['goals'][$src.'_words'] * 100);
    }

    $stats['added_sentences'] = get_sentence_adders_stats();
    $stats['added_sentences_last_week'] = get_sentence_adders_stats(true);

    // we need 2 timestamps to show last activity
    $stats['timestamp_yesterday'] = ($stats['timestamp_today'] = mktime(0, 0, 0)) - 3600 * 24;

    return $stats;
}
function get_sentence_adders_stats($last_week=false) {
    if ($last_week)
        $param = 7;
    else
        $param = 6;

    $out = array();
    $res = sql_query("SELECT user_shown_name AS user_name, param_value FROM user_stats LEFT JOIN users USING (user_id) WHERE param_id=$param ORDER BY param_value DESC");
    while ($r = sql_fetch_array($res)) {
        $out[] = array('user_name' => $r['user_name'], 'value' => $r['param_value']);
    }
    return $out;
}
function get_word_stats_for_chart() {
    $chart = array();
    $t = array();
    $tchart = array();
    $time = time();

    $param_set = array(32, 27, 23, 19, 15, 11);

    foreach ($param_set as $param_id) {
        $res = sql_query("SELECT timestamp, param_value FROM stats_values WHERE timestamp > ".($time - 90*24*60*60)." AND param_id = $param_id ORDER BY timestamp");
        while ($r = sql_fetch_array($res)) {
            $day = intval($r['timestamp'] / 86400);
            $t[$day][$param_id] = $r['param_value'];
        }
    }
    ksort($t);

    // we need two cycles for cases when a parameter was measured more than once a day
    // we suppose that all parameters were measured simultaneously

    foreach ($t as $day => $ar) {
        $sum = 0;
        foreach ($param_set as $param_id) {
            $sum += $ar[$param_id];
            $tchart[$param_id][] = '['.($day * 86400000).','.$sum.']';
        }
    }

    $chart['chaskor_words'] = join(',', $tchart[11]);
    $chart['wikinews_words'] = join(',', $tchart[15]);
    $chart['wikipedia_words'] = join(',', $tchart[19]);
    $chart['blogs_words'] = join(',', $tchart[23]);
    $chart['chaskor_news_words'] = join(',', $tchart[27]);
    $chart['fiction_words'] = join(',', $tchart[32]);

    return $chart;
}
function get_tag_stats() {
    $out = array();
    $res = sql_query("SELECT prefix, value, texts, words FROM tag_stats ORDER BY prefix, texts DESC, words DESC");
    
    while ($r = sql_fetch_array($res)) {
        $out[$r['prefix']][] = array('value' => $r['value'], 'texts' => $r['texts'], 'words' => $r['words']);
    }
    return $out;
}
function get_user_stats() {
    $annotators = array();
    // team info
    $uid2team = array();
    $res = sql_query("SELECT user_id, user_team FROM users WHERE user_team > 0");
    while ($r = sql_fetch_array($res))
        $uid2team[$r['user_id']] = $r['user_team'];
    $teams = get_team_list();
    foreach ($teams as $i => $team) {
        $teams[$i]['total'] = $teams[$i]['moderated'] = $teams[$i]['correct'] = 0;
    }

    $uid2sid = array();
    $res = sql_query("SELECT user_id, COUNT(*) AS cnt FROM morph_annot_instances WHERE answer > 0 GROUP BY user_id ORDER BY cnt DESC");
    while ($r = sql_fetch_array($res)) {
        $annotators[] = array('total' => number_format($r['cnt'], 0, '', ' '), 'user_id' => $r['user_id']);
        $uid2sid[$r['user_id']] = sizeof($annotators) - 1;
        if (isset($uid2team[$r['user_id']]))
            $teams[$uid2team[$r['user_id']]]['total'] += $r['cnt'];
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
    $res = sql_query("SELECT user_id, MAX(timestamp) AS last_time FROM morph_annot_click_log GROUP BY user_id");
    while ($r = sql_fetch_array($res)) {
        $last_click[$r['user_id']] = $r['last_time'];
    }

    // divergence and moderation info
    $divergence = array();
    $moderated = array();
    $correct = array();

    $res = sql_query("SELECT user_id, param_id, param_value FROM user_stats WHERE param_id IN (34, 38, 39)");
    while ($r = sql_fetch_array($res)) {
        switch ($r['param_id']) {
            case 34:
                $divergence[$r['user_id']] = $r['param_value'];
                break;
            case 38:
                $moderated[$r['user_id']] = $r['param_value'];
                if (isset($uid2team[$r['user_id']]))
                    $teams[$uid2team[$r['user_id']]]['moderated'] += $r['param_value'];
                break;
            case 39:
                $correct[$r['user_id']] = $r['param_value'];
                if (isset($uid2team[$r['user_id']]))
                    $teams[$uid2team[$r['user_id']]]['correct'] += $r['param_value'];
        }
    }

    foreach ($teams as $i => $team) {
        if ($team['moderated'])
            $teams[$i]['error_rate'] = 100 * (1 - $team['correct'] / $team['moderated']);
        else
            $teams[$i]['error_rate'] = 0;
    }

    $res = sql_query("SELECT u.user_id, u.user_shown_name AS user_name, param_value FROM user_stats s LEFT JOIN users u ON (s.user_id=u.user_id) WHERE param_id=33 ORDER BY param_value DESC");
    while ($r = sql_fetch_array($res)) {
        $t = array(
            'user_id' => $r['user_id'],
            'user_name' => $r['user_name'],
            'value' => number_format($r['param_value'], 0, '', ' '),
            'divergence' => $divergence[$r['user_id']] / $r['param_value'] * 100,
            'last_active' => $last_click[$r['user_id']],
            'moderated' => isset($moderated[$r['user_id']]) ? $moderated[$r['user_id']] : 0,
            'error_rate' => (!isset($moderated[$r['user_id']]) || !$moderated[$r['user_id']]) ? 0 : (1 - $correct[$r['user_id']] / $moderated[$r['user_id']]) * 100
        );
        $annotators[$uid2sid[$r['user_id']]]['fin'] = $t;
    }

    foreach ($annotators as $k => $v) {
        if (!isset($v['fin']['user_name'])) {
            $annotators[$k]['fin']['user_id'] = $v['user_id'];
            $annotators[$k]['fin']['user_name'] = get_user_shown_name($v['user_id']);
            $annotators[$k]['fin']['last_active'] = isset($last_click[$v['user_id']]) ? $last_click[$v['user_id']] : 0;
            $annotators[$k]['fin']['moderated'] = isset($moderated[$v['user_id']]) ? $moderated[$v['user_id']] : 0;
            $annotators[$k]['fin']['error_rate'] = (!isset($moderated[$v['user_id']]) || !$moderated[$v['user_id']]) ? 0 : (1 - $correct[$v['user_id']] / $moderated[$v['user_id']]) * 100;
        }
    }
    return array('annotators' => $annotators, 'teams' => $teams);
}
?>
