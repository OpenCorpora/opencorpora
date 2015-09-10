<?php

require_once('constants.php');
require_once('lib_users.php');

class EventTypes {
    const TASK_DONE = "TaskDoneListenerInterface";
    const JOINED_TEAM = "JoinedTeamListenerInterface";
    const WANT_MORE = "WantMoreListenerInterface";
    const SIGNED_UP = "SignedUpListenerInterface";
    const MONTH_PASSED = "MonthPassedListenerInterface";
}

class RestrictionTypes {
    const NONE = 0;
    const DIVERGENCE_PCT = 1;
    const ERROR_PCT = 2;
}

interface ListenerInterface {
    public function dispatch($args);
}

interface TaskDoneListenerInterface extends ListenerInterface {}
interface JoinedTeamListenerInterface extends ListenerInterface {}
interface WantMoreListenerInterface extends ListenerInterface {}
interface SignedUpListenerInterface extends ListenerInterface {}
interface MonthPassedListenerInterface extends ListenerInterface {}

require_once(__DIR__.'/achievements_implementations.php');


abstract class Achievement {
    // Текст "как получить ачивку"
    // например, "Чтобы получить эту ачивку, надо вступить в команду"
    public $how_to_get;

    // описание колонки с пороговыми значениями уровней
    public $column_description;

    // вдохновляющий текст ачивки
    // а-ля "За любознательность"
    public $caption;

    // краткое название
    public $short_title;

    public $css_class;

    public $amount_of_work = "%d"; // string for sprintf

    // is this achievement given to the current user
    public $given = FALSE;

    // is it even obtainable? (should we draw a stub for it)
    public $collectable = TRUE;

    // was this achievement updated recently?
    // if so, we might need to show a pop-up banner
    public $seen = TRUE;

    // текущий ID юзера, для которого все считаем
    protected $user_id;
    public function __construct($user_id) {
        $this->user_id = $user_id;
    }

    public function set($db_record) {
        $this->given = TRUE;
        $this->seen = (bool)$db_record['seen'];
        $this->updated = $db_record['updated'];
    }

    public function push() {
        return sql_pe("INSERT INTO user_achievements
            (achievement_type, seen, user_id)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                seen = VALUES(seen)",
                array($this->css_class, (int)$this->seen, $this->user_id));
    }

}

class AchievementsManager {
    private $user_id;
    private $achievement_types;
    public $objects = array();
    private $all_pulled = FALSE;

    public function __construct($user_id) {
        global $config;
        $this->user_id = $user_id;
        $this->achievement_types = array_map(function($n) {
            return $n."achievement";
        }, explode(',', $config['achievements']['names']));

        $this->init_all();
        $this->pull_all();
    }

    public function emit($event_type, $args = array()) {
        $args['event_type'] = $event_type;
        foreach ($this->objects as $obj) {
            if (in_array($event_type, class_implements($obj))) {
                $obj->dispatch($args);
            }
        }
    }

    public function init_all() {
        $objects = array();
        foreach ($this->achievement_types as $class) {
            $obj = new $class($this->user_id);
            if ($obj->collectable) {
                $objects[$obj->css_class] = $obj;
            }
        }

        $this->objects = $objects;
        return $objects;
    }

