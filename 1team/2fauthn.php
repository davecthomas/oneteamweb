<?php
// Verify the code entered by the user in the form matches the stored auth code in the session
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

// Only admins 
if (!isAnyAdminLoggedIn($session)) {
	redirect("default.php");
}

$bError = false;

if (!isUser( $session, Role_ApplicationAdmin )) {
	$teamid = $session["teamid"];
} else {
	if (isset($_POST["teamid"])) {
		$teamid = $_POST["teamid"];
	} else {
		$bError = true;
		$err = "t";
	}
}

if ((isset($_POST["code"])) && (is_numeric($_POST["code"])) && (is_int((int)$_POST["code"]))) {
	$code = (int)$_POST["code"];
} else {
	$bError = true;
	$err="c";
	if (isset($_POST["code"])) {
		$err .=$_POST["code"];
		if (!is_numeric($_POST["code"])) $err .= "noi";
	}
}

if (isset($_POST["publiclocation"])){
	$publiclocation = TRUE;
} else {
	$publiclocation = FALSE;
}

if (!$bError){
	// Verify the session has the code stored in it
	$dbh = getDBH($session);
	$strSQL = "SELECT count(*) FROM sessions WHERE authsms = ? and userid = ? and login = ? and teamid = ?;";
	$pdostatement = $dbh->prepare($strSQL);
	if ($pdostatement->execute(array($code, $session["userid"], $session["login"], $session["teamid"]))){
//		print_r(array($code, $session["userid"], $session["login"], $session["teamid"]));
		$num = $pdostatement->fetchColumn();
		if ($num == 1){
			// Good code! Happy day, they can log in.
			// Update the session to reset retries
			$strSQL = "UPDATE sessions SET authsmsretries = 0 WHERE userid = ? AND login = ? and teamid = ?";
			$pdostatement = $dbh->prepare($strSQL);
			$pdostatement->execute(array($session["userid"], $session["login"], $session["teamid"]));
			// If not a public location,
			// update the recognizeduserlocations table to have the new IP address so we don't harrass them next time
			if ($publiclocation != TRUE){
				$strSQL = "INSERT INTO recognizeduserlocations VALUES(DEFAULT, ?, ?, ?)";
				$pdostatement = $dbh->prepare($strSQL);
				$pdostatement->execute(array($session["userid"], $session["teamid"], $_SERVER["REMOTE_ADDR"]));
			}
			// Finally: There's no place like home. Click your heels, Dorothy!
			redirect("home.php?sessionkey=" . $sessionkey . "&userid=" . $userid);
		} else {
			$bError = true;
			$err = "";	// No error code since they got the code wrong. No hints for cheaters! :)
			// Get the retry count
			$strSQL = "SELECT authsmsretries FROM sessions WHERE userid = ? and login = ? and teamid = ?;";
			$pdostatement = $dbh->prepare($strSQL);
			$pdostatement->execute(array($session["userid"], $session["login"], $session["teamid"]));
			$authsmsretries = $pdostatement->fetchColumn();
			if (empty($authsmsretries)) $authsmsretries = 1;
			else $authsmsretries++;
			// Before we redirect back on incorrect code, update the retries count
			// Update the session to have the new IP address so we don't harrass them next time
			$strSQL = "UPDATE sessions SET authsmsretries = ? WHERE userid = ? AND login = ? and teamid = ?";
			$pdostatement = $dbh->prepare($strSQL);
			$pdostatement->execute(array($authsmsretries, $session["userid"], $session["login"], $session["teamid"]));
		}
	} else {
		$bError = true;
		$err = RC_PDO_Error;
	}
}
if ($bError)
	redirect("2fauthn-form.php?".returnRequiredParams($session)."&err=$err");
//	echo "ERR=".$err;
?>