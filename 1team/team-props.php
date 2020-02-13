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
		if ( isset($_POST["teamname"])) {
			$teamname = $_POST["teamname"];
		} else {
			$teamname = "Unnamed team";
		}
		if ( isset($_POST["city"])) {
			$city = $_POST["city"];
		} else {
			$city = "";
		}
		if ( isset($_POST["state"])) {
			$state = $_POST["state"];
		} else {
			$state = "";
		}
		if ( isset($_POST["address1"])) {
			$address1 = $_POST["address1"];
		} else {
			$address1 = "";
		}
		if ( isset($_POST["address2"])) {
			$address2 = $_POST["address2"];
		} else {
			$address2 = "";
		}
		if ( isset($_POST["postalcode"])) {
			$postalcode = $_POST["postalcode"];
		} else {
			$postalcode = "";
		}
		if ( isset($_POST["email"])) {
			$email = $_POST["email"];
		} else {
			$email = "";
		}
		if ( isset($_POST["coachid"])) {
			$coachid = $_POST["coachid"];
		} else {
			$coachid = User::UserID_Undefined;
		}
		if ( isset($_POST["phone"])) {
			$phone = $_POST["phone"];
		} else {
			$phone = "";
		}
		if ( isset($_POST["website"])) {
			$website = $_POST["website"];
		} else {
			$website = "";
		}
		if ( isset($_POST["paymenturl"])) {
			$paymenturl = $_POST["paymenturl"];
		} else {
			$paymenturl = "";
		}
		if ( isset($_POST["activityname"])) {
			$activityname = $_POST["activityname"];
		} else {
			$activityname = "";
		}
		if ( isset($_POST["payment_provider"])) {
			$payment_provider = $_POST["payment_provider"];
		} else {
			$payment_provider = "";
		}
		if ( isset($_POST["api_username"])) {
			$api_username = $_POST["api_username"];
		} else {
			$api_username = "";
		}
		if ( isset($_POST["api_password"])) {
			$api_password = $_POST["api_password"];
		} else {
			$api_password = "";
		}
		if ( isset($_POST["api_signature"])) {
			$api_signature = $_POST["api_signature"];
		} else {
			$api_signature = "";
		}
		if ( isset($_POST["notes"])) {
			$notes = $_POST["notes"];
		} else {
			$notes = "";
		}

		if ( isset($_POST["introtext"])) {
			$introtext = $_POST["introtext"];
		} else {
			$introtext = "";
		}


		if ( isset($_POST["startdate"])) {
			$startdate = $_POST["startdate"];
		} else {
			$startdate = date("m-d-Y");
		}

		if (isUser($session, Role_ApplicationAdmin)){
			if ( isset($_POST["adminid"])) {
				$adminid = $_POST["adminid"];
				if ($adminid != User::UserID_Undefined) {
					$strSQL = "UPDATE teams SET name = ?, city = ?, coachid = ?, address1 = ?, address2 = ?, state = ?, postalcode = ?, phone = ?, email = ?, adminid = ?, notes = ?, startdate = ?, website = ?, paymenturl = ?, activityname = ?, api_username = ?, api_password = ?, api_signature = ?, payment_provider = ?, introtext = ? WHERE id = ?";
					executeQuery($dbconn, $strSQL, $bError, array($teamname, $city, $coachid, $address1, $address2, $state, $postalcode, $phone, $email, $adminid, $notes, $startdate, $website, $paymenturl, $activityname, $api_username, $api_password, $api_signature, $payment_provider, $introtext, $teamid));
				}
			} else {
					$strSQL = "UPDATE teams SET name = ?, city = ?, coachid = ?, address1 = ?, address2 = ?, state = ?, postalcode = ?, phone = ?, email = ?, notes = ?, startdate = ?, website = ?, paymenturl = ?, activityname = ?, api_username = ?, api_password = ?, api_signature = ?, payment_provider = ?, introtext = ? WHERE id = ?";
					executeQuery($dbconn, $strSQL, $bError, array($teamname, $city, $coachid, $address1, $address2, $state, $postalcode, $phone, $email, $notes, $startdate, $website, $paymenturl, $activityname,  $api_username, $api_password, $api_signature, $payment_provider, $introtext, $teamid));
			}

		} else {
			$strSQL = "UPDATE teams SET city = ?, coachid = ?, address1 = ?, address2 = ?, state = ?, postalcode = ?, phone = ?, email = ?, notes = ?, eventidattendance = ?, website = ?, paymenturl = ?, activityname = ?, api_username = ?, api_password = ?, api_signature = ?, payment_provider = ?, introtext = ? WHERE id = ?";
			executeQuery($dbconn, $strSQL, $bError, array($city, $coachid, $address1, $address2, $state, $postalcode, $phone, $email, $notes, $eventidattendance, $website, $paymenturl, $activityname,  $api_username, $api_password, $api_signature, $payment_provider, $introtext, $teamid));
		}
		redirect("team-props-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&done=1");
	}
}

if ($bError) {
	redirect("team-props-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=" . $errno);
}
 ?>
