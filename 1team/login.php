<?php include_once('utilsbase.php');
// Pull Request Parameters
$bError = false;
if (isset($_POST['login'])) {
	$formLogin = $_POST['login'];
	$formLogin = cleanSQL($formLogin);
} else {
	redirect("login-form.php?b=1");
}
if (isset($_POST['password'])) {
	$formPassword = $_POST['password'];
} else {
	redirect("login-form.php?c=2");
}
if ((isset($_POST['gotourl'])) && (is_url($_POST["gotourl"]))) {
	$formURL = $_POST['gotourl'];
} else {
	$formURL = "";
}

// Only let active members on active teams login. Get the team status to push the user to license screen if they have not accepted license yet.
//$strSQL = "SELECT teamaccountinfo.status as teamstatus, users.teamid as team_id, users.id as userid, users.* FROM users, useraccountinfo, teamaccountinfo WHERE users.useraccountinfo = useraccountinfo.id AND teamaccountinfo.teamid = users.teamid and useraccountinfo.status = " . UserAccountStatus_Active . " AND teamaccountinfo.status <> " . TeamAccountStatus_Inactive . " AND login = ?;";
$strSQL = "SELECT users.teamid as team_id, users.id as userid, users.*, teamaccountinfo.status as teamstatus FROM useraccountinfo, users LEFT OUTER JOIN teamaccountinfo ON (teamaccountinfo.teamid = users.teamid AND teamaccountinfo.status <> " . TeamAccountStatus_Inactive . ") WHERE users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status = " . UserAccountStatus_Active . " AND login = ?;";
$dbconn = getConnection();
$loginResults = executeQuery($dbconn, $strSQL, $bError, array($formLogin));

if ((! is_array($loginResults)) || (count($loginResults) != 1)) {
	$bError = true;
	redirect("login-form.php?e=".RC_LoginFailure);

} else {
	$rowCount = count( $loginResults);
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
		redirect("login-form.php?e=" . RC_IncorrectPassword);
	// Good password
	} else {
		$userid = (int) $loginResults[0]["userid"];
		$roleid = (int) $loginResults[0]["roleid"];
		$teamid = $loginResults[0]["team_id"];

		// Only allow application administrator login from team console
		if ((doesRoleContain($roleid, Role_ApplicationAdmin)) && (!isAdminSystem()) ){
			$bError = true;
			redirect("login-form.php?e=5");
		} else {
			// Create a new session row in DB
			$sessionkey = createSession( $userid, $roleid, $dbconn);
			if ((!isSuccessful($sessionkey)) || (!isSessionKeyValid($sessionkey))) {
				$bError = true;
				redirect("login-form.php?f=" . $sessionkey);
			} else {
				// If team admin hasn't accepted license agreement yet, force them to
				if ((!doesRoleContain($roleid, Role_ApplicationAdmin)) && ($loginResults[0]["teamstatus"] == TeamAccountStatus_PendingLicense)){
				     if (doesRoleContain($roleid, Role_TeamAdmin)){
				     	redirect("license-form.php?sessionkey=" . $sessionkey . "&userid=" . $userid );
					} else {
						$bError = true;
						redirect("login-form.php?e=" . RC_NoLicense);
					}
				// Only if there is a good license can we direct them to intended destination
				} else {
					// Make sure account isn't locked out due to 2fa auth retries failures
					// And make sure any team admin is logging in from the same address as last time
					if (doesRoleContain($roleid, Role_TeamAdmin)) {
						if (isLockedOut($userid, $teamid, $dbconn)){
							$bError = true;	// Not really an error, but avoid redirect to home
							redirect("login-form.php?l=" . RC_LoginFailAccountLocked);
						}
						else if (!isUserLocationRecognized($userid, $teamid, $_SERVER["REMOTE_HOST"], $dbconn)){
							$bError = true;	// Not really an error, but avoid redirect to home
							redirect("2fauthn-form.php?sessionkey=" . $sessionkey . "&userid=" . $userid);
						}
					}
					// TODO: $formURL is intended to support redirecting to a bookmarked page after authentication, but it doesn't work
					if (!$bError){
						if (strlen($formURL) > 0 ){
							redirect($formURL."?sessionkey=" . $sessionkey . "&userid=" . $userid);
						} else {
							// Finally: There's no place like home. Click your heels, Dorothy!
							redirect("home.php?sessionkey=" . $sessionkey . "&userid=" . $userid);
						}
					}
				}
			}
		}
	}
}

