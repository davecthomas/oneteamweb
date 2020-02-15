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
	$customlistid = $_GET["id"];
} else {
	$bError = true;
}

if ($bError != true) {
	// Find out if this custom list is in use anywhere. If so, fail the delete
	$strSQL = "SELECT name FROM customfields WHERE customlistid = ? AND teamid = ?;";
	$dbconn = getConnectionFromSession($session);
	$bError = false;
	$customfieldsResults = executeQuery($dbconn, $strSQL, $bError, array($customlistid, $teamid));
	if (count($customfieldsResults) > 0) {
		// error, redirect with name
		redirect("manage-custom-lists-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&errname=" . $customfieldsResults[0]["name"]);
	} else {

		// Delete any custom list data so we have no orphans
		$strSQL = "DELETE FROM customlistdata WHERE customlistid = ? AND teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($customlistid, $teamid));

		// Delete the custom list
		$strSQL = "DELETE FROM customlists WHERE id = ? AND teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($customlistid, $teamid));

		redirect("manage-custom-lists-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid );
	}
} else {
	redirect("manage-custom-lists-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1");
}
