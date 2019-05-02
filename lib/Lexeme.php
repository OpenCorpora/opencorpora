<?php
require_once('lib_xml.php');


class WordForm {
    public $text;
    public $grammemes;
}

class Lexeme {
    public $lemma;
    public $forms = array();

    public function __construct($xml) {
        $arr = xml2ary($xml);
        $arr = $arr['dr']['_c'];
        $this->lemma = new WordForm;
        $this->lemma->text = $arr['l']['_a']['t'];
        $this->lemma->grammemes = self::_parse_grammemes($arr['l']['_c']['g']);
        if (isset($arr['f']['_a'])) {
            // if there is only one form
            $form = new WordForm;
            $form->text = $arr['f']['_a']['t'];
            $form->grammemes = self::_parse_grammemes($arr['f']['_c']['g']);
            $this->forms = array($form);
        } else {
            $this->forms = array();
            foreach ($arr['f'] as $k => $farr) {
                $form = new WordForm;
                $form->text = $farr['_a']['t'];
                $form->grammemes = self::_parse_grammemes($farr['_c']['g']);
                $this->forms[] = $form;
            }
        }
    }

    public function get_all_forms_texts() {
        $forms = array();
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
}

?>
