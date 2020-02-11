<?php
include ('utils.php');

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
$errno = 0;

// teamid depends on who is calling
if (isUser($session, Role_TeamAdmin)){
	if (isset($session["teamid"])){
		$teamid = $session["teamid"];
	}
} else {
	if (isset($_POST["id"])){
		$teamid = $_POST["id"];
	} else {
		$bError = true;
		$errno = "teamid";
	}
}

// The checkbox for accepted must be selected.
if (isset($_REQUEST["accepted"])) {
	$accepted = true;
} else {
	$bError = true;
	$errno = "noaccept";
}

if (!$bError) {


	// First, make sure the team is actually currently TeamAccountStatus_PendingLicense. Otherwise, this could create a status reset back door for overdue customers!
	$strSQL = "SELECT COUNT(*) FROM teamaccountinfo WHERE status = ? AND id = ?";
	$dbconn = getConnection();
	$count_results = executeQueryFetchColumn($dbconn, $strSQL, $bError, array(TeamAccountStatus_PendingLicense, $teamid));
	// Update the teamaccountinfo to make the team active
	if ($count_results == 1) {
		$strSQL = "UPDATE teamaccountinfo SET status = ? WHERE id = ?";
		executeQuery($dbconn, $strSQL, $bError, array(TeamAccountStatus_Active, $teamid));
		redirect("home.php?".returnRequiredParams($session));
	} else {
		redirect($_SERVER['HTTP_REFERER']."&err=s");
	}
}

if ($bError) {
	redirect($_SERVER['HTTP_REFERER']."&err=".$errno);
}
 ?>
