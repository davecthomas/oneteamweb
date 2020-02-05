<?php include('globals.php'); 
include_once('utilsbase.php');
$title = "Sign In"; ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta name="keywords" content="1TeamWeb Team Management Sports Membership Clubs Site"/>
<meta name="description" content="1 Team Web: Focus On Your Team"/>
<title><?php echo $title . " to " . appname;?></title>
<link rel="stylesheet" type="text/css" href="1team.css"/>
<script type="text/javascript" src="/1team/utils.js"></script>
<link rel="icon" type="image/png" href="/1team/img/1teamweb-logo-200.png" />
</head>
<body>
<div id="wrapper"> 
<?php 
include('nav-notloggedin.php'); ?>
<h3><?php echo appname . " " . $title ?></h3>
<form name="loginform" action="/1team/login.php" method="post">
<div class="indented-group-noborder">
<table class="noborders">
<tr><td class="bold">User login</td><td><input type="text" name="login" value="" name="login"/></td></tr>
<tr><td class="bold">Password</td><td><input type="password"  name="password" maxlength="50"/></td></tr>
<tr><td colspan="2"><input type="submit" value="Login" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>&nbsp;
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'default.php'"/>
</td></tr>
</table>
</div>
<?php
// This section is for internal redirects due to someone bookmarking or entering a URL behind a protected login area
// We want to save the page they wanted to go to, send them to the login page, then send them to the page they wanted
if (isset($_GET['login-redirect'])) { ?>
<input type=hidden name="gotourl" value="<?php echo $_GET['login-redirect']?>" />
<?php
} ?>	
</form>
<?php 
if (isset($_GET["e"])){
	$err = $_GET["e"];
	if ($err == RC_NoLicense) $errstr = "A ". $teamterms["termadmin"]." is required to accept the Subscription License terms. Contact your team adminstrator for help.";
	else $errstr = "Unable to sign on with the given information." ;
	showError("Sign On Error", $errstr, "document.loginform.login.focus();");
} else if (isset($_GET["2fa"])){
	$err = $_GET["2fa"];
	$errstr = "There was a problem sending an authentication code to the ". $teamterms["termadmin"]." for sign-on. Contact ". appname.".";
	showError("Sign On Error", $errstr, "document.loginform.login.focus();");
} else if (isset($_GET["l"])){
	$err = $_GET["l"];
	$errstr = "Your account is locked due to failed authentication attempts. Contact ". appname.".";
	showError("Sign On Error", $errstr, "document.loginform.login.focus();");
}

include('footer.php'); ?>
</body>
</html>
