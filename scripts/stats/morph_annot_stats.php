<?php

if (php_sapi_name() != 'cli')
    die("This script is for CLI only");

// set_include_path(get_include_path().PATH_SEPARATOR.'/corpus');
set_include_path(get_include_path().PATH_SEPARATOR.'/home/grand/opencorpora');
require_once('lib/header_ajax.php');
require_once('lib/lib_morph_pools.php');
update();


class SingleTypeStats {
    public $total_samples = 0;
    public $all_wrong_samples = 0;  // # of samples when all annotators were wrong
    public $total_moderated_answers = 0;
    public $moderated_manually_answers = 0;
    public $total_wrong_answers = 0;
    public $total_answers = 0;

    public $total_moderated_samples = 0;
    public $moderated_manually_samples = 0;

    public function wrong_ans_perc() {
        return $this->total_moderated_answers > 0 ? $this->total_wrong_answers / $this->moderated_manually_answers : 0;
    }
}

class Stats {
    public $stats = array();

    public function update(array $data) {
        $type = $data['type'];
        $status = $data['status'];
        assert($status >= MA_POOLS_STATUS_ANSWERED);

        if (!isset($this->stats[$type])) {
            $this->stats[$type] = new SingleTypeStats;
        }

        $answers = array_sum(array_map(function (array $t) { return count($t); }, array_column($data['samples'], 'instances')));

        $this->stats[$type]->total_samples += count($data['samples']);
        $this->stats[$type]->total_answers += $answers;

        if ($status >= MA_POOLS_STATUS_MODERATED) {
            $this->stats[$type]->total_moderated_samples += count($data['samples']);
            $this->stats[$type]->total_moderated_answers += $answers;
            $this->stats[$type]->total_wrong_answers += array_sum(array_map(function (array $sample) {
                return array_sum(array_map(function($i) use (&$sample) { return ($i['answer_num'] != $sample['moder_answer_num']) ? 1 : 0; }, $sample['instances']));
            }, $data['samples']));
            $this->stats[$type]->moderated_manually_answers += array_sum(array_map(function (array $sample) {
                return $sample['moder_is_manual'] ? count($sample['instances']) : 0;
            }, $data['samples']));

            $dis = array_count_values(array_column($data['samples'], 'disagreed'));
            if (isset($dis[2]))
                $this->stats[$type]->all_wrong_samples += $dis[2];

            $manual = array_count_values(array_column($data['samples'], 'moder_is_manual'));
            if (isset($manual[1]))
                $this->stats[$type]->moderated_manually_samples += $manual[1];
        }
    }

    public function sort() {
        uasort($this->stats, function($a, $b) { return ($a->wrong_ans_perc() > $b->wrong_ans_perc()) ? -1 : 1; });
    }

    public function print_tsv() {
        print join("\t", $this->_header()) . "\n";
        $types = get_morph_pool_types();
        foreach ($this->stats as $typeid => $stats) {
            print join("\t", $this->_values($types[$typeid]['grammemes'], $stats)) . "\n";
        }
    }

    public function print_tsv_formatted() {
        $header = $this->_header();
        $header[0] = sprintf("%50s", $header[0]);
        print join("\t", $header) . "\n";

        $types = get_morph_pool_types();
        $format = $this->_format();
        foreach ($this->stats as $typeid => $stats) {
            $data = $this->_values($types[$typeid]['grammemes'], $stats);
            $items = [];
            foreach ($data as $k => $val) {
                $items[] = sprintf($format[$k], $val);
            }
            print join("\t", $items) . "\n";
        }
    }

    private function _header() {
        return [
            "type",
            "tot s",
            "mod s",
            "manual",
            "all wr",
            "tot a",
            "tot ma",
            "man ma",
            "mod %",
            "wr a",
            "wrong %",
        ];
    }

    private function _values($grammemes, $stats) {
        return [
            $grammemes,
            $stats->total_samples,
            $stats->total_moderated_samples,
            $stats->moderated_manually_samples,
            $stats->all_wrong_samples,
            $stats->total_answers,
            $stats->total_moderated_answers,
            $stats->moderated_manually_answers,
            100 * $stats->total_moderated_answers / $stats->total_answers,
            $stats->total_wrong_answers,
            100 * $stats->wrong_ans_perc(),
        ];
    }

    private function _format() {
        return [
            "%50s",
            "%5d",
            "%5d",
            "%5d",
            "%5d",
            "%d",
            "%d",
            "%5d",
            "%6.2f%%",
            "%4d",
            "%5.2f%%",
        ];
    }
}


function update() {
    $res = sql_query("
        SELECT pool_id
        FROM morph_annot_pools
        WHERE status >= " . MA_POOLS_STATUS_ANSWERED . "
        ORDER BY pool_id
    ");

    $stats = new Stats;
    while ($row = sql_fetch_array($res)) {
        // print $row['pool_id'] ."\n";
        $pool_info = get_morph_samples_page($row['pool_id'], true, 1000);
        $stats->update($pool_info);
    }
    $stats->sort();
    $stats->print_tsv();
}
