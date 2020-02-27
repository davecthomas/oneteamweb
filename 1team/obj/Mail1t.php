<?php
include_once ('globals.php');
include_once ('utils.php');
require '../vendor/autoload.php';
/**
 * Support Mail
 *
 * @author dthomas
 */
class Mail1t {
  function __construct( $session = null ) {
    $this->from = new SendGrid\Email(null, emailadmin);
    $this->apiKey = getenv('SENDGRID_API_KEY');
    $this->sendgrid = new \SendGrid($this->apiKey);
    $this->session = $session;
    $this->statuscode = null;
  }

  function mail($to_address, $subject, $body, $to_name = ""){
    $result = false;
    $from = $this->from;
    // var_dump(array($to_address, $subject, $body, $to_name));
    try {
      $email = new \SendGrid\Mail\Mail();
      var_dump($email);
      $email->setFrom($from, "1TeamWeb Support");
      $email->setSubject($subject);
      $email->addTo($to_address, $to_name);
      $email->addContent(
          "text/plain", $body
      );
    // $email->addContent(
    //     "text/html", "<strong>and easy to do anywhere, even with PHP</strong>"
    // );

      var_dump($email);
      $response = $this->sendgrid->send($email);
      $this->statuscode = $response->statusCode();
      print $this->statuscode . "\n";
      print_r($response->headers());
      print $response->body() . "\n";
      $result = $this->statusok($this->statuscode);

    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
    }
    return result;
  }

    // $to = new SendGrid\Mail(null, $to_address);
    // $content = new SendGrid\Content("text/plain", $body);
    // $mail = new SendGrid\Mail($from, $subject, $to, $content);

    // $sg = new \SendGrid($this->apiKey);
    // $response = $sg->client->mail()->send()->post($mail);
  //   return $response->statusCode();
  // }


  function statusok($statuscode){
    return $statuscode < 400;
  }

}
?>
