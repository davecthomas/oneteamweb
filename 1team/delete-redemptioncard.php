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
$dbconn = getConnectionFromSession($session);

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
	$redemptioncardid = $_GET["id"];
} else {
	$bError = true;
}

if ($bError != true) {
	if ($redemptioncardid != RedemptionCardID_All){
		$strSQL = "DELETE FROM redemptioncards WHERE id = ? AND teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($redemptioncardid, $teamid));
	// Special request to delete all expired cards
	} else {
		$strSQL = "DELETE FROM redemptioncards WHERE (expires < current_date OR numeventsremaining = 0) AND teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid));
	}
	redirectToReferrer( "&done=1");
} else {
	redirectToReferrer(  "&err=1");
}
