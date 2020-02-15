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
	} else {
		$bError = true;
	}
}

if (isset($_POST["id"])) {
	$customlistid = $_POST["id"];
} else {
	$bError = true;
}

if (isset($_POST["name"])) {
	$customlistname = $_POST["name"];
} else {
	$bError = true;
}

if (!$bError) {


	$strSQL = "UPDATE customlists SET name = ? WHERE id = ? AND teamid = ?;";
	$dbconn = getConnectionFromSession($session);
	executeQuery($dbconn, $strSQL, $bError, array($customlistname, $customlistid, $teamid));

	redirect("edit-custom-list-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&id=" . $customlistid ."&done=1");
} else {
	redirect("edit-custom-list-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&id=" . $customlistid . "&err=1");
}
