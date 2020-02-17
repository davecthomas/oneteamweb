<?php
$title = "Home";
include('header.php');
$username = getCurrentUserName($session);
$userid = getSessionUserID($session);
$canlogattendance = false;
// TeamID is passed in for application admin,  else it's in the session
if ((!isUser( $session, Role_ApplicationAdmin)) && (isset($session["teamid"]))) {
	$teamid = $session["teamid"];
} else {
	if (isset($_GET["teamid"])) $teamid = $_GET["teamid"];
	else $teamid = 0;
}

$strSQL = "SELECT users.*, users.id as userid, images.*, useraccountinfo.* FROM useraccountinfo, teams RIGHT OUTER JOIN images RIGHT OUTER JOIN users ON users.imageid = images.id ON images.teamid = teams.id WHERE users.useraccountinfo = useraccountinfo.id AND users.id = ? and users.teamid = ?;";
$dbconn = getConnectionFromSession($session);
$userprops = executeQuery($dbconn, $strSQL, $bError, array($userid, $teamid));

echo "<h3>" .$title . " of " . roleToStr($session["roleid"], $teamterms) . " " . $username . " with " . $teaminfo["teamname"] ."</h3>\n";

if (isset($userprops["id"])) {
	$teamid = $userprops["teamid"];
	$roleid = $userprops["roleid"];
	$runningOnAdminConsole = (bool) (AttendanceConsole::isAttendanceConsole($session));
	$isBillable = $userprops["isbillable"];
	$teamname = $teaminfo["teamname"];
	$fullname = $userprops["firstname"] . " " . $userprops["lastname"];

	$accountStatus = $userprops["status"];

	$todaysDate = date("m/d/Y");
	// Member who is paying
	if (isUser( $session,Role_Member) && $isBillable) {
		// This is to allow emailing me if necessary
		$flagEmailMgmt = False;
		// Figure out if their payment is late
		// Get all unexpired orderitems, and their events
		$strSQL = "SELECT programs.name as programname, skus.*, skus.name as skuname, events.id as eventid, events.name as eventname, orderitems.id as payid, orderitems.* FROM events INNER JOIN (programs INNER JOIN (users INNER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid) on orderitems.programid = programs.id) on events.id = programs.eventid WHERE users.id = orderitems.userid AND userid = ? AND orderitems.teamid = ? and (paymentdate expires >= current_date) ORDER BY paymentdate DESC;";
		$paymentResults = executeQuery($dbconn, $strSQL, $bError, array($userid, $teamid));

		// If they have unexpired events, show attendance button, with event selector (only events they can record)
		if (count($paymentResults) > 0){
			// Only enable attendance logging from the Admin IP address stored for this team
			if ($runningOnAdminConsole) { ?>
<form name="logattendanceform" action="/1team/log-attendance.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="id" value="<?php echo $userid ?>"/>
<p class="attention">Log your attendance at <?php echo $teamname?> today for event&nbsp;<?php
				$rowCount = 0;
				$loopMax = count($paymentResults);?>
<select name="eventid" onchange="document.logattendanceform.eventname.value = this.options[this.selectedIndex].text">
<?php
				while ($rowCount < $loopMax) {
					// Only include events where they have a payment that has > 0 events left
					if ($paymentResults[$rowCount]["numeventsremaining"] > 0){
						echo  "<option ";
						echo  'value="' . $paymentResults[$rowCount]["eventid"] . '"';
						if ($rowCount == 0) {
							echo " selected ";
						}
						echo  ">";
						echo $paymentResults[$rowCount]["eventname"] . " (" . $paymentResults[$rowCount]["numeventsremaining"] . " remaining events to be used within " .
							getNextPaymentDueDate2($userid, $paymentResults[$rowCount]["payid"], $paymentResults[$rowCount]["expires"], $dbconn) . ")";
						echo  "</option>\n";
					}
					$rowCount++;
				}?>
</select>
<input type="hidden" name="eventname" value="<?php echo $paymentResults[0]["name"]?>"/>
<input type="submit" value="Here <?php echo $todaysDate?>" name="log-attendance" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/></p>
</form>
<?php
			}
		}
	// End Billable Member
	}

	// Active Non billable members can log attendance if they are at the admin console
	if ((($accountStatus == UserAccountStatus_Active) && $runningOnAdminConsole && isUser( $session,Role_Member) && ($isBillable == 0))) {
		// Only enable attendance logging from the Admin IP address stored for this team
		if ($runningOnAdminConsole) {
?>
<form action="/1team/log-attendance.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="id" value="<?php echo $userid ?>"/>
<p class="attention">Click the button to register your attendance at <?php echo $teamname?> today at&nbsp;
<?php
				$strSQL = "SELECT * FROM events WHERE teamid = ? and scannable = TRUE ORDER by listorder;";
				$eventResults = executeQuery($dbconn, $strSQL, $bError, array($teamid ));
				$rowCount = 0;
				$loopMax = count($eventResults);
?>
<select name="eventid" onchange="document.scaneventform.eventname.value = this.options[this.selectedIndex].text">
<?php
				while ($rowCount < $loopMax) {
					echo  "<option ";
					echo  'value="' . $eventResults[$rowCount]["id"] . '"';
					if ($rowCount == 0) {
						echo " selected ";
					}
					echo  ">";
					echo $eventResults[$rowCount]["name"];
					echo  "</option>\n";
					$rowCount++;
				}
?>
</select>
<input type="hidden" name="eventname" value="<?php echo $eventResults[0]["name"]?>"/>
<input type="submit" value="Here <?php echo $todaysDate?>" name="log-attendance" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
</form>

<?php
		}
	}
	// Conditionally display image
	if ((!is_null($userprops["url"])) && (strlen($userprops["url"]) > 0)) {?>
<img src="<?php echo $userprops["url"]?>" id="" border=0" alt="user">
<?php
	}
?>
<form name="userprops" action="/1team/user-props.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="id" value="<?php echo $userid ?>"/>
<h4 class="expandable"><a class="linkopacity" href="javascript:togglevis('personalinfo')">Personal Information<img src="img/a_expand.gif" alt="expand section" id="personalinfo_img" border="0"></a></h4>
<div class="hideit" id="personalinfo">
<div class="indented-group-noborder">
<table class="noborders">
<tr>
<td class="strong">Login name</td>
<td><?php echo $userprops["login"]?></td>
</tr>
<tr>
<td class="strong">First name</td>
<td><input type="text" value="<?php echo $userprops["firstname"]?>" name="firstname"></td>
</tr>
<tr>
<td class="strong">Last name</td>
<td><input type="text" value="<?php echo $userprops["lastname"]?>" name="lastname"></td>
</tr>
<tr>
<td class="strong">Street Address</td>
<td><input type="text" value="<?php echo $userprops["address"]?>" name="address"></td>
</tr>
<tr>
<td class="strong">City</td>
<td><input type="text" value="<?php echo $userprops["city"]?>" name="city"></td>
</tr>
<tr>
<td class="strong">State</td>
<td ><input type="text" value="<?php echo $userprops["state"]?>" name="state"></td>
</tr>
<tr>
<td class="strong">Zip code</td>
<td ><input type="text" value="<?php echo $userprops["postalcode"]?>" name="postalcode"></td>
</tr>
<tr>
<td class="strong">Mobile Phone <?php if (isUser($session, Role_TeamAdmin)) echo '<br/><span class="normal">'.$teamterms["termadmin"].' must be able to receive text messages</span>'?></td>
<td ><input type="text" value="<?php echo $userprops["smsphone"]?>" name="smsphone"></td>
</tr>
<tr>
<td class="strong">Mobile phone carrier<br/><span class="normal">Mobile phone carrier is required to send text messages</span></td>
<td ><select name="smsphonecarrier">
<?php
		if (isset($userprops["smsphonecarrier"])) $smsphonecarrier = $userprops["smsphonecarrier"];
		else $smsphonecarrier = smsphonecarrier_Undefined;
		if ((!isset($userprops["smsphonecarrier"]) || ($userprops["smsphonecarrier"] == smsphonecarrier_Undefined))){?>
		<option value="<?php echo smsphonecarrier_Undefined?>" selected>Select a mobile phone carrier...</option>
<?php	}?>
		<option value="att" <?php if ($smsphonecarrier == "att") echo "selected"?>>AT&T</option>
		<option value="verizon" <?php if ($smsphonecarrier == "verizon") echo "selected"?>>Verizon</option>
		<option value="sprint" <?php if ($smsphonecarrier == "sprint") echo "selected"?>>Sprint or Credo</option>
		<option value="googlefi" <?php if ($smsphonecarrier == "googlefi") echo "selected"?>>Google Fi</option>
		<option value="tmobile" <?php if ($smsphonecarrier == "tmobile") echo "selected"?>>T-Mobile</option>
		<option value="cellularone" <?php if ($smsphonecarrier == "cellularone") echo "selected"?>>Cellular One</option>
		<option value="qwest" <?php if ($smsphonecarrier == "qwest") echo "selected"?>>Qwest</option>
		<option value="virgin" <?php if ($smsphonecarrier == "virgin") echo "selected"?>>Virgin</option>
		<option value="nextel" <?php if ($smsphonecarrier == "nextel") echo "selected"?>>Nextel</option>
		<option value="alltel" <?php if ($smsphonecarrier == "alltel") echo "selected"?>>Alltel</option>
		<option value="boost" <?php if ($smsphonecarrier == "boost") echo "selected"?>>Boost</option>
	</select>
</td>
</tr>
<tr>
<td class="strong">Phone 2</td>
<td ><input type="text" value="<?php echo $userprops["phone2"]?>" name="phone2"></td>
</tr>
<tr>
<td class="strong">Email Address</td>
<td><input type="text" value="<?php echo $userprops["email"]?>" name="email"></td>
</tr>
<?php
	// Member
	if (isUser($session, Role_Member )) {?>
<tr>
<td class="strong">Emergency contact</td>
<td ><input type="text" value="<?php echo $userprops["emergencycontact"]?>" name="emergencycontact"></td>
</tr>
<tr>
<td class="strong">EC Phone 1</td>
<td ><input type="text" value="<?php echo $userprops["ecphone1"]?>" name="ecphone1"></td>
</tr>
<tr>
<td class="strong">EC Phone 2</td>
<td ><input type="text" value="<?php echo $userprops["ecphone2"]?>" name="ecphone2"></td>
</tr>
<?php
	} ?>
<tr>
<td></td><td>
<input type="button" value="Change" name="change" class="btn" onclick="validateForm()" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
</tr>
</table>
</div>
</div>
</form>
<?php
}

