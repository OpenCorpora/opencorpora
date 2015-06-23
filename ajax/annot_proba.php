<?php
require_once('../lib/header_ajax.php');
require_once('../lib/lib_annot.php');

if (!is_logged()) {
    return;
}


try {
    $result['status'] = 1;
    $result_answers = json_decode($_POST['answers']);
    $answers = array();
    foreach($result_answers as $answ){
        $sample_id = $answ[0];
        $answer_id = $answ[1];
        $moderator_answer_id = 0; // No_answer or DB corrupt

        // TODO save my answer

        $res = sql_query("SELECT `answer` FROM `morph_annot_moderated_samples` WHERE `sample_id` = $sample_id");

        if ($r = sql_fetch_array($res))
            $moderator_answer_id = (int)$r['answer'];

        $answers[] = array($sample_id, $answer_id, $moderator_answer_id);
    }
    $result['answers'] = $answers;
}
catch (Exception $e) {
    $result['status'] = 0;
    $result['error'] = 1;
}
log_timing(true);
die(json_encode($result));
?>
