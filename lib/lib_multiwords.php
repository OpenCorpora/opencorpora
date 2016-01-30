<?php
require_once('lib_annot.php');

class MultiWordTask {

    const ANSWERS_PER_TASK = 4;
    const CONTEXT_WIDTH = 5;

    const NOT_READY        = 0;
    const READY            = 1;
    const APPROVED_AUTO    = 2;
    const REJECTED_AUTO    = 3;
    const APPROVED_MANUAL  = 4;
    const REJECTED_MANUAL  = 5;

    const ANSWER_YES  = 1;
    const ANSWER_NO   = 2;
    const ANSWER_SKIP = 3;
    

    public static function get_task($user_id) {
        $res = sql_pe("
            SELECT mw_id, token_id, sent_id
            FROM mw_main
            JOIN mw_tokens mwt USING (mw_id)
            LEFT JOIN tokens t USING (tf_id)
            WHERE status = ". NOT_READY ."
            AND mw_id NOT IN (
                SELECT DISTINCT mw_id
                FROM mw_answers
                WHERE user_id = ?
            )
            ORDER BY mw_id
            LIMIT 10
        ", array($user_id));

        if (!sizeof($res))
            return false;

        $sent_id = $res[0]['sent_id'];
        $mw_id = $res[0]['mw_id'];
        $token_ids = array();
        foreach ($res as $row) {
            if ($mw_id != $row['mw_id'])
                break;
            if ($sent_id != $row['sent_id'])
                throw new Exception("Inconsistent sentence id");
            $token_ids[] = $row['tf_id'];
        }

        if (sizeof($token_ids) < 2)
            throw new Exception("Too few tokens");

        return array(
            'id' => $mw_id,
            'token_ids' => $token_ids,
            'context' => get_context_for_word($token_ids[0], sizeof($token_ids) + CONTEXT_WIDTH)
        );
    }

    public static function register_answer($mw_id, $user_id, $answer) {
        if (!$mw_id || !$user_id || !in_array($answer, array(ANSWER_YES, ANSWER_NO, ANSWER_SKIP)))
            throw new UnexpectedValueException();
        sql_begin();
        sql_pe("INSERT INTO mw_answers (mw_id, user_id, answer) VALUES (?, ?, ?)", array($mw_id, $user_id, $answer));
        _check_mw_status($mw_id);
        sql_commit();
    }

    private static function _check_mw_status($mw_id) {
        $res = sql_pe("
            SELECT COUNT(DISTINCT user_id) As cnt
            FROM mw_answers
            WHERE mw_id = ?
            AND answer in (".ANSWER_YES.", ".ANSWER_NO.")
        ", array($mw_id));

        if ($res[0]['cnt'] >= ANSWERS_PER_TASK)
            sql_pe("UPDATE mw_main SET status = ? WHERE mw_id = ? LIMIT 1", array(READY, $mw_id));
    }
}

?>
