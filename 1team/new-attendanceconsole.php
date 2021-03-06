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

if (isset($_POST["ip"])) {
	$ipaddress = $_POST["ip"];
} else {
	$bError = true;
}
if (isset($_POST["name"])) {
	$name = $_POST["name"];
} else {
	$bError = true;
}

if (!$bError) {
	$ac = new AttendanceConsole($session, DbObject::DbID_Undefined);
	$ac->init($name, $ipaddress);
	$ac->commit();
	
	redirect("manage-attendance-consoles-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&done=1");
} else {
	redirect("manage-attendance-consoles-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1");
}
