<?php

class ModerationFilter {
    const NEXT = +1;
    const PREV = -1;
    private $context;
    private $mainword_idx;
    private $pool_type;

    // 2: ADJF masc/neut
    // 12: NOUN sing/plur
    // 35: NOUN/PREP
    // 36: GRND/PREP
    // 44: CONJ/INTJ
    // 70: INTJ/PREP

    private static $FOCUS_WORD_EXACT = array(
         2 => array('общем'),
        35 => array('посредством', 'типа'),
        36 => array('включая', 'благодаря'),
        44 => array('однако'),
    );

    private static $LENGTH_IN_CONTEXT = array(
         2 => array(self::NEXT, 1),
        35 => array(self::PREV, 2),
        36 => array(self::PREV, 1),
        44 => array(self::NEXT, 1),
        70 => array(self::NEXT, 1),
    );

    public function sample_needs_moderation($pool_type, $sample, $has_focus) {
        $this->context = $sample['context'];
        $this->mainword_idx = $sample['mainword'];
        $this->pool_type = (int)$pool_type;

        $mainword = $this->_word(0);
        $prev_word = $this->_word(self::PREV);

        // check all focus words beginning with a capital letter
        $first_letter = mb_substr($mainword, 0, 1);
        if (mb_strtoupper($first_letter) === $first_letter)
            return true;

        // check all one-symbol focus words except aux parts of speech
        if (
            !in_array($pool_type, array(35, 36, 44, 70)) &&
            mb_strlen($mainword) == 1
        )
            return true;

        // disregard context in any pools except the following
        if (!$has_focus)
            return false;

        if ($this->_check_exact_mainword($mainword))
            return true;

        if ($this->_check_context())
            return true;


        switch ($this->pool_type) {
        case 12:  // NOUN sing/plur
            // focus word with Fixd or Pltm
            foreach ($sample['parses'] as $parse) {
                foreach ($parse->gramlist as $gram) {
                    if (in_array($gram['inner'], array('Fixd', 'Pltm')))
                        return true;
                }
            }

            // left or right context with numbers
            // except 'NNNN goda'
            if (preg_match('/^года$/iu', $mainword) && preg_match('/^[0-9]{4}$/', $prev_word))
                return false;

            for ($i = max(0, $sample['mainword'] - 3); $i < min($sample['mainword'] + 3, sizeof($sample['context'])); ++$i) {
                if ($i == $sample['mainword'])
                    continue;
                if (preg_match('/^(?:\d+|полтор[аы]|дв[ае]|об[ае]|три|четыре)$/iu', $sample['context'][$i]))
                    return true;
            }
            return false;
        case 35:  // NOUN/PREP
            return preg_match('/^только$/iu', $prev_word);
        case 70:  // INTJ/PREP
            return $prev_word == '-';
        default:
            return false;
        }
    }

    private function _word($idx) {
        return isset($this->context[$this->mainword_idx + $idx]) ? $this->context[$this->mainword_idx + $idx] : false;
    }

    private function _check_exact_mainword($mainword) {
        $mainword = mb_strtolower($mainword);
        if (in_array($this->pool_type, self::$FOCUS_WORD_EXACT)) {
            if (in_array($mainword, self::$FOCUS_WORD_EXACT[$this->pool_type]))
                return true;
        }
        return false;
    }

    private function _check_context() {
        if (in_array($this->pool_type, self::$LENGTH_IN_CONTEXT)) {
            list($idx, $length) = self::$LENGTH_IN_CONTEXT[$this->pool_type];
            return mb_strlen($this->_word($idx)) == $length;
        }
        return false;
    }
}
