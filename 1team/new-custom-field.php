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

if (isset($_POST["name"])) {
	$name = $_POST["name"];
} else {
	$bError = true;
}

if (isset($_POST["datatype"])) {
	$datatype = $_POST["datatype"];
} else {
	$bError = true;
}

if (isset($_POST["hasdisplaycondition"])) {
	$hasdisplaycondition = 'TRUE';
} else {
	$hasdisplaycondition = 'FALSE';
}

if (!$bError) {


	$strSQL = "INSERT INTO customfields VALUES (DEFAULT, ?, ?, ?, NULL, NULL, NULL, NULL, " . $hasdisplaycondition . ", NULL, NULL);";
	$dbconn = getConnection();
	$results = executeQuery($dbconn, $strSQL, $bError, array($datatype, $name, $teamid, $hasdisplaycondition));
	if (! $bError) {

		// Get the new id and send them to the edit form for more details
		$strSQL = "SELECT id from customfields WHERE name = ? AND teamid = ?;";
		$customfieldsResults = executeQuery($dbconn, $strSQL, $bError, array($name, $teamid));

		if (count($customfieldsResults) > 0) {
			redirect("edit-custom-field-form.php?" . returnRequiredParams($session) . "&id=" . $customfieldsResults[0]["id"] . "&teamid=" . $teamid . "&done=1");
		} else {
			$bError = true;
		}
	}
}
if ($bError == true) {
	redirect("manage-custom-fields.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1");
}
