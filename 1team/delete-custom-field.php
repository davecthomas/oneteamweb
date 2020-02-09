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
	$customfieldid = $_GET["id"];
} else {
	$bError = true;
}

if ($bError != true) {

	// Delete any custom data so we have no orphans
	$strSQL = "DELETE FROM customdata WHERE customfieldid = ? AND teamid = ?;";
	$dbconn = getConnection();
	$results = executeQuery($dbconn, $strSQL, array($customfieldid, $teamid));

	// Delete the custom field
	$strSQL = "DELETE FROM customfields WHERE id = ? AND teamid = ?;";
	$results = executeQuery($dbconn, $strSQL, array($customfieldid, $teamid));

	redirect("manage-custom-fields.php?" . returnRequiredParams($session) . "&teamid=" . $teamid );
} else {
	redirect("manage-custom-fields.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1");
}