    public function pull_all() {
        if ($this->all_pulled) return $this->objects;

        $res = sql_pe("SELECT *
            FROM user_achievements
            WHERE user_id=?", array($this->user_id));

        foreach ($res as $record) {
            $this->objects[$record['achievement_type']]->set($record);
        }

        $this->all_pulled = TRUE;
        return $this->objects;
    }

    public function get_closest() {
        $all = $this->pull_all();
        $next = NULL;
        foreach ($all as $a) {
            if (!isset($a->level) || $a->progress >= 100)
                continue;
            if (!$next || $a->progress > $next->progress)
                $next = $a;
        }
        return $next;
    }

    public function pull_stats() {
        global $config;
        $res = sql_pe("
            SELECT *
            FROM user_achievements
            JOIN users USING (user_id)
            ORDER BY achievement_id DESC, updated DESC
        ", array());

        $out = array();
        foreach ($res as $record) {
            $haslevels = isset($this->objects[$record['achievement_type']]->level);
            if (empty($out[$record['achievement_type']])) {
                if ($haslevels)
                    $out[$record['achievement_type']] = array_fill_keys(range(1, $config['achievements']['max_level']), array());
                else $out[$record['achievement_type']] = array();
            }

            if ($haslevels)
                array_push($out[$record['achievement_type']][$record['level']], $record);
            else
                array_push($out[$record['achievement_type']], $record);
        }

        $total = sql_pe("SELECT COUNT(*) FROM users", array());
        $out['total_users'] = $total[0][0];
        return $out;
    }

    public function set_all_seen() {
        sql_pe("UPDATE user_achievements
                SET seen=1
                WHERE user_id=?",
                array($this->user_id));
    }
}

trait AchievementWithLevels {
    public $progress = 0, // %
    $level = 0; // number

    public function set($db_record) {
        parent::set($db_record);
        $this->level = (int)$db_record['level'];
        $this->progress = (int)$db_record['progress'];
    }

    public function push() {
        return sql_pe("INSERT INTO user_achievements
            (achievement_type, level, progress, seen, user_id)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                seen=VALUES(seen),
                level=GREATEST(level, VALUES(level)),
                progress=VALUES(progress)",

            array($this->css_class, $this->level, $this->progress, (int)$this->seen,
                $this->user_id));
    }

    public function fetch_grades() {
        return $this->level_reqs;
    }

    public function grades() {
        $out = array();
        foreach ($this->fetch_grades() as $pair) {
            array_push($out, vsprintf($this->amount_of_work, $pair));
        }
        return $out;
    }

    protected function _types_spelling($number) {
        $lastdigit = substr($number, -1);
        if ($lastdigit == 1) return "типе";
        return "типах";
    }

    protected function _tasks_spelling($number) {
        $lastdigit = substr($number, -1);
        $prevdigit = substr($number, -2, 1);

        if ($lastdigit == 1 && $prevdigit != 1 && $prevdigit != 0)
            return "задание";
        if (in_array($lastdigit, range(2, 4)) && $prevdigit != 1)
            return "задания";
        return "заданий";
    }

    public function how_to_get_next() {}
}

trait AchievementWithQualityRestriction {
    // for now these are the same for all achievements
    private $restriction_levels = array(
        array(RestrictionTypes::NONE, 1),
        array(RestrictionTypes::NONE, 1),
        array(RestrictionTypes::NONE, 1),
        array(RestrictionTypes::DIVERGENCE_PCT, 0.50),
        array(RestrictionTypes::DIVERGENCE_PCT, 0.45),
        array(RestrictionTypes::DIVERGENCE_PCT, 0.40),
        array(RestrictionTypes::DIVERGENCE_PCT, 0.35),
        array(RestrictionTypes::DIVERGENCE_PCT, 0.30),
        array(RestrictionTypes::DIVERGENCE_PCT, 0.25),
        array(RestrictionTypes::ERROR_PCT, 0.15),
        array(RestrictionTypes::ERROR_PCT, 0.14),
        array(RestrictionTypes::ERROR_PCT, 0.13),
        array(RestrictionTypes::ERROR_PCT, 0.12),
        array(RestrictionTypes::ERROR_PCT, 0.11),
        array(RestrictionTypes::ERROR_PCT, 0.10),
        array(RestrictionTypes::ERROR_PCT, 0.09),
        array(RestrictionTypes::ERROR_PCT, 0.08),
        array(RestrictionTypes::ERROR_PCT, 0.07),
        array(RestrictionTypes::ERROR_PCT, 0.06),
        array(RestrictionTypes::ERROR_PCT, 0.05),
    );

    private function _get_divergence_info() {
        // returns array(number_of_checked_samples, divergence_rate)
        $res = sql_pe("
            SELECT param_value FROM user_stats
            WHERE param_id = ".STATS_ANNOTATOR_DIVERGENCE_TOTAL."
            AND user_id = ?
        ", array($this->user_id));
        if (!sizeof($res))
            return array(0, 1);

        $divergent_count = $res[0]['param_value'];

        $info = get_user_info($this->user_id);
        $diverg_rate = $info['answers_in_ready_pools'] ? $divergent_count / $info['answers_in_ready_pools'] : 1;
        return array($info['answers_in_ready_pools'], $diverg_rate);
    }

    private function _get_error_rate_info() {
        // returns array(number_of_checked_samples, error_rate)
        $info = get_user_info($this->user_id);
        $error_rate = $info['checked_answers'] ? $info['incorrect_answers'] / $info['checked_answers'] : 1;
        return array($info['checked_answers'], $error_rate);
    }

    private function _has_enough_quality_info($level, $count) {}

    public function check_quality_restrictions($level) {
        // $level counts from 1

        $quality_info = array();

        switch ($this->restriction_levels[$level-1][0]) {
            case RestrictionTypes::NONE:
                return TRUE;
            case RestrictionTypes::DIVERGENCE_PCT:
                $quality_info = $this->_get_divergence_info();
                break;
            case RestrictionTypes::ERROR_PCT:
                $quality_info = $this->_get_error_rate_info();
                break;
            default:
                throw new Exception("Unknown restriction type");
        }

        if (!$this->_has_enough_quality_info($level, $quality_info[0]))
            return FALSE;

        return $this->restriction_levels[$level-1][1] >= $quality_info[1]; 
    }
}

trait GivenOnTrigger {
    public function dispatch($args) {
        if ($this->given) return;

        $this->given = TRUE;
        $this->seen = FALSE;
        $this->push();
    }
}

