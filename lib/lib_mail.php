<?php

function send_email($to, $header, $body) {
    //$to may be either an array or a comma-separated string
    if (!is_array($to)) $to = explode(',', $to);
    // Create the message
    $message = Swift_Message::newInstance()
      // Give the message a subject
      ->setSubject($header)
      // Set the From address with an associative array
      ->setFrom(array('robot@opencorpora.org' => 'OpenCorpora'))
      // Set the To addresses with an associative array
      ->setTo($to)
      // Give it a body
      ->setBody($body);
    // Create the Transport
    $transport = Swift_MailTransport::newInstance(); // should switch to SMTP though!
    // Create the Mailer using your created Transport
    $mailer = Swift_Mailer::newInstance($transport);
    return $mailer->send($message);
}