// Admins don't need this
if (! isUser( $session,Role_ApplicationAdmin) ) {
?>
<h4 class="expandable"><a class="linkopacity" href="javascript:togglevis('planinfo')">Plan Information<img src="img/a_expand.gif" alt="expand section" id="planinfo_img" border="0"></a></h4>
<div class="hideit" id="planinfo">
<div class="indented-group-noborder">
<table class="noborders">
<tr>
<td class="strong">Team</td>
<td><?php echo $teamname?></td></tr>
<tr>
<td class="strong">Member Since</td>
<?php
	if (! is_null($userprops["startdate"])) {
		$startdate = $userprops["startdate"];
	} else {
		$startdate =  date("m/d/Y");
	}
	$timeIn = getMembershipDuration($userid, $dbconn);?>
<td  valign="top"><?php echo $startdate?> (<?php echo $timeIn ?>)</td>
</tr>
</table>
</div>
</div>
<?php
	if (isUser( $session,Role_Member)) {
		$pageMode = "embedded";
		$whomode = "user";
		// Payment history only for billable members
		if ($isBillable) { ?>
<h4 class="expandable"><a class="linkopacity" href="javascript:togglerender('paymenthist', 'paymenthistory','include-payment-history.php<?php echo buildRequiredParams($session)?>&whomode=<?php echo $whomode?>&pagemode=<?php echo $pageMode?>&id=<?php echo $userid?>&teamid=<?php echo $teamid?>' )">Payment History<img src="img/a_expand.gif" alt="expand section" id="paymenthist_img" border="0"></a></h4>
<div class="hideit" id="paymenthist">
<iframe src=""
	id="paymenthistory" name="paymenthistory"
	style="width: 800px;
	height: 200px;
	border:none"
></iframe>
</div>
<?php	}
		// Attendance toggle ?>
<h4 class="expandable"><a class="linkopacity" href="javascript:togglerender('attendanceshist', 'attendancehistory','include-attendance-table.php<?php echo buildRequiredParams($session)?>&whomode=<?php echo $whomode?>&pagemode=<?php echo $pageMode?>&id=<?php echo $userid?>&teamid=<?php echo $teamid?>&startdate=<?php echo htmlspecialchars($startdate)?>' )">Attendance History<img src="img/a_expand.gif" alt="expand section" id="attendancehist_img" border="0"></a></h4>
<div class="hideit" id="attendanceshist">
<iframe src=""
	id="attendancehistory" name="attendancehistory"
	style="width: 800px;
	height: 200px;
	border:none"
></iframe>
</div>
<?php	// Promotions toggle
		if (isTeamUsingLevels($session, $teamid)) { ?>
<h4 class="expandable"><a class="linkopacity" href="javascript:togglerender('promotionshist', 'promotionshistory','include-promotions.php?whomode=<?php echo $whomode?>&pagemode=<?php echo $pageMode?>&id=<?php echo $userid?>&teamid=<?php echo $teamid . buildRequiredParamsConcat($session)?>' )">Promotions History<img src="img/a_expand.gif" alt="expand section" id="promotionshist_img" border="0"></a></h4>
<div class="hideit" id="promotionshist">
<iframe src=""
	id="promotionshistory" name="promotionshistory"
	style="width: 800px;
	height: 150px;
	border:none"
></iframe>
</div>
<?php
		}

		// Custom fields support - see if we have any defined for this team
		$strSQL = "SELECT COUNT(*) FROM customfields where teamid = ?;";
		$numcustomfields = executeQueryFetchColumn($dbconn, $strSQL, $bError, array($teamid));
		if ($numcustomfields > 0) {?>
<?php buildRequiredPostFields($session) ?>
<h4 class="expandable"><a class="linkopacity" href="javascript:togglevis('custominfo')">Custom Information<img src="img/a_expand.gif" alt="expand section" id="custominfo_img" border="0"></a></h4>
<div class="hideit" id="custominfo">
<div class="indented-group-noborder">
<table class="noborders">
<?php
			$strSQL = "SELECT customfields.name as customfieldname, * FROM customfields LEFT OUTER JOIN customdata ON (customdata.customfieldid = customfields.id and customdata.memberid = ? AND customfields.teamid = ?) ;";
			$customdataResults = executeQuery($dbconn, $strSQL, $bError, array($userid, $teamid));
			$loopMax = count( $customdataResults);

			$rowCount = 0;

			while ($rowCount < $loopMax) {
				// Get the data type of the custom field so you know how to display it
				$datatype = $customdataResults[$rowCount]["customdatatypeid"];
				if ($customdataResults[$rowCount]["hasdisplaycondition"]) {
					$dcObject = $customdataResults[$rowCount]["displayconditionobject"];
					$dcField = $customdataResults[$rowCount]["displayconditionfield"];
					$dcOperator = $customdataResults[$rowCount]["displayconditionoperator"];
					$dcValue = $customdataResults[$rowCount]["displayconditionvalue"];

					if ($dcObject == DisplayConditionObject_User) {
						$strSQL = "SELECT " . $dcField . " FROM " . $dcObject . " WHERE id = ?;";
					} else {
						$strSQL = "SELECT " . $dcField . " FROM " . $dcObject . " WHERE userid = ?;";
					}
					$dcResult = executeQueryFetchColumn($dbconn, $strSQL, $bError, array($userid));
					// Assume we can't until condition proves we can
					$displayCustomField = false;

					switch ($dcOperator) {
						case DisplayConditionOperator_EQ:
							if ($dcResult == $dcValue) $displayCustomField = true;
							break;
						case DisplayConditionOperator_LT:
							if ($dcResult< $dcValue) $displayCustomField = true;
							break;
						case DisplayConditionOperator_GT:
							if ($dcResult > $dcValue) $displayCustomField = true;
							break;
						case DisplayConditionOperator_NE:
							if ($dcResult != $dcValue) $displayCustomField = true;
							break;
					}

					// Skip the rest of this loop iteration if we don't display field
					if (!$displayCustomField) {
						$rowCount++;
						continue;
					}
				}
				echo "<tr>\n";
				echo '<td class="strong">' . $customdataResults[$rowCount]["customfieldname"] . "</td>\n";
				echo "<td>\n";
				echo "<td>";
				switch($datatype ){
					case CustomDataType_Text:
						$customdataValue = $customdataResults[$rowCount]["valuetext"];
						echo $customdataValue;
						break;
					case CustomDataType_Num:
						$customdataValue = $customdataResults[$rowCount]["valueint"];
						echo $customdataValue;
						break;
					case CustomDataType_Float:
						$customdataValue = $customdataResults[$rowCount]["valuefloat"];
						echo $customdataValue;
						break;
					case CustomDataType_Bool:
						$customdataValue = $customdataResults[$rowCount]["valuebool"];
						echo '<input type="checkbox" name="hasdisplaycondition"';
						if ($customdataValue == true) echo "checked='checked'" ;
						echo " disabled/>";
						break;
					case CustomDataType_Date:
						$customdataValue = $customdataResults[$rowCount]["valuedate"];
						echo $customdataValue;
						break;
					case CustomDataType_List:
						$customdataValue = $customdataResults[$rowCount]["valuelist"];

						// if the datatype is a list, build the select (and conditionally select the item)
						$strSQL = "SELECT listitemname FROM customlists, customlistdata WHERE customlistdata.customlistid = customlists.id and customlists.id = ? and customlists.teamid = ? AND customlistdata.id = ? ORDER BY listorder;";

						$customlistItemName = executeQueryFetchColumn($dbconn, $strSQL, $bError, array($customdataResults[$rowCount]["customlistid"], $teamid, $customdataValue ));
						echo $customlistItemName;
						break;
					default:
						$customdataValue = Error;
						echo $customdataValue;
						break;
				}
				if ((CustomDataType_Bool != $datatype) && (strlen($customdataValue < 1))) echo 'Contact your ' . $teamterms["termadmin"] . ' to set ' . $customdataResults[$rowCount]["customfieldname"];
				echo "</td></tr>\n";

				$rowCount ++;
			} ?>
</table>
</div>
</div>
<?php
			// End if there are any custom fields defined for this team (block 5 ln 524)
			}
	// member
	}
// not application Admin
}
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "Not saved successfully: " . $_GET["err"], "");
} else if (isset($_GET["done"])){
	showMessage("Success", "Saved successfully.");
}
// Start footer section
include('footer.php'); ?>
<script type="text/javascript">
function validateForm(){
	<?php
	// if team admin, generate javascript to test smsphone for value
	if (isUser($session, Role_TeamAdmin)){
		echo "if (!validatePhone(document.userprops.smsphone)){\n";
		echo "		alert('The ".$teamterms["termadmin"]." is required to have a phone capable of receiving text messages. Please enter a phone number with area code.')\n";
		echo "	} else {\n";
		echo "		userprops.submit();\n";
		echo "	}\n";
	} else {
		echo "	userprops.submit();\n";
	} ?>
}
</script>
</body>
</html>
