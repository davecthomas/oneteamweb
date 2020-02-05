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
		$err = "teamid";
	}
}

if (!$bError) {
	$dbh = getDBH($session);  
	
	$strSQL = "SELECT * FROM teamaccountinfo WHERE teamid = ?;";
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array($teamid));

	$teamResults = $pdostatement->fetchAll();
	
	$rowCount = 0;
	if (count($teamResults) > 0) {
		if (isset($_POST["status"])) {
			$status = $_POST["status"];
		} else {
			$bError = true;
			$err = "s";
		}
		if (isset($_POST["plan"])) {
			$plan = $_POST["plan"];
		} else {
			$bError = true;
			$err = "p";
		}
		if (isset($_POST["planduration"])) {
			$planduration = $_POST["planduration"];
		} else {
			$bError = true;
			$err = "pd";
		}
		// This is backed by a checkbox, which is only set in POST if checked.
		if (isset($_POST["isbillable"])) {
			$isbillable = 'TRUE';
			
		} else {
			$isbillable = 'FALSE';
		}
		if (!$bError){
			$strSQL = "UPDATE teamaccountinfo SET status = ?, plan = ?, planduration = ?, isbillable = " . $isbillable . " WHERE teamid = ?;";
			$pdostatement = $dbh->prepare($strSQL);
			$bError = ! $pdostatement->execute(array($status, $plan, $planduration, $teamid));
			
			if (!$bError){
				redirect("team-props-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&done=1");
			} else {
     			$err = "q";
			}
			
		}
	} else {
		$bError = true;
		$err = "no";
	}
}
if ($bError) {
	redirect("team-props-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=" . $err);
}
