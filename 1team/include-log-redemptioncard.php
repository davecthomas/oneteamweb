<?php 
// This is intended to be included from other attendance recording pages
// Prerequisites: RedemptionCard object must be defined, named redemptioncard
// attendanceDate, eventid must be set
if ((! isset($attendanceDate)) || (! isset($eventid)) ||(! isset($teamid)) || (! isset($redemptioncard))){?>
<h3 class="usererror">Error</h3>
<p class="usererror">Invalid scan.</p>
<?php
	$bError = true;
} else {
	$canLogAttendance = false;
	  

	$userid = $redemptioncard->getUserID();

	$user = new User($session, $userid );

	if ((DbObject::isDbObject($user)) && (!$bError)) {

		$fullname = $user->getFirstName(). "&nbsp;" . $user->getLastName();
		$accountStatus = $user->getAccountStatus();

		$numEventsRemaining = $redemptioncard->getNumEventsRemaining();
		if ((($accountStatus == UserAccountStatus_Active) || ($accountStatus == UserAccountStatus_Guest)) && ($numEventsRemaining != 0)) {
			$today = date("m/d/Y");
			if ($numEventsRemaining != Sku::NumEventsUnlimited){
				$redemptioncard->setNumEventsRemaining(--$numEventsRemaining);
				$redemptioncard->commit();
			} ?>
<h3>Attendance Recorded</h3>
<p>Attendance recorded for <a href="user-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $userid?>"><?php echo $fullname?></a>.<br>
<?php
			if ($numEventsRemaining != Sku::NumEventsUnlimited){?>
There are <?php echo $numEventsRemaining?> events remaining in the card.</p>
<?php
			}
			// Notify if plan is empty
			if ($numEventsRemaining == 0) {  ?>
<p class="usererror">The card may be discarded. It is now empty.</p>
<?php 
			}

			$canLogAttendance = true;
		} else {?>
<p class="usererror">The card scanned is no longer valid and cannot be used. Please discard it.</p>
<?php

		}
	}

	if ($canLogAttendance ) {
		$attendance = new Attendance($session);
		if (DbObject::isDbObject($attendance)){
			$attendance->init($userid, $attendanceDate, $eventid, $teamid, $redemptioncard->getType());
			$attendance->commit();
		}
		else $bError = true;
	}
	// Conditionally display image
	if ($user->hasImageURL()) {?>
<img src="<?php echo $user->getImageURL()?>" id="" border=0">
<?php		
	}

}
if ($bError){ ?>
<p class="usererror">Card not accepted. No attendance recorded for this event.</p>
<?php
}
// Start footer section
include('footer.php'); 
?>
</body>
</html>

