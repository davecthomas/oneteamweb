<?php   
include('utils.php');
// Assure we have the input we need, else send them to default.php
if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid = getUserID()) == RC_RequiredInputMissing)){
	redirect("default.php?rc=" . RC_RequiredInputMissing);
}
// Get $session array initialized
$session = startSession($sessionkey, $userid);
if (! isValidSession($session )){
	redirect("default.php?rc=" . $session);
}
// Only admins can execute this script
redirectToLoginIfNotAdmin( $session);

$bError = false;

// teamid depends on who is calling 
if (isUser($session, Role_TeamAdmin)){
	if (isset($session["teamid"])){
		$teamid = $session["teamid"];
	} 
} else {
	if (isset($_POST["teamid"])){
		$teamid = $_POST["teamid"];
	} 
}

if (isset($_POST["summary"])) {
	$summary = $_POST["summary"];
} else {
	$bError = true;
}
if (isset($_POST["details"])) {
	$details = $_POST["details"];
} else {
	$bError = true;
}

if (!$bError) {
	$dbh = getDBH($session);  
	
	$strSQL = "INSERT INTO feedback VALUES (DEFAULT, ?, ?, ?, ?, ?);";
	$pdostatement = $dbh->prepare($strSQL);
	$bError = ! $pdostatement->execute(array($summary, $details, $session["userid"], date("m-d-Y"), $teamid));
	
	if (!$bError){
		$mailok = mail(getAdminEmail($session, $dbh), appname . " : " . $teamname . " " . " Feedback", "User: " . getCurrentUserName($session) . "; Summary: " . $summary . "; Details " . $details, "From: " . getUserEmail($session, $dbh));
	
		redirect("feedback-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&done=1");
	}
} 
if ($bError) {
	redirect("feedback-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1");
}?>