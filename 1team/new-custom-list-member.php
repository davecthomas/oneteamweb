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

if (isset($_POST["customlistid"])) {
	$customlistid = $_POST["customlistid"];
} else {
	$bError = true;
}

if (isset($_POST["listitemname"])) {
	$listitemname = $_POST["listitemname"];
} else {
	$bError = true;
}

if (isset($_POST["listitemorder"])) {
	$listitemorder = $_POST["listitemorder"];
} else {
	$listitemorder = 1000;
}

if (!$bError) {


	$strSQL = "INSERT INTO customlistdata VALUES (DEFAULT, ?, ?, ?, ? );";
	$dbconn = getConnectionFromSession($session);
	executeQuery($dbconn, $strSQL, $bError, array($customlistid, $listitemname, $listitemorder, $teamid));

	redirect("edit-custom-list-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&id=" . $customlistid . "&done=1");
} else {
	redirect("edit-custom-list-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1");
}
