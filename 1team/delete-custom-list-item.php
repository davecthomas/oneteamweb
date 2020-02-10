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
	$customlistitemid = $_GET["id"];
} else {
	$bError = true;
}

if (isset($_GET["customlistid"])) {
	$customlistid = $_GET["customlistid"];
} else {
	$bError = true;
}

if ($bError != true) {
	// Find out if this custom list item is in use anywhere. If so, fail the delete
	$strSQL = "SELECT name FROM customdata, customfields WHERE customfields.id = customdata.customfieldid AND customlistid = ? AND valuelist = ? AND customfields.teamid = ?;";
	$dbconn = getConnection();
	$customfieldsResults = executeQuery($dbconn, $strSQL, $bError, array($customlistid, $customlistitemid, $teamid));
	if (count($customfieldsResults) > 0) {
		// error, redirect with name
		redirect("manage-custom-lists-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&errlistitem=" . $customfieldsResults[0]["name"]);
	} else {
		// Delete any custom list data so we have no orphans
		$strSQL = "DELETE FROM customlistdata WHERE id = ? AND teamid = ?;";
		$results = executeQuery($dbconn, $strSQL, $bError, array($customlistitemid, $teamid));

		redirect("edit-custom-list-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&id=" . $customlistid);
	}
} else {
	redirect("edit-custom-list-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&id=" . $customlistid . "&err=1");
}
