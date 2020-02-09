<?php
include('utils.php');
$bError = false;
$err="";
// Assure we have the input we need, else send them to default.php
if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid = getUserID()) == RC_RequiredInputMissing)){
	$bError = true;
	$err = "s";
} else {
	// Get $session array initialized
	$session = startSession($sessionkey, $userid);
	if (! isValidSession($session )){
		$bError = true;
		$err = "s2";
	} else {
		// Only admins can execute this script
		if (!isAnyAdminLoggedIn( $session)){
			$bError = true;
			$err = "a";
		} else {
			// Pull Request Parameters
			if (isset($_REQUEST['p'])) {
				$formPassword = $_REQUEST['p'];
			} else {
				$bError = true;
				$err = "p";
			}
			if (isset($_REQUEST['uid'])) {
				$uid = $_REQUEST['uid'];
			} else {
				$uid = $session["userid"];
			}
		}
	}
}

if (!$bError) {
	// Only let active members on active teams login. Get the team status to push the user to license screen if they have not accepted license yet.
	//$strSQL = "SELECT teamaccountinfo.status as teamstatus, users.teamid as team_id, users.id as userid, users.* FROM users, useraccountinfo, teamaccountinfo WHERE users.useraccountinfo = useraccountinfo.id AND teamaccountinfo.teamid = users.teamid and useraccountinfo.status = " . UserAccountStatus_Active . " AND teamaccountinfo.status <> " . TeamAccountStatus_Inactive . " AND login = ?;";
	$strSQL = "SELECT users.salt, users.passwd FROM users WHERE id = ?;";
	$dbconn = getConnection();
	$loginResults = executeQuery($dbconn, $strSQL, array($uid));
	$rowCount = count( $loginResults);
	if ($rowCount != 1) {
		$bError = true;
		$err = "i";
	} else {
		// Pull out the password and salt from the db
		$salt = $loginResults[0]["salt"];
		$passwordenc_db = $loginResults[0]["passwd"];
		// Get hashed version with salt
		$passwordencsalted_form = generateHash($formPassword, $salt);
		// Remove salt
		$passwordenc_nosalt_form = substr($passwordencsalted_form, SALT_LENGTH);
		// Chop down to PASSWORD_LENGTH
		$passwordenc_form = substr($passwordenc_nosalt_form, 0, PASSWORD_LENGTH);

		// Bad password, go to login form
		if ((strcmp($passwordenc_db, $passwordenc_form)) != 0 ) {
			$bError = true;
			$err = "Incorrect credentials";
		// Good password - return userid
		} else {
			echo $userid;
		}
	}
}
// Error - return code
if ($bError) echo htmlspecialchars($err);
 ?>
