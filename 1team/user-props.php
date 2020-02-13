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
$bError = false;
if ( isset($_POST["id"])) {
	$userid = (int)(getCleanInput($_POST["id"]));
} else {
	$bError = true;
	$errstr = "i";
}
// teamid depends on who is calling
if (!isUser($session, Role_ApplicationAdmin)){
	if (isset($session["teamid"])){
		$teamid = $session["teamid"];
	} else {
	     $bError = true;
	     $errstr = "t";
	}
} else {
	if (isset($_POST["teamid"])){
		$teamid = $_POST["teamid"];
	} else {
		$bError = true;
		$errstr = "t";
	}
}

if ( !$bError) {


	$strSQL = "SELECT * FROM users, useraccountinfo WHERE users.useraccountinfo = useraccountinfo.id AND users.id = ? AND users.teamid = ?;";
	$dbconn = getConnection();
	$userResults = executeQuery($dbconn, $strSQL, $bError, array($userid, $teamid));

	if ($bError) $errstr = "s";
	else {
		$userResults = $pdostatement->fetchAll();

		$rowCount = 0;
		if (count($userResults) > 0) {
		     // If the input POST data isn't present, set the local value to the existing value in the DB
			if (isset($_POST["firstname"])) $firstname = getCleanInput($_POST["firstname"]); else $firstname = $userResults[0]["firstname"];
			if (isset($_POST["lastname"])) $lastname = getCleanInput($_POST["lastname"]); else $lastname = $userResults[0]["lastname"];
			if (isset($_POST["roleid"])) $currentroleid = $userResults[0]["roleid"]; else $roleid = $userResults[0]["roleid"];
			if (isset($_POST["address"])) $address = getCleanInput($_POST["address"]); else $address = $userResults[0]["address"];
			if (isset($_POST["city"])) $city = getCleanInput($_POST["city"]); else $city = $userResults[0]["city"];
			if (isset($_POST["state"])) $state = getCleanInput($_POST["state"]); else $state = $userResults[0]["state"];
			if (isset($_POST["postalcode"])) $postalcode = getCleanInput($_POST["postalcode"]); else $postalcode = $userResults[0]["postalcode"];
			if (isset($_POST["smsphone"])) $smsphone= getCleanInput($_POST["smsphone"]); else $smsphone = $userResults[0]["smsphone"];
			if (isset($_POST["smsphonecarrier"])) $smsphonecarrier= getCleanInput($_POST["smsphonecarrier"]); else $smsphonecarrier = $userResults[0]["smsphonecarrier"];
			if (isset($_POST["phone2"])) $phone2 = getCleanInput($_POST["phone2"]); else $phone2 = $userResults[0]["phone2"];
	  		if (isset($_POST["birthdate"])) $birthdate = getCleanInput($_POST["birthdate"]); else $birthdate = $userResults[0]["birthdate"];
			if (isset($_POST["emergencycontact"])) $emergencycontact= getCleanInput($_POST["emergencycontact"]); else $emergencycontact = $userResults[0]["emergencycontact"];
			if (isset($_POST["ecphone1"])) $ecphone1 = getCleanInput($_POST["ecphone1"]); else $ecphone1 = $userResults[0]["ecphone1"];
			if (isset($_POST["ecphone2"])) $ecphone2 = getCleanInput($_POST["ecphone2"]); else $ecphone2 = $userResults[0]["ecphone2"];
			if (isset($_POST["gender"])) $gender = getCleanInput($_POST["gender"]); else $gender = $userResults[0]["gender"];

			$strSQL = "UPDATE users SET firstname = ?, lastname = ?, address = ?, city = ?, state = ?, postalcode = ?, smsphone = ?, phone2 = ?, emergencycontact = ?, ecphone1 = ?, ecphone2 = ?, gender = ?, smsphonecarrier = ? WHERE id = ?;";
			executeQuery($dbconn, $strSQL, $bError, array($firstname, $lastname, $address, $city, $state, $postalcode, strval($smsphone), $phone2, $emergencycontact, $ecphone1, $ecphone2, $gender, $smsphonecarrier, $userid));

			// If admin, do another update for those fields
			// Only admins can change coach, referral, or notes
			if ( isAnyAdminLoggedIn( $session) ) {
				if (isset($_POST["roleid"])){
					$roleid = 0;	// Zero out bitfield
					$roleidposted = $_POST["roleid"];
					foreach ($roleidposted as $role){
						$roleid = $roleid | $role;	// Add roles to bitfield
					}
					$strSQL = "UPDATE users SET roleid = ? WHERE id = ? AND teamid = ?;";
					executeQuery($dbconn, $strSQL, $bError, array($roleid, $userid, $teamid));
				}

				if (( isset($_POST["startdate"])) && (strlen(getCleanInput($_POST["startdate"])) > 5 )) {
					$startdate = getCleanInput($_POST["startdate"]);
				} else {
					$startdate = $userResults[0]["startdate"];
				}
				// only a member has a coach and program
				if ( doesRoleContain($roleid, Role_Member )) {
					$coachid = getCleanInput($_POST["coachid"]);
				} else {
					$coachid = $userResults[0]["coachid"];
				}
				if (isset($_POST["referredby"])) $referredby = getCleanInput($_POST["referredby"]); else $referredby = $userResults[0]["referredby"];
				if (isset($_POST["notes"])) $notes = getCleanInput($_POST["notes"]); else $notes = $userResults[0]["notes"];
				if (isset($_POST["status"])) {
					$accountStatus = getCleanInput($_POST["status"]);
				}
				else {
//				     print_r($userResults[0]);
					$accountStatus  = $userResults[0]["status"];
				}

				// Stop date and reason only apply to an non-active member
				if ( $accountStatus == UserAccountStatus_Inactive ) {
					$stopdate = getCleanInput($_POST["stopdate"]);
					if (isValidDate($stopdate)) $stopdateSQL = $stopdate;
					else $stopdateSQL = date("Y-m-d");
					$stopreason = getCleanInput($_POST["stopreason"]);
					// only store stopdate and stopreason if they are inactive
					$strSQL = "UPDATE users SET startdate = ?, coachid = ?, referredby = ?, notes = ?, stopdate = '$stopdateSQL', stopreason = ? WHERE id = ?;";
					executeQuery($dbconn, $strSQL, $bError, array($startdate, $coachid, $referredby, $notes, $stopreason, $userid));
				} else {
					$strSQL = "UPDATE users SET startdate = ?, coachid = ?, referredby = ?, notes = ? WHERE id = ?;";
					executeQuery($dbconn, $strSQL, $bError, array($startdate, $coachid, $referredby, $notes, $userid));
				}
			}

			// Only app admin can change the login
			if ( isUser( $session, Role_ApplicationAdmin) ) {
				if ( strlen(getCleanInput($_POST["login"])) > 1 ) {
					$login = getCleanInput($_POST["login"]);
					// Only update if changed
					if (strcmp( $login, $userResults[0]["login"]) != 0) {
						$strSQL = "UPDATE users SET login = ? WHERE id = ?;";
						executeQuery($dbconn, $strSQL, $bError, array($login, $userid));
					}
				}
			}

			// Update account info
			$strSQL = "SELECT * from useraccountinfo WHERE $userid = ?";
			$userAccountinfo = executeQuery($dbconn, $strSQL, $bError, array($userid));

			if (count($userAccountinfo) > 0) {
				// All of this stuff is for admins only
				if ( isAnyAdminLoggedIn($session) ) {
					// if the status isn't active, don't bother with most fields
					if ( (UserAccountStatus_Active == $accountStatus) ) {
						$email = getCleanInput($_POST["email"]);

						if (isset($_POST["isbillable"])) $isbillable = getCleanInput($_POST["isbillable"]); else $isbillable = $userResults[0]["isbillable"];
						$strSQL = "UPDATE useraccountinfo SET email = ?, isbillable = ?, status = ? WHERE userid = ?;";
						executeQuery($dbconn, $strSQL, $bError, array($email, $isbillable, $accountStatus, $userid));

						// Else - account status not active so make sure we save status
					} else {
						$strSQL = "UPDATE useraccountinfo SET status = ? WHERE userid = ?;";
						executeQuery($dbconn, $strSQL, $bError, array($accountStatus, $userid));
					}
				// members can change email in useraccountinfo
				} else {
					$email = getCleanInput($_POST["email"]);
					$strSQL = "UPDATE useraccountinfo SET email = ? WHERE userid = ?;";
					executeQuery($dbconn, $strSQL, $bError, array($email, $userid));
				}

			}
		}
		if ( ! isUser( $session, Role_Member )) {
		     if ($bError)
				redirect("user-props-form.php?teamid=" . $teamid . "&id=" . $userid . "&" . returnRequiredParams($session)."&err=".$errstr);
			else
				redirect("user-props-form.php?teamid=" . $teamid . "&id=" . $userid . "&" . returnRequiredParams($session)."&done=1");
		} else {
		     if ($bError)
				redirect("home.php?".returnRequiredParams($session)."&err=".$errstr);
			else
				redirect("home.php?".returnRequiredParams($session)."&done=1");
		}

	}
} else {
	redirect("home.php?".returnRequiredParams($session)."&err=".$errstr);
}

?>
