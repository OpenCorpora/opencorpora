<?php
require_once('Mail.php');
function send_email($to, $header, $body) {
    //$to may be either an array or a comma-separated string

    $mail =& Mail::factory('mail');

    mb_internal_encoding('utf-8');

    $headers['From'] = 'OpenCorpora <robot@opencorpora.org>';
    $headers['To'] = $to;
    $headers['Subject'] = mb_encode_mimeheader($header, 'utf-8');
    $headers['Content-Type'] = 'text/plain; charset="UTF-8"';
    $headers['Content-Transfer-Encoding'] = '8bit';

    $b = $mail->send($to, $headers, $body);
    return $b;
}
?>
