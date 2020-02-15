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

if (isset($_POST["eventdate"])) {
	$eventdate = $_POST["eventdate"];
} else {
	$eventdate = 0;
}
if (isset($_POST["name"])) {
	$eventname = $_POST["name"];
} else {
	$bError = true;
}
if (isset($_POST["location"])) {
	$location = $_POST["location"];
} else {
	$bError = true;
}

if (!$bError) {

	$dbconn = getConnectionFromSession($session);

	if ($eventdate == 0) {
		$strSQL = "INSERT INTO events VALUES (DEFAULT, ?, NULL, ?, NULL, ?);";
		executeQuery($dbconn, $strSQL, $bError, array($eventname, $location, $teamid));
	} else {
		$strSQL = "INSERT INTO events VALUES (DEFAULT, ?, ?, ?, NULL, ?);";
		executeQuery($dbconn, $strSQL, $bError, array($eventname, $eventdate, $location, $teamid));
	}

	redirect("manage-events-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&done=1");
} else {
	redirect("manage-events-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1");
}
