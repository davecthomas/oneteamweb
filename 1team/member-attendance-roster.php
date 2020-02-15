<?php
ob_start(); 	// This caches non-header output, allowing us to redirect after header.php
include('utils.php');
$bError = false;
$err = "";
// Assure we have the input we need, else send them to default.php
if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid = getUserID()) == RC_RequiredInputMissing)){
	redirect("default.php?rc=" . RC_RequiredInputMissing);
}
// Get $session array initialized
$session = startSession($sessionkey, $userid);
if (! isValidSession($session )){
	redirect("default.php?rc=" . $session);
	$bError = true;
}
// Only admins can execute this script
redirectToLoginIfNotAdminOrCoach( $session);

// teamid depends on who is calling
if ( !isUser($session, Role_ApplicationAdmin)){
	if ( !isset($session["teamid"])){
		$bError = true;
		$err = "at.";
	} else {
		$teamid = $session["teamid"];
	}
} else {
	if ( isset($_REQUEST["teamid"])){
		$teamid = $_REQUEST["teamid"];
	} else {
		$bError = true;
		$err = "t";
	}
}

if (isset($_REQUEST["attendancerosterlistselections"])){
	$attendancerosterlistselections = trim($_REQUEST["attendancerosterlistselections"]);
} else {
	$bError = true;
	$err = "i";
}

if (isset($_POST["eventid"])){
	$eventid = $_POST["eventid"];
} else {
	$bError = true;
	$err = "e";
}
if (isset($_POST["date"])){
	$attendanceDate = $_POST["date"];
} else {
	$bError = true;
}

if (!$bError) {
	// Array format: numentries, uid1, uname1, uid2, uname2, ...
	$attendees = explode(",",$attendancerosterlistselections);
	// First element is the number of entries
	$numattendees = array_splice($attendees, 0, 1);
	$returnString = "";
	$returnErrString = "";
	for ($i=0; $i<$numattendees[0]; $i++){
		if (logAttendance( $session, $attendees[$i*2], $teamid, $attendanceDate, $eventid, $err) != RC_Success){
			if (strlen($returnErrString) > 0) {
				$returnErrString .= ", ";
				if ($i == $numattendees[0]-1) $returnErrString .= "and ";
			}
			$returnErrString .= $attendees[$i*2+1]. " reason: ".$err;
			if ($i == $numattendees[0]-1) $returnErrString .= ".";
			$bError = true;
		} else {
			if (strlen($returnString) > 0) {
				$returnString .= ", ";
				if ($i == $numattendees[0]-1) $returnString .= "and ";
			}
			$returnString .= $attendees[$i*2+1];
			if ($i == $numattendees[0]-1) $returnString .= ".";
		}
	}

	if (!$bError){
		redirect("member-attendance-roster-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&done=" . urlencode($returnString));
		ob_end_flush();
	}
}
if ($bError) {
	redirect("member-attendance-roster-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=" . urlencode($returnErrString));
	ob_end_flush();
}


function logAttendance( $session, $uid, $teamid, $attendanceDate, $eventid, &$err){
	$bError = false;
	$err = "";
	// Make sure this user exists
	$strSQL = "SELECT users.firstname, users.lastname, users.roleid, users.id AS userid, users.useraccountinfo, useraccountinfo.*, images.* FROM useraccountinfo, teams RIGHT OUTER JOIN images RIGHT OUTER JOIN users ON users.imageid = images.id ON images.teamid = teams.id WHERE users.useraccountinfo = useraccountinfo.id AND users.id = ? and users.teamid = ?;";

	$dbconn = getConnectionFromSession($session);
	$userResults = executeQuery($dbconn, $strSQL, $bError, array($uid, $teamid));

	if (!$bError)  {

		$fullname = $userResults["firstname"] . "&nbsp;" . $userResults["lastname"];
		$canLogAttendance = false;

		if (! is_null($userResults["useraccountinfo"]) ) {
			$accountStatus  = $userResults["status"];
			$isUserBillable = $userResults["isbillable"];
			// Non-billable and active is ok to log
			if (! $isUserBillable && $accountStatus == UserAccountStatus_Active ) {
				$canLogAttendance = true;
			}

			// Billable and active members requires check for payments made to log attendance
			else if ($isUserBillable && ($accountStatus == UserAccountStatus_Active) && (doesRoleContain($userResults["roleid"], Role_Member)) ) {
				$today = date("m/d/Y");
				$bNoValidPaymentsFound = true;

				// Get last payment for this program
				// Get the programid for this event
				$strSQL = "select programid from events where id = ?";
				$programid = executeQueryFetchColumn($dbconn, $strSQL, $bError, array($eventid));

				if (($programid != Program_Undefined) && ($programid != FALSE)){
					// Get the payment details for the last payment made for this program
					$strSQL = "SELECT skus.*, orderitems.id as payid, orderitems.* FROM (paymentmethods INNER JOIN (programs INNER JOIN (users INNER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid) on orderitems.programid = programs.id) on orderitems.paymentmethod = paymentmethods.id) WHERE users.id = orderitems.userid and userid = ? AND orderitems.programid = ? AND orderitems.teamid = ? and numeventsremaining <> 0 and (paymentdate + expires >= current_date) ORDER BY paymentdate DESC";
					$paymentResults = executeQuery($dbconn, $strSQL, $bError, array($uid, $programid, $teamid));

					// If we find one result, we know this user has a payment that will conver the event they are trying to log
					if (count($paymentResults) > 0){
						$lastPaymentDate = $paymentResults[0]["paymentdate"];
						$numClassesRemaining = $paymentResults[0]["numeventsremaining"];
						$canLogAttendance = true;
						$bNoValidPaymentsFound = FALSE;
						$lastPaymentID = $paymentResults[0]["payid"];
						$expires = $paymentResults[0]["expires"];
					} else {
						$bNoValidPaymentsFound = TRUE;
						$numClassesRemaining = 0;
					}

				} else {
					// ERROR
					$bError = true;
					$err = "pid";
					$numClassesRemaining = 0;

				}

				// if the member is out of classes, can't log
				if (($numClassesRemaining == 0) || ($bNoValidPaymentsFound)){
					$canLogAttendance = false;

				} else {
					// Take one class off remaining classes
					if ($numClassesRemaining != Sku::NumEventsUnlimited) $numClassesRemaining --;
					// Notify if plan is empty
					if ($numClassesRemaining == 0) {
						// No warning, but they just burned their last class
					}
					// Update the number of classes remaining
					$strSQL = "UPDATE orderitems SET numeventsremaining = ? WHERE id = ? and userid = ?;";
					executeQuery($dbconn, $strSQL, $bError, array($numClassesRemaining, $lastPaymentID, $uid));

					$canLogAttendance = true;
				}
			}

			if ($canLogAttendance ) {
				// Regardless of if they pay, we log attendance
				// Add a record to the attendance table with the member id and date.
				$strSQL = "INSERT INTO attendance VALUES ( ?, ?, ?,DEFAULT,?)";
				executeQuery($dbconn, $strSQL, $bError, array($uid, $attendanceDate, $eventid, $teamid));
			}
		}
	}
	if (!$bError && $canLogAttendance) return RC_Success;
	else {
		if (!$canLogAttendance) $err = "ordernotfound";
		return RC_LogAttendanceUnsuccessful;
	}

}
