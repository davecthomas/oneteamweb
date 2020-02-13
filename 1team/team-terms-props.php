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
		$errno = "teamid";
	}
}

if (!$bError) {


	$strSQL = "SELECT * FROM teams WHERE id = ?;";
	$dbconn = getConnection();
	$teamResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));

	$rowCount = 0;
	if (count($teamResults) > 0) {
		if ( isset($_POST["termteam"])) {
			$termteam = $_POST["termteam"];
		} else {
			$bError = true;
			$errno = "team";
		}
		if ( isset($_POST["termadmin"])) {
			$termadmin = $_POST["termadmin"];
		} else {
			$bError = true;
			$errno = "admin";
		}
		if ( isset($_POST["termcoach"])) {
			$termcoach = $_POST["termcoach"];
		} else {
			$errno = "coach";
			$bError = true;
		}
		if ( isset($_POST["termmember"])) {
			$termmember = $_POST["termmember"];
		} else {
			$errno = "member";
			$bError = true;
		}
		if ( isset($_POST["termclass"])) {
			$termclass = $_POST["termclass"];
		} else {
			$errno = "class";
			$bError = true;
		}

		if (!$bError) {
			// May need to create or update depending on if this is the first time or not
			$strSQL = "SELECT id FROM teamterms WHERE teamid = ?";
			$teamtermsResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));

			$rowCount = 0;
			if (count($teamtermsResults ) == 0) {
				$strSQL = "INSERT INTO teamterms VALUES (DEFAULT, ?, NULL, ?, ?, ?, ?, ?);";
				executeQuery($dbconn, $strSQL, $bError, array($termteam, $termadmin, $termcoach, $termmember, $teamid, $termclass));
			} else {
				$strSQL = "UPDATE teamterms SET termteam = ?, termadmin = ?, termcoach = ?, termmember = ?, termclass = ? WHERE teamid = ?";
				executeQuery($dbconn, $strSQL, $bError, array($termteam, $termadmin, $termcoach, $termmember, $termclass, $teamid));
			}

			if (!$bError) redirect("team-props-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&done=1");
		}
	}
}

if ($bError) {
	redirect("team-props-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=" . $errno);
}?>
