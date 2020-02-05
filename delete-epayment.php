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
	if (isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	}  
}

if (isset($_GET["id"])) { 
	$epaymentid = $_GET["id"];
} else {
	$bError = true;
}

if ($bError != true) {
	$dbh = getDBH($session);  
	
	$strSQL = "DELETE FROM epayments WHERE id = ? AND teamid = ?;"; 
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array($epaymentid, $teamid));
	
	redirect("epayment-reconcile-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&done=1");
} else {
	redirect("epayment-reconcile-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1");
}