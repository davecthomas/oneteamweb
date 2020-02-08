<?php
include("utilsbase.php");
include_once('obj/Mail.php');
$title = appname . " Registration";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><?php echo $title ?></title>
<link rel="stylesheet" type="text/css" href="1team.css"/>
<script type="text/javascript" src="/1team/utils.js"></script>
</head>
<body>
<div id="wrapper">
<?php include('nav-notloggedin.php'); ?>
<h3><?php echo $title ?></h3>
<?php
$bError = false;
if (( !isset($_POST["firstname"])) || (isURL($_POST["firstname"]))){
	$bError = true;
	$err = "f";
} else {
	$firstname = cleanSQL($_POST["firstname"]);
}
if (( !isset($_POST["lastname"])) || (isURL($_POST["lastname"]))){
	$bError = true;
	$err = "l";
} else {
	$lastname = cleanSQL($_POST["lastname"]);
}
if (( !isset($_POST["email"])) || (isURL($_POST["email"]))){
	$bError = true;
	$err = "e";
} else {
	$email = cleanSQL($_POST["email"]);
     if (! isValidEmail($email)) {
          $bError = true;
          $err = "e";
	}
}
if (( !isset($_POST["phone"])) || (isURL($_POST["phone"]))){
	$bError = true;
	$err = "p";
} else {
	$phone = cleanSQL($_POST["phone"]);
}

// Not required
if ( isset($_POST["referredby"])) {
	$referredby = cleanSQL($_POST["referredby"]);
} else $referredby = "";

if ( isset($_POST["activity"])) {
	$activity = cleanSQL($_POST["activity"]);
} else $activity = "";
if ( isset($_POST["name"])) {
	$name = cleanSQL($_POST["name"]);
} else $name = "";

// TO DO - check CAPTCHA, send email
// on error - send back to register page
if (!$bError){

		$emailsubject = $title . ": Team name: " . $name . "\n\n";
		$textBody = "Reporting IP Address: " . $_SERVER["REMOTE_ADDR"]. "\n\n";
		$textBody = $textBody . "Name: " . $firstname . " " . $lastname . "\n\n";
		$textBody = $textBody . "Activity: " . $activity . "\n\n";
		$textBody = $textBody . "Email: " . $email . "\n\n";
		$textBody = $textBody . "Phone: " . $phone . "\n\n";
		$textBody = $textBody . "Referred by: " . $referredby . "\n\n";
		ini_set("SMTP", MailServer);
		$m = new Mail();
		$statuscode = $m->mail(emailadmin, $emailsubject, $textBody);

		if (!$m->statusok($statuscode)){
			$bError = TRUE;
		}
?>
<h3><?php echo $title?></h3>
<p>Your registration has been sent. We will contact you with details soon. Thank you!</p>
<p><a href="default.php">Home</a></p>
<?php
}

// else bad input or captcha
if ($bError) { ?>
<h3 class="usererror">Registration error</h3>
<p>Check your entered information or contact us for assistance. It is either incomplete or has an identified security problem.</p>
<p><a href="register-form.php">Back</a></p>
<?php
}
include('footer.php'); ?>
</body>
</html>
