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
// Only app admins can execute this script
redirectToLoginIfNot( $session, Role_ApplicationAdmin);

$bError = false;

if (isset($_REQUEST["id"])) {
	$teamid = $_REQUEST["id"];
} else {
	$bError = true;
	$err = "i";
}

if ($bError != true) {
	$strSQL = "DELETE FROM feedback WHERE teamid = ?;";
	$dbconn = getConnectionFromSession($session);
	executeQuery($dbconn, $strSQL, $bError, array($teamid)));

	if (!$bError) {
		$strSQL = "DELETE FROM attendanceconsoles WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=2");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM attendance WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=3");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM customdata WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=4");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM customfields WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=6");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM customlistdata WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=7");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM customlists WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=8");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM orderitems WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=9");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM epayments WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=10");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM events WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=11");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM images WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=12");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM levels WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=13");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM orders WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=14");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM paymentmethods WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=15");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM promotions WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=17");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM recognizeduserlocations WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=19");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM redemptioncards WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=20");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM sessions WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=21");
	}
	// skus must go before programs
	if (!$bError) {
		$strSQL = "DELETE FROM skus WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=18");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM programs WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=16");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM teamaccountinfo WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=22");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM teamterms WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=23");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM teams WHERE id = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=25");
	}
	// Users must be deleted before useraccountinfo
	if (!$bError) {
		$strSQL = "DELETE FROM users WHERE teamid = ?;";
		$executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=24");
	}
	if (!$bError) {
		$strSQL = "DELETE FROM useraccountinfo WHERE teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($teamid)));
		if ($bError) redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=1");
	}
	if (!$bError) {
		redirect("teams-roster.php?" . returnRequiredParams($session) . "&done=1");
	}
}
if ($bError) {
	redirect("teams-roster.php?" . returnRequiredParams($session) . "&err=".$err);
}?>
