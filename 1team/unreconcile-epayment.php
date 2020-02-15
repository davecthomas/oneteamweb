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
	if (isset($_REQUEST["teamid"])){
		$teamid = $_REQUEST["teamid"];
	} else {
		$bError = true;
		$err = "tid";
	}
}

if (isset($_REQUEST["id"])) {
	$epaymentid = $_REQUEST["id"];
} else {
	$bError = true;
	$err = "pid";
}

// Verify this epayment exists
if (!$bError) {
	$strSQL = "SELECT * from epayments WHERE id = ? AND teamid = ?";
	$resultsEpayments = executeQuery( getConnectionFromSession($session), $strSQL, $bError, array($epaymentid, $teamid));
	if ($bError) {
		$err = "epn";
	} else {
		if (count($resultsEpayments) != 1){
			$bError = true;
			$err = "pnf";
		}
	}
}

if (!$bError) {
	$strSQL = "UPDATE epayments SET reconciled = FALSE WHERE id = ? AND teamid = ?;";
	executeQuery( getConnectionFromSession($session), $strSQL, $bError, array($epaymentid, $teamid));
	$err = "s";
}
if (!$bError){
	redirect("epayment-reconcile-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&done=1");
} else {
	redirect("epayment-reconcile-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1&rc=" . $err);
} ?>
