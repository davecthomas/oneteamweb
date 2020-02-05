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
	$eventid = $_POST["id"];
} else {
	$bError = true;
}

if (isset($_POST["eventdate"])) {
	$eventdate = $_POST["eventdate"];
	if (strlen( $eventdate) < 1) $eventdate = 0;
} else {
	$eventdate = date("m-d-Y");
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
if (isset($_POST["programid"])) {
	$programid = $_POST["programid"];
} else {
	$bError = true;
}

if (isset($_POST["scannable"])){
	$scannable = "TRUE";
} else {
	$scannable = "FALSE";
}
if (!$bError) {
	$dbh = getDBH($session);  
	
	if 	($eventdate == 0) {
		$strSQL = "UPDATE events SET name = ?, location = ?, scannable = " . $scannable . ", programid = ? WHERE id = ? AND teamid = ? ;";
		$pdostatement = $dbh->prepare($strSQL);
		$pdostatement->execute(array($eventname, $location, $programid, $eventid, $teamid));
		
	} else {
		$strSQL = "UPDATE events SET name = ?, eventdate = ?, location = ?, scannable = " . $scannable . ", programid = ?  WHERE id = ? AND teamid = ?;";
		$pdostatement = $dbh->prepare($strSQL);
		$pdostatement->execute(array($eventname, $eventdate, $location, $programid, $eventid, $teamid));
	}
	redirect("manage-events-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&done=1");
} else {
	redirect("manage-events-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1");
}