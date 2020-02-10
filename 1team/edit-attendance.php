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

if (isset($_POST["memberid"])) {
	$userid = $_POST["memberid"];
} else {
	$bError = true;
}

if (isset($_POST["objname"])) {
	$objname = $_POST["objname"];
} else {
	$bError = true;
}
if (isset($_POST["attendanceid"])) {
	$attendanceid = $_POST["attendanceid"];
} else {
	$bError = true;
}
if (isset($_POST["attendancedate"])) {
	$attendancedate = $_POST["attendancedate"];
} else {
	$bError = true;
}

if (isset($_POST["eventid"])) {
	$eventid = $_POST["eventid"];
} else {
	$bError = true;
}

if (!$bError) {


	$strSQL = "UPDATE attendance SET attendancedate = ?, eventid = ? WHERE id = ? AND memberid = ? AND teamid = ?;";
	$dbconn = getConnection();
	executeQuery($dbconn, $strSQL, $bError, array($attendancedate, $eventid, $attendanceid, $userid, $teamid));

	$datetimeevent = new DateTime($attendancedate);

	redirect("include-attendance-table.php?" . returnRequiredParams($session) . "&mode=user&teamid=" . $teamid . "&id=" . $userid . "&pagemode=standalone&name=" . urlencode($objname) . "&EventDate=01-" . $datetimeevent->format("m") . "-" . $datetimeevent->format("Y") . "&done=1");
} else {
	redirect("include-attendance-table.php?" . returnRequiredParams($session) . "&mode=user&teamid=" . $teamid . "&id=" . $userid . "&pagemode=standalone&name=" . urlencode($objname) . "&EventDate=01-" . $datetimeevent->format("m") . "-" . $datetimeevent->format("Y") . "&err=1");
}?>
