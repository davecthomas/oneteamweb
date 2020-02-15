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
	$paymenttypeid = $_GET["id"];
} else {
	$bError = true;
}

if ($bError != true) {
	$strSQL = "DELETE FROM paymentmethods WHERE id = ? AND teamid = ?;";

	$dbconn = getConnectionFromSession($session);
	executeQuery($dbconn, $strSQL, $bError, array($paymenttypeid, $teamid));

	// Change any orderitems with this type to undefined so there are no orphans
	$strSQL = "UPDATE orderitems SET paymentmethod = ? where paymentmethod = ? and teamid = ?";
	executeQuery($dbconn, $strSQL, $bError, array(PaymentMethod_Undefined, $paymenttypeid, $teamid));

	redirect("manage-payment-types-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&done=1");
} else {
	redirect("manage-payment-types-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1");
}
