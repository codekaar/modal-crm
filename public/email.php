<?php

include '../bootstrap.php';

$to = $_REQUEST["to"];
$title = $_REQUEST["subject"];
$plaintext = wordwrap($_REQUEST["content"], 70);
$plaintext .= "\nHai\nResearch in Social Graph\nmakers of Grou.ps and Graph.js";


use Mailgun\Mailgun;

$mg = Mailgun::create(getenv("MAILGUN_KEY")); 

$mg->messages()->send(getenv("MAILGUN_DOMAIN"), [
    'from'    => getenv("MAIL_FROM_NAME")." <".getenv("MAIL_FROM_ADDRESS").">", 
    'to'      => $to, 
    'subject' => $title, 
    'text'    => $plaintext
]);

echo json_encode([
    "success"=>true
]);