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

// Only admins can promote
if (!isAnyAdminLoggedIn($session)) {
	redirect("default.php");
}

$bError = false;
if ( isset($_POST["id"])) {
	$memberID = (int)(getCleanInput($_POST["id"]));
} else {
	$bError = true;
} 	
if ( isset($_POST["teamid"])) {
	$teamid = (int)(getCleanInput($_POST["teamid"]));
} else {
	$bError = true;
} 
if ( isset($_POST["level"])) {
	$levelID = (int)(getCleanInput($_POST["level"]));
} else {
	$bError = true;
} 
if ( isset($_POST["date"])) {
	$promotionDate = $_POST["date"];
	$promotionDatearray = explode("/", $promotionDate );
	$bError = checkdate($promotionDatearray[1], $promotionDatearray[2], $promotionDatearray[0]);
} else {
	$bError = true;
} 
if (!$bError) {
	if (promoteUser($session, $teamid, $memberID, $levelID, $promotionDate ) != RC_Success)
		$bError = true;
}

if ($bError) {
	redirect("promote-member-form.php?id=" . $memberID . "&teamid=" . $teamid . "&badpromo=1" . buildRequiredParamsConcat($session));
} else {
	redirect("include-promotions.php?id=" . $memberID . "&pagemode=standalone&mode=user&teamid=" . $teamid. "&done=1" . buildRequiredParamsConcat($session));
} ?>
