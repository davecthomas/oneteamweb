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


if (isset($_POST["customlistorder"])) {
	$customlistorder = explode(",", $_POST["customlistorder"]);
	if (count($customlistorder) < 1) {
		$bError = true;
	}
}

if (isset($_POST["customlistid"])) {
	$customlistid = $_POST["customlistid"];
} else {
	$bError = true;
}

if (!$bError) {


	for ($i = 0; $i < count($customlistorder); $i++){
		$strSQL = "UPDATE customlistdata SET listorder = ? WHERE id = ? AND customlistid = ? AND teamid = ?;";
		executeQuery( getConnectionFromSession($session), $strSQL, $bError, array($i+1, $customlistorder[$i], $customlistid, $teamid));
	}

	redirect("edit-custom-list-form.php?" .returnRequiredParams($session) . "&teamid=" . $teamid . "&done=1" . "&id=" . $customlistid);
} else {
	redirect("edit-custom-list-form.php?" .returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1" . "&id=" . $customlistid);
}
