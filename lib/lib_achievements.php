<?php

require_once(__DIR__.'/achievements_implementations.php');

class EventTypes {
    const TASK_DONE = "TaskDoneListenerInterface";
    const JOINED_TEAM = "JoinedTeamListenerInterface";
    const WANT_MORE = "WantMoreListenerInterface";
    const SIGNED_UP = "SignedUpListenerInterface";
    const MONTH_PASSED = "MonthPassedListenerInterface";
}

interface ListenerInterface {
    public function dispatch($args);
}

interface TaskDoneListenerInterface {}
interface JoinedTeamListenerInterface {}
interface WantMoreListenerInterface {}
interface SignedUpListenerInterface {}
interface MonthPassedListenerInterface {}

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
        if ($all_pulled) return $this->objects;

        $res = sql_pe("SELECT *
            FROM user_achievements
            WHERE user_id=?", array($this->user_id));

        foreach ($res as $record) {
            $this->objects[$record['achievement_type']]->set($record);
        }

        $this->all_pulled = TRUE;
        return $this->objects;
    }

    public function pull_stats() {
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
                    $out[$record['achievement_type']] = array_fill_keys(range(1, 20), array());
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
        global $config;
        if (!$grades = $config['achievements'][$this->css_class]) return array();
        return array_map(function($pair) {
            return explode(':', $pair);
        }, explode(',', $grades));
    }

    public function grades() {
        global $config;

        $out = array();
        foreach ($this->fetch_grades() as $pair) {
            array_push($out, vsprintf($this->amount_of_work, $pair));
        }
        return $out;
    }

    public function how_to_get_next() {}
}

trait GivenOnTrigger {
    public function dispatch($args) {
        if ($this->given) return;

        $this->given = TRUE;
        $this->seen = FALSE;
        $this->push();
    }
}

