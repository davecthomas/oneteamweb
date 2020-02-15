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

if (!isUser( $session, Role_ApplicationAdmin )) {
	$teamid = $session["teamid"];
} else {
	if (isset($_GET["teamid"])) {
		$teamid = $_GET["teamid"];
	} else {
		redirect($_SERVER['HTTP_REFERER'] . "&err=t");
	}
}
// Promotion ID
if ( isset($_GET["id"])) {
	$promotionid = getCleanInput($_GET["id"]);
} else {
	$bError = true;
}

// Member ID of promotion holder
if ( isset($_GET["memberid"])) {
	$memberid = getCleanInput($_GET["memberid"]);
} else {
	$bError = true;
}

// This is so we can go back to the same pagemode we came from
if ( isset($_GET["pagemode"])) {
	$pagemode = getCleanInput($_GET["pagemode"]);
} else {
	$pagemode = "standalone";
}

if (!$bError) {
	$strSQL = "DELETE FROM promotions WHERE memberid = ? AND id = ? AND teamid = ?";
	$dbconn = getConnectionFromSession($session);
	executeQuery($dbconn, $strSQL, $bError, array($memberid, $promotionid, $teamid ));
	redirect("include-promotions.php?id=" . $memberid . "&pagemode=" . $pagemode . "&whomode=user&teamid=" . $teamid . buildRequiredParamsConcat($session) );
} else {
	redirect("include-promotions.php?id=" . $memberid . "&pagemode=" . $pagemode . "&whomode=user&teamid=" . $teamid . "&baddel=1" . buildRequiredParamsConcat($session));
}
