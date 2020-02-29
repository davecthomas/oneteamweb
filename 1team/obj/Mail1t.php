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
      // $this->email = new \SendGrid\Mail();
      // $this->sendgrid = new \SendGrid($this->apiKey);
      // $this->session = $session;
      // $this->statuscode = null;
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
    $result = null;
    $from = $this->from;
    // var_dump(array($to_address, $subject, $body, $to_name));
    try {
      
      $to = new SendGrid\Email($to_name, $to_address);
      $content = new SendGrid\Content("text/plain", $body );
      $mail = new SendGrid\Mail($from, $subject, $to, $content);
      $response = $this->sg->client->mail()->send()->post($mail);
      $this->statuscode = $response->statusCode();
      $result = $this->statusok($this->statuscode);
    } catch (Exception $e) {
      echo 'Mail1t.mail exception: ',  $e->getMessage(), "\n";
    }
    return $result;
  }

  function statusok($statuscode){
    return $statuscode < 400;
  }

}
?>
