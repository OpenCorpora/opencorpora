<?php
require_once('lib_xml.php');

function parse_dict_rev_gram(array $src) {
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
function parse_dict_rev($text) {
    // output has the following structure:
    // lemma => array (text => lemma_text, grm => array (grm1, grm2, ...)),
    // forms => array (
    //     [0] => array (text => form_text, grm => array (grm1, grm2, ...)),
    //     [1] => ...
    // )
    $arr = xml2ary($text);
    $arr = $arr['dr']['_c'];
    $parsed = array();
    $parsed['lemma']['text'] = $arr['l']['_a']['t'];
    $parsed['lemma']['grm'] = parse_dict_rev_gram($arr['l']['_c']['g']);
    if (isset($arr['f']['_a'])) {
        //if there is only one form
        $parsed['forms'][0]['text'] = $arr['f']['_a']['t'];
        $parsed['forms'][0]['grm'] = parse_dict_rev_gram($arr['f']['_c']['g']);
    } else {
        foreach ($arr['f'] as $k=>$farr) {
            $parsed['forms'][$k]['text'] = $farr['_a']['t'];
            $parsed['forms'][$k]['grm'] = parse_dict_rev_gram($farr['_c']['g']);
        }
    }
    return $parsed;
}

class WordForm {
    public $text;
    public $grammemes;
}

class Lexeme {
    public $lemma;
    public $forms = array();

    public function __construct($xml) {
        $tmp = parse_dict_rev($xml);

        $this->lemma = new WordForm;
        $this->lemma->text = $tmp['lemma']['text'];
        $this->lemma->grammemes = $tmp['lemma']['grm'];

        foreach ($tmp['forms'] as $f) {
            $form = new WordForm;
            $form->text = $f['text'];
            $form->grammemes = $f['grm'];

            $this->forms[] = $form;
        }
    }

    public function get_all_forms_texts() {
        $forms = array();
        foreach ($this->forms as $form) {
            $forms[] = $form->text;
        }
        return $forms;
    }
}

?>
