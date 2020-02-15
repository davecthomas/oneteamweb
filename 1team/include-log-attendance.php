<?php
// This is intended to be included from other attendance recording pages
// Prerequisites:
// attendanceDate, eventid, teamid and memberid must be set
if ((! isset($attendanceDate)) || (! isset($eventid)) ||(! isset($teamid)) ||(! isset($memberID))){?>
<h3 class="usererror">Error</h3>
<p class="usererror">Required information was missing from the request.</p>
<?php
	$bError = true;
} else {

	$strSQL = "SELECT COUNT(*) FROM users WHERE users.id = ? AND users.teamid = ?;";
	$dbconn = getConnectionFromSession($session);
	$rowCount = executeQueryFetchColumn($dbconn, $strSQL, $bError, array( $memberID, $teamid));

	if ($rowCount != 1 ) {?>
<h3 class="usererror">Error</h3>
<p class="usererror">The credentials you entered are not recognized.</p>
<?php
		$bError = true;
	} else {
		// Make sure this user exists
		$strSQL = "SELECT users.firstname, users.lastname, users.roleid, users.id AS userid, users.useraccountinfo, useraccountinfo.*, images.* FROM useraccountinfo, teams RIGHT OUTER JOIN images RIGHT OUTER JOIN users ON users.imageid = images.id ON images.teamid = teams.id WHERE users.useraccountinfo = useraccountinfo.id AND users.id = ? and users.teamid = ?;";
		$userResults = executeQuery($dbconn, $strSQL, $bError, array($memberID, $teamid));

		if ((count($userResults) == 1) && (!$bError)) {

			$fullname = $userResults[0]["firstname"] . "&nbsp;" . $userResults[0]["lastname"];
			$canLogAttendance = false;

			if (! is_null($userResults[0]["useraccountinfo"]) ) {
				$accountStatus  = $userResults[0]["status"];
				$isUserBillable = $userResults[0]["isbillable"];
				if (! $isUserBillable && $accountStatus == UserAccountStatus_Active ) {
					$canLogAttendance = true; ?>
<h3>Attendance Recorded</h3>
<p>Attendance recorded for <a href="user-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $memberID?>"><?php echo $fullname?></a>.<br>
<?php
				}

				if ($isUserBillable && ($accountStatus == UserAccountStatus_Active) && (doesRoleContain($userResults[0]["roleid"], Role_Member)) ) {
					$today = date("m/d/Y");
					$bNoValidPaymentsFound = true;

					// Get last payment for this program
					// Get the programid for this event
//					$strSQL = "select programs.id as programid from events, programs where eventid = events.id and eventid = ?";
					$strSQL = "select programid from events where id = ?";
					$programid = executeQueryFetchColumn($dbconn, $strSQL, $bError, array($eventid));

					if (($programid != Program_Undefined) && ($programid != FALSE)){
						// Get the payment details for the last payment made for this program
						$strSQL = "SELECT skus.*, orderitems.id as payid, orderitems.*
									FROM (paymentmethods INNER JOIN (
										programs INNER JOIN (
											users INNER JOIN (
												orderitems LEFT OUTER JOIN
													skus ON (skus.id = orderitems.skuid))
												on users.id = orderitems.userid)
											on orderitems.programid = programs.id)
										on orderitems.paymentmethod = paymentmethods.id)
									WHERE users.id = orderitems.userid and userid = ? AND orderitems.programid = ? AND orderitems.teamid = ? and
										((numeventsremaining > 0) or (numeventsremaining = -1)) and (paymentdate + expires >= current_date)
									ORDER BY paymentdate DESC";
						$paymentResults = executeQuery($dbconn, $strSQL, $bError, array($memberID, $programid, $teamid));

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

					// if the member is out of classes, tell them so
					if ((($numClassesRemaining != Sku::NumEventsUnlimited) && ($numClassesRemaining < 1)) || ($bNoValidPaymentsFound)){ ?>
<h3 class="usererror">Attendance Problem</h3>
<p class="usererror">Sorry, there are no existing payments on file to cover logging attendance for this event.</p>
<?php
						$canLogAttendance = false;
					} else { ?>
<h3>Attendance Recorded</h3>
<p>Attendance recorded for <a href="user-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $memberID?>"><?php echo $userResults[0]["firstname"]?>&nbsp;<?php echo $userResults[0]["lastname"]?></a>.<br>
<?php
						// Take one class off remaining classes
						if ($numClassesRemaining != Sku::NumEventsUnlimited) {
							$numClassesRemaining --; ?>
There are <?php echo $numClassesRemaining?> classes remaining in the plan.</p>
<?php
							// Notify if plan is empty
							if ($numClassesRemaining < 1) {  ?>
<p class="usererror">The plan is now empty for the current payment period!</p>
<?php
							}
						} ?>
<p>Last payment was made <?php echo $lastPaymentDate?>.</p>
<p>Next payment is due in <?php echo getNextPaymentDueDate2($memberID, $lastPaymentID, $expires, $dbconn)?>.<p>
<?php
						// Update the number of classes remaining
						$strSQL = "UPDATE orderitems SET numeventsremaining = ? WHERE id = ? and userid = ?;";
						executeQuery($dbconn, $strSQL, $bError, array($numClassesRemaining, $lastPaymentID, $memberID));

						$canLogAttendance = true;
					}
				}

				if ($canLogAttendance ) {
					// Regardless of if they pay, we log attendance
					// Add a record to the attendance table with the member id and date.
					$strSQL = "INSERT INTO attendance VALUES ( ?, ?, ?,DEFAULT,?)";
					executeQuery($dbconn, $strSQL, $bError, array($memberID, $attendanceDate, $eventid, $teamid));
					$pagemode = "embedded";
					$whomode = "user";
					$userid = $memberID;
				} else { ?>
<p>Attendance not logged for <?php echo $fullname ?></p>
<input type="button" value="Back" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = '<?php echo $_SERVER["HTTP_REFERER"]?>'"/>
<?php
				}
				// Conditionally display image
				if ((!is_null($userResults[0]["url"])) && (strlen($userResults[0]["url"]) > 0)) {?>
<img src="<?php echo $userResults[0]["url"]?>" id="" border=0">
<?php
				}
			}

		}
	}
}
if ($bError){ ?>
<p class="usererror">Attendance not recorded.</p>
<?php
}
// Start footer section
include('footer.php');
?>
</body>
</html>