// Create Session - called on login to create or reuse a record in the session table, unless it has expired.
// This function doesn't return any session data. The record that this creates is read in by startSession.
// Returns < RC_Success on error
function createSession( $userid, $roleid, $dbconn = null){
	if ($dbconn == null){
		$dbconn = createConnection();
	}
	if (!isValidUserID($userid)) {
		return RC_UserID_Invalid;
	}

	// Generate a session key based on ipaddr and userid
	$ipaddr = (string) $_SERVER["REMOTE_ADDR"];

	if (($sessionkey = createSessionKey( $ipaddr, $userid)) == RC_SessionKey_Invalid) {
		return RC_SessionKey_Invalid;		// failure prevents login
	}

	// Note: trust the session key here. Usually, if passed from client, call isSessionKeyValid to verify

	// Store the session vars in session table - either creates or reuses an existing record
	$rc = saveSession( $sessionkey, $userid, $ipaddr, $dbconn);
	if (!isSuccessful($rc)) {
		return $rc;
	}

	setlocale(LC_MONETARY, 'en_US');
	return $sessionkey;
}

// Stores session vars in sessions table - may reuse an existing row, in which case it updates the expiration date
// This doesn't pull the values out into the array. use startSession for that
// Returns error code < 1 on error
function saveSession( $sessionkey, $userid, $ipaddr, $dbconn = null) {
	if ($dbconn == null){
		$dbconn = createConnection();
	}
	// Get user/useraccountinfo record so we can store in session record
	$strSQL = "SELECT * FROM users, useraccountinfo WHERE users.useraccountinfo = useraccountinfo.id AND users.id = ?;";
	$userloginResults = executeQuery($dbconn, $strSQL, $bError, array($userid));

	$rowCount = count( $userloginResults);
	// Save in temporary variables in the format friendly for a SQL insert/update statement
	if ( $rowCount == 1) {
		$login = $userloginResults[0]["login"];
		$fullname = $userloginResults[0]["firstname"] . " " . $userloginResults[0][ "lastname"];
		$teamid = ($userloginResults[0]["teamid"] == NULL) ? "NULL" : $userloginResults[0]["teamid"];
		$isbillable = ($userloginResults[0][ "isbillable"]) ? 1 : 0;
		$status = (int) $userloginResults[0][ "status"];
		$roleid = $userloginResults[0]["roleid"];

	// This represents a failure since the user should have been found, since they have passed the login by this point
	} else {
		return RC_UserNotFound_Failure;
	}

	// Session expiration is based on role
	$timeStart = time();
	// Since roles can be additive, test for most generous session length first
	if ((doesRoleContain($roleid, Role_TeamAdmin)) || (doesRoleContain($roleid, Role_ApplicationAdmin))) {
		$timeExpireSQL = "1 month";
		$timeExpire = time() + SessionExpiration_Month;
	} else if (doesRoleContain($roleid, Role_Coach)) {
		$timeExpireSQL = "2 hours";
		$timeExpire = time() + (2 * SessionExpiration_Hour);
	// Default to most restrictive
	} else {
		$timeExpireSQL = "1 hours";
		$timeExpire = time() + SessionExpiration_Hour;
	}

	// See if this user already has a session from this IP address (the key is based on address/userid)
	// We never blindly trust a sessionkey from a client. Use the isValidSessionKey in that case. In this case, we just generated this key
	// and know the IP address is correct
	$strSQL = "SELECT * FROM sessions WHERE sessionkey = ? AND userid = ?;";
	$sessionResults = executeQuery($dbconn, $strSQL, $bError, array($sessionkey, $userid));
	$rowCountSessions = count( $sessionResults);

	// If no results, create a session record
	if ($rowCountSessions == 0) {
		$strSQL = "INSERT INTO sessions VALUES (DEFAULT, ?, ?, ?, current_timestamp, (current_timestamp  + interval " . $timeExpireSQL . "), ?, ?, ?, ?, ?, ?, 0, 0);";
		executeQuery($dbconn, $strSQL, $bError, array($ipaddr, $userid, $sessionkey, $login, $roleid, $fullname, $teamid, $isbillable, $status));

	// Else get the session results
	} else {
		// Update the record. It's important to update all of this stuff in case a value changed since the last session update (e.g., "status")
		$strSQL = "UPDATE sessions SET timecreated = current_timestamp, timeexpires = (current_timestamp  + interval " . $timeExpireSQL . "), login = ?, roleid = ?, fullname = ?, teamid = ?, isbillable = ?, status = ?, authsms = 0, authsmsretries = 0 WHERE ipaddr = ? AND userid = ? AND teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($login, $roleid, $fullname, $teamid, $isbillable, $status, $ipaddr, $userid, $teamid));
	}

	return(RC_Success);
}

function isUserLocationRecognized( $userid, $teamid, $ipaddr, $dbconn = null){
	if ($dbconn == null){
		$dbconn = createConnection();
	}
	$strSQL = "SELECT * FROM recognizeduserlocations WHERE userid = ? AND teamid = ? AND ipaddr = ?;";
	$results = executeQuery($dbconn, $strSQL, $bError, array($userid, $teamid, $ipaddr));
	if (count($results) > 0) return true;
	else return false;
}?>
