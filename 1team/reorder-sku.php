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


if (isset($_POST["skuorder"])) {
	$skuorder = explode(",", $_POST["skuorder"]);
//	print_r( $skuorder );
	if (count($skuorder) < 1) { 
		$bError = true;
	} 
}

if (!$bError) {
	$dbh = getDBH($session);  

	for ($i = 0; $i < count($skuorder); $i++){	
		$strSQL = "UPDATE skus SET listorder = ? WHERE id = ? AND teamid = ?;";
		$pdostatement = $dbh->prepare($strSQL);
		// The +1 is to force sku orders to start at 1, not 0
		$pdostatement->execute(array($i+1, $skuorder[$i], $teamid));
	}
	
	redirect("manage-skus-form.php?" .returnRequiredParams($session) . "&teamid=" . $teamid . "&done=1");
} else {
	redirect("manage-skus-form.php?" .returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1");
}