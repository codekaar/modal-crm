<?php

include '../bootstrap.php';

$to = $_REQUEST["to"];
$title = $_REQUEST["subject"];
$plaintext = wordwrap($_REQUEST["content"], 70);
$plaintext .= "\n\n--\nHai\nResearch in Social Graph\nmakers of Grou.ps and Graph.js";


use Mailgun\Mailgun;

$mg = Mailgun::create(getenv("MAILGUN_KEY")); 

$mg->messages()->send(getenv("MAILGUN_DOMAIN"), [
    'from'    => "Emre <emre@risg.co>", 
    'to'      => $to, 
    'subject' => $title, 
    'text'    => $plaintext
]);

echo json_encode([
    "success"=>true
]);