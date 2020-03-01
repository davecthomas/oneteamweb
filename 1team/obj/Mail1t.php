<?php
// include_once ('globals.php');
// include_once ('utils.php');
require '../vendor/autoload.php';

/**
 * Support Mail
 *
 * @author dthomas
 */
class Mail1t {
  function __construct( $session = null ) {
    try {
    
      $this->from = new SendGrid\Email(companyname. " ". defaultterm_appadmin, emailadmin);
      $this->apiKey = getenv('SENDGRID_API_KEY');
      $this->sg = new \SendGrid($this->apiKey);
      $this->statuscode = null;
      $this->response = null;
    } catch (Exception $e) {
        echo 'Mail1t exception: ',  $e->getMessage(), "\n";
    }
  }

  // function helloEmail(){
  //   $from = new SendGrid\Email(null, "app160835029@heroku.com");
  //   $subject = "New email World from the SendGrid PHP Library!";
  //   $to = new SendGrid\Email(null, "foo@gmail.com");
  //   $content = new SendGrid\Content("text/plain", "Testing, This is different!");
  //   $mail = new SendGrid\Mail($from, $subject, $to, $content);

  //   $apiKey = getenv('SENDGRID_API_KEY');
  //   $sg = new \SendGrid($apiKey);

  //   $response = $sg->client->mail()->send()->post($mail);
  //   echo $response->statusCode();
  //   echo $response->headers();
  //   echo $response->body();
  // }

  function mail($to_address, $subject, $body, $to_name = ""){
    $bError = false;
    $from = $this->from;
    // var_dump(array($to_address, $subject, $body, $to_name));
    try { 
      $to = new SendGrid\Email($to_name, $to_address);
      $content = new SendGrid\Content("text/plain", $body );
      $mail = new SendGrid\Mail($from, $subject, $to, $content);
      $this->response  = $this->sg->client->mail()->send()->post($mail);
      $this->statuscode = intval($this->response->statusCode());
      $bError = ! $this->statusok($this->response );
    } catch (Exception $e) {
      echo 'Mail1t.mail exception: ',  $e->getMessage(), "\n";
    }
    return $bError;
  }

  function statusok(){
    if ($this->statuscode == null) return false;
    else {
      $status = intval($this->response->statuscode());
      return ($status < 400);
    }
  }

}
?>
