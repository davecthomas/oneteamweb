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

$err = "";
$bError = false;

// teamid depends on who is calling
if ( isUser($session, Role_TeamAdmin)){
	if ( !isset($session["teamid"])){
		$bError = true;
		$err = "ti";
	} else {
		$teamid = $session["teamid"];
	}
} else {
	if ( isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	} else {
		$bError = true;
		$err = "ti";
	}
}

if (isset($_GET["id"])) {
	$delete_id= (int)(getCleanInput($_GET["id"]));
} else {
	$bError = true;
	$err = "id";
}

if (!$bError) {
	// Remove orderitems
	$strSQL = "DELETE FROM orderitems WHERE orderid = ? AND teamid = ?;";
	$dbconn = getConnectionFromSession($session);
	executeQuery($dbconn, $strSQL, $bError, array($delete_id, $teamid));

	if (!$bError) {
		// Remove the order
		$strSQL = "DELETE FROM orders WHERE id = ? AND teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($delete_id, $teamid));
		// Send them back
		if (!$bError) {
			redirect("manage-orders-form.php?" . returnRequiredParams($session) ."&teamid=" . $teamid . "&done=1");
		}
	}
}
if ($bError) {
	redirect("manage-orders-form.php?" . returnRequiredParams($session) ."&teamid=" . $teamid . "&err=". $err);
}?>
