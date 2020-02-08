<?php
include_once ('globals.php');
include_once ('utils.php');
require 'vendor/autoload.php';
/**
 * Support Mail
 *
 * @author dthomas
 */
class Mail {
  function __construct( ) {
    $this->from = new SendGrid\Email(null, emailadmin);
    $this->apiKey = getenv('SENDGRID_API_KEY');
  }
//
//   function mail($to_address, $subject, $body){
//     $from = $this->from;
//
//     $to = new SendGrid\Email(null, $to_address);
//     $content = new SendGrid\Content("text/plain", $body);
//     $mail = new SendGrid\Mail($from, $subject, $to, $content);
//
//     $sg = new \SendGrid($this->apiKey);
//     $response = $sg->client->mail()->send()->post($mail);
//     return $response->statusCode();
//     // echo $response->headers();
//     // echo $response->body();
//   }
//
//   function statusok($statuscode){
//     return $statuscode < 400;
//   }
//
}
?>
