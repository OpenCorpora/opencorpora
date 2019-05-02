<?php
require_once('lib_db.php');
require_once('lib_xml.php');


class WordForm {
    public $text;
    public $grammemes;
}

class Lexeme {
    public $lemma;
    public $forms;

    private static $gram_order = array();

    public function __construct($xml = NULL) {
        $this->lemma = new WordForm;
        $this->forms = array();

        if ($xml) {
            $arr = xml2ary($xml);
            $arr = $arr['dr']['_c'];
            $this->lemma->text = $arr['l']['_a']['t'];
            $this->lemma->grammemes = self::_parse_grammemes($arr['l']['_c']['g']);
            if (isset($arr['f']['_a'])) {
                // if there is only one form
                $form = new WordForm;
                $form->text = $arr['f']['_a']['t'];
                $form->grammemes = self::_parse_grammemes($arr['f']['_c']['g']);
                $this->forms[] = $form;
            } else {
                foreach ($arr['f'] as $k => $farr) {
                    $form = new WordForm;
                    $form->text = $farr['_a']['t'];
                    $form->grammemes = self::_parse_grammemes($farr['_c']['g']);
                    $this->forms[] = $form;
                }
            }
        }
    }

    public function set_lemma($text, $gram) {
        $this->lemma->text = $text;
        $this->lemma->grammemes = self::_prepare_gram_array($gram);
    }

    public function set_paradigm(array $forms_text, array $forms_gram) {
        if (sizeof($forms_text) != sizeof($forms_gram)) {
            throw new UnexpectedValueException();
        }
        $this->forms = array();
        foreach ($forms_text as $i => $text) {
            $text = trim($text);
            if ($text === '') {
                //the form is to be deleted, so we do nothing
            } elseif (strpos($text, ' ') !== false) {
                throw new UnexpectedValueException();
            } else {
                // TODO: perhaps some data validity check?
                $form = new WordForm;
                $form->text = $text;
                $form->grammemes = self::_prepare_gram_array($forms_gram[$i]);
                $this->forms[] = $form;
            }
        }
    }

    public function to_xml() {
        $new_xml = '<dr><l t="'.htmlspecialchars(mb_strtolower($this->lemma->text)).'">';
        foreach ($this->lemma->grammemes as $gr) {
            $new_xml .= '<g v="'.htmlspecialchars($gr).'"/>';
        }
        $new_xml .= '</l>';
        // paradigm
        foreach ($this->forms as $form) {
            $new_xml .= '<f t="'.htmlspecialchars(mb_strtolower($form->text)).'">';
            foreach ($form->grammemes as $gr) {
                $new_xml .= '<g v="'.htmlspecialchars($gr).'"/>';
            }
            $new_xml .= '</f>';
        }
        $new_xml .= '</dr>';
        return $new_xml;
    }

    public function get_all_forms_texts() {
        $forms = array() ;
        foreach ($this->forms as $form) {
            $forms[] = $form->text;
        }
        return $forms;
    }

    private static function _parse_grammemes(array $src) {
        $t = array();
        foreach ($src as $garr) {
            if (isset($garr['v'])) {
                // if there is only one grammeme
                $t[] = $garr['v'];
                break;
            }
            $t[] = $garr['_a']['v'];
        }
        return $t;
    }

    private static function _load_grammeme_order() {
        $res = sql_query("SELECT inner_id FROM gram ORDER BY orderby");
        while ($r = sql_fetch_array($res))
            self::$gram_order[] = $r['inner_id'];
    }

    private static function _sort_grammemes(&$gram_array) {
        usort($gram_array, function($a, $b) {
            return array_search($a, self::$gram_order) < array_search($b, self::$gram_order) ? -1 : 1;
        });
    }

    private static function _prepare_gram_array($raw_grams) {
        if (sizeof(self::$gram_order) == 0) {
            self::_load_grammeme_order();
        }
        if (!is_array($raw_grams))
            $raw_grams = explode(',', $raw_grams);
        $grams = array_filter(array_map("trim", $raw_grams), "strlen");
        self::_sort_grammemes($grams);
        return $grams;
    }
}

?>
