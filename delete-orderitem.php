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

$err = "";
$bError = false;

// teamid depends on who is calling 
if ( isUser($session, Role_TeamAdmin)){
	if ( !isset($session["teamid"])){
		$bError = true;
		$err = "A team must be selected.";
	} else {
		$teamid = $session["teamid"];
	}
} else {
	if ( isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	} else {
		$bError = true;
		$err = "A team must be selected.";
	}
}

if (isset($_GET["paymentid"])) {
	$delete_id= (int)(getCleanInput($_GET["paymentid"]));
} else {
	$bError = true;
	$err = "input";
} 	

if (isset($_GET["id"])) {
	$objid= $_GET["id"];
} else {
	$bError = true;
	$err = "input";
} 	

if (isset($_GET["whomode"])) {
	$whomode= $_GET["whomode"];
} else {
	$bError = true;
	$err = "input";
} 	

if (!$bError) {
	$dbh = getDBH($session);  
	$strSQL = "DELETE FROM orderitems WHERE id = ? AND teamid = ?;";
	$pdostatement = $dbh->prepare($strSQL);
	$bError = ! $pdostatement->execute(array($delete_id, $teamid));
	
	// Send them back 
	if (!$bError) {
		redirect("payment-history.php?" . returnRequiredParams($session) ."&teamid=" . $teamid . "&id=" . $objid . "&whomode=" . $whomode . "&done=1"); 
	}
} 
if ($bError) {
	redirect("payment-history.php?"  . returnRequiredParams($session) . "&teamid=" . $teamid . "&id=" . $objid . "&whomode=" . $whomode . "&err=" . $err); 
}?>
