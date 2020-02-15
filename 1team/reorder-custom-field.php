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


if (isset($_POST["customfieldorder"])) {
	$customfieldorder = explode(",", $_POST["customfieldorder"]);
	if (count($customfieldorder) < 1) {
		$bError = true;
	}
}

if (!$bError) {

//	print_r($customfieldorder);
	for ($i = 0; $i < count($customfieldorder); $i++){
		$strSQL = "UPDATE customfields SET listorder = ? WHERE id = ? AND teamid = ?;";
		executeQuery( getConnectionFromSession($session), $strSQL, $bError, array($i+1, $customfieldorder[$i], $teamid));
	}

	redirect("manage-custom-fields.php?" .returnRequiredParams($session) . "&teamid=" . $teamid . "&done=1");
} else {
	redirect("manage-custom-fields.php?" .returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1");
}
