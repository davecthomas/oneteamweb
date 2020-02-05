<?php
// Only admins can execute this script. Header.php enforces this.
$title= " Verify Your Identity" ;
include_once('header-minimal.php'); ?>
<body class="<?php echo dojostyle?>">
<div id="wrapper">
<?php include('nav-notloggedin.php'); ?>
<?php
// This is a friendly reminder that if (we are running on the staging server that we should be cautious
// with any writes to the DB.
if (isStagingServer() ) {?>
<div id="staging">
Beta Test:<br/><?php echo appname . " version " . appversion?><br /><a href="http://<?php echo stagingserveraddr?>/1team">Staging server</a><br /> using WWW production DB.
</div>
<?php
} ?>
<h3><?php echo $title?></h3>
<?php
$bError = FALSE;
if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid = getUserID()) == RC_RequiredInputMissing)) {
	redirect("default.php?rc=" . RC_RequiredInputMissing);
}
// Get $session array initialized
$session = startSession($sessionkey, $userid);
if (! isValidSession($session )) {
	redirect("default.php?rc=" . $session);
}
// Only admins can execute this script
redirectToLoginIfNotAdmin( $session);

if ((!isset($_GET["err"])) && (!isset($_GET["done"]))){

	$dbh = getDBH($session);
	// Get the retry count to decide what text to include
	$strSQL = "SELECT authsmsretries FROM sessions WHERE userid = ? and login = ? and teamid = ?;";
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array($session["userid"], $session["login"], $session["teamid"]));
	$authsmsretries = $pdostatement->fetchColumn();
	if ($authsmsretries >= authsmsretries_Max){
		// Penalty box! TODO: lock the account for 5 minutes
		$timeoutpenalty = authsmsfailure_LockoutPenalty;
		$strSQL = "UPDATE users SET timelockoutexpires = (current_timestamp  + cast('" . $timeoutpenalty . "' as interval)) WHERE id = ? and teamid = ?;";
		$pdostatement = $dbh->prepare($strSQL);
		$pdostatement->execute(array($session["userid"], $session["teamid"]));

		// Log off user
		deleteSession($session);?>

<p>Please contact <?php echo appname ?> for assistance accessing your account.</p>
<p><a href="default.php">Back</p>
<?php
	} else {
		// Generate an SMS code to the current session user
		$bError = generate2fauthn( $session, $err); 
		if (!$bError) {?>
<p>You are signed on as a <?php echo $teamterms["termadmin"]?> from an unrecognized location.
You will shortly receive a text message to your account mobile phone number with an authentication code. </p>
<form action="/1team/2fauthn.php" method="post">
<?php buildRequiredPostFields($session) ?>
<p>Please enter the authentication code you receive:&nbsp;<input type="text" value="" name="code" ></p>
<p><input type="checkbox" name="publiclocation">&nbsp;I am signing in from a public computer.</p>
<input type="submit" value="Authorize" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
</form>
<div class="helpbox">
<div class="helpboxtitle"><h5><a class="linkopacity" href="javascript:togglevis('overview')">Why am I being asked this?<img src="/1team/img/a_expand.gif" id="overview_img" border="0" alt="expand region"></a></h5></div>
<div class="hideit" id="overview">
<div class="helpboxtext">
<p>As a <?php echo $teamterms["termadmin"]?>, you have access to important information about your business and customers. In order to protect your business
information from fraudulent access by unauthorized users, <?php echo appname?> will send you a text message code if you ever attempt to sign on from an unrecognized location.
This secondary code is a further verification of your identity that is more secure from your password alone.
<p/>
<p>Once you successfully sign in from this location and provide the code, you will not be required to provide this secondary code from the same location in the future.
If you sign in from a public computer location that is accessed by many other people, such as at a library, check the box above that indicates this.
<?php echo appname?> will never store a public location as recognized.</p></div></div>
<div class="push"></div>
</div>
<?php
		} else {
			redirect("login-form.php?2fa=". $err);
		}
	}

}

// If the user enters the wrong code, they get redirected back here to try again
// TODO: add code to support 3 retries
if (isset($_GET["err"])){
	showError("Error", "Incorrect code: " . $_GET["err"], ""); ?>
<form action="/1team/2fauthn-form.php" method="post">
<?php buildRequiredPostFields($session) ?>
<p>Try again?</p>
<input type="submit" value="Generate another" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
</form>
<?php
}
include('footer.php'); ?>
</body>
</html>
