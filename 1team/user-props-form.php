<?php
include_once('utils.php');
$pagemode = pagemodeStandalone;
$title= " User Properties" ;
// In search results case, this page is included, so $userid may be set outside page
if ((!isset($userid)) && (isset($_GET["id"]))){
	if (isset($_GET["id"]))  {
		$userid_input = (int)(getCleanInput($_GET["id"]));
	} else {
		$userid_input = 0;
	}
} else {
//	$pagemode = pagemodeEmbedded;
}
if ($pagemode == pagemodeStandalone){
	include('header.php');
	$userid = $userid_input;
?>
<script type="text/javascript">
dojo.require("dijit.form.DateTextBox");
dojo.require("dijit.form.MultiSelect");
</script>

<?php
}
$bError = false;
$teamid = NotFound;
$err = "";
// teamid depends on who is calling
if (!isUser($session, Role_ApplicationAdmin)){
	$teamid = getTeamID($session);
} else {
	if (isset($_REQUEST["teamid"])){
		$teamid = $_REQUEST["teamid"];
	} else{
		$bError = true;
		$err = "t";
	}
}

if (canIAdministerThisUser( $session, $userid)) {
	$canAdmin = true;
} else {
	$canAdmin = false;
}

if (isThisMe($session, $userid)) {
	$thisIsMe = true;
} else {
	$thisIsMe = false;
}



// Only display prev/next for app admin, since this data spans teams
if (isUser( $session, Role_ApplicationAdmin) ) { ?>
<p></p>
<div class="navtop">
<ul id="nav">
<li><a href="<?php if ($userid > 1 ) echo "user-props-form.php?id=" . ($userid-1) . buildRequiredParamsConcat($session) . "&teamid=" . $teamid?>"><img src="img/a_previous.gif" border="0" alt="previous">Previous member</a></li>
<li><a class="linkopacity" href="user-props-form.php?id=<?php echo($userid+1) . buildRequiredParamsConcat($session) . "&teamid=" . $teamid?>">Next member<img src="img/a_next.gif" border="0" alt="next"></a></li>
</ul>
</div><p></p>
<?php
}

$strSQL = "SELECT users.*, users.id as userid, images.*, useraccountinfo.* FROM useraccountinfo, teams RIGHT OUTER JOIN images RIGHT OUTER JOIN users ON users.imageid = images.id ON images.teamid = teams.id WHERE users.useraccountinfo = useraccountinfo.id AND users.id = ? and users.teamid = ?;";

$dbconn = getConnectionFromSession($session);
$userprops = executeQuery($dbconn, $strSQL, $bError, array($userid, $teamid));

if (!isset($userprops["userid"])) {
	echo '<p class="error">' . UserNotFound . '</p>';
} else {
	$roleid = $userprops["roleid"];

	$teamid = $userprops["teamid"];
	$teamname = $teaminfo["teamname"];

	// Before we show anything, we need to know who is looking at what to decide what to show/enable
	// if a user is looking at his own record, show and enable limited set. if he is looking at his coach's record, hide/disable
	$bAuthorizedView = TRUE;
	// enableControl allows us to disable input controls
	$enableControl = "";
	// disableMemberControls allows us to disable specific inputs that members can read but not change, such as status, pay method
	$disableMemberControls = "";
	$enableControlApplicationAdmin = "disabled";

	// if not application admin, check out who this is to determine if they are ok to see it
	if ((! isUser( $session, Role_ApplicationAdmin)) ) {
		// Members can view themselves and edit themselves, but they can view their coach
		if ((isUser( $session, Role_Member)) && (! isThisMe( $session, $userid)) ) {
			// if this is not the coach record, error
			if (! isThisMyCoach($session, $userid) ) {
				$bAuthorizedView = FALSE;
			} else {
				$enableControl = "disabled";
			}

		// Only TeamAdmin and ApplicationAdmin can look at TeamAdmins
		} else if ((doesRoleContain($roleid, Role_TeamAdmin)) && ((! isUser( $session, Role_ApplicationAdmin)) && (! isUser( $session, Role_TeamAdmin))) ) {
			$bAuthorizedView = FALSE;

		// Only ApplicationAdmins can look at ApplicationAdmins
		} else if ((doesRoleContain($roleid, Role_ApplicationAdmin)) && (! isUser( $session, Role_ApplicationAdmin)) ) {
			$bAuthorizedView = FALSE	;

		} else if (isUser( $session, Role_Member) ) {
			$disableMemberControls = "disabled";
		}
	// Blank means enabled for app admin. Currently only used for login value
	} else {
		$enableControlApplicationAdmin = "";
	}

	// Check if authorized to see data (block 3)
	if ($bAuthorizedView == FALSE ) { ?>
<h4 class="usererror">Not authorized.</h4>
<?php
	// Authorized OK to see the data
	} else {
		$todaysDate = date("m/d/Y");

		// This is the h3 section that displays the user role and name
		echo "<h3>";
		// For Appl Admin, we don't have a team name
		if (! isUser( $session, Role_ApplicationAdmin) ) {
			echo($teamname);
		}

		echo " " . roleToStr( $roleid, $teamterms) . ": ";

		$accountStatus  = $userprops["status"];

		if ($accountStatus == UserAccountStatus_Inactive ) {
			echo "<span class=\"subdued\">\n";

		} else {
			echo "<span>\n";
		}

		$firstname = $userprops["firstname"];
		$lastname = $userprops["lastname"];
		$username = $firstname . " " . $lastname;
		$isBillable = $userprops["isbillable"];
		echo htmlspecialchars($firstname) . "&nbsp;";
		echo htmlspecialchars($lastname) . "</span></h3>\n";

		// End h3 section ?>
<form name="userprops" action="/1team/user-props.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="id" value="<?php echo $userid ?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<?php
		// Show member account status info
		if (isRoleNonAdmin($roleid)) {
?>
<h4 class="expandable"><a class="linkopacity" href="javascript:void(0)" onclick="javascript:togglevis('paymentinfo');return false">Account Information<img src="img/a_expand.gif" alt="expand section" id="paymentinfo_img" border="0"></a></h4>
<div class="hideit" id="paymentinfo" name="paymentinfo">
<div class="indented-group-noborder">
<table class="noborders">
<?php		if (isAnyAdminLoggedIn($session)) { ?>
<tr>
<td class="strong">Account Status</td>
<td>
<select name="status" <?php echo disableMemberControls?>>
<option value="<?php echo UserAccountStatus_Inactive?>" <?php if ($accountStatus==UserAccountStatus_Inactive )  echo("selected")?>><?php echo $aStatus[UserAccountStatus_Inactive+UserAccountStatus_ArrayOffset]?></option>
<option value="<?php echo UserAccountStatus_Active?>" <?php if ($accountStatus==UserAccountStatus_Active ) echo("selected")?>><?php echo $aStatus[UserAccountStatus_Active+UserAccountStatus_ArrayOffset]?></option>
<option value="<?php echo UserAccountStatus_Overdue?>" <?php if ($accountStatus==UserAccountStatus_Overdue ) echo("selected")?>><?php echo $aStatus[UserAccountStatus_Overdue+UserAccountStatus_ArrayOffset]?></option>
<option value="<?php echo UserAccountStatus_Disabled?>" <?php if ($accountStatus==UserAccountStatus_Disabled ) echo("selected")?>><?php echo $aStatus[UserAccountStatus_Disabled+UserAccountStatus_ArrayOffset]?></option>
</select>
</td>
</tr>
<?php		} ?>
<tr>
<td class="strong">Email address</td>
<td><input type="text" value="<?php echo $userprops["email"]?>" name="email" <?php echo $enableControl?>></td>
</tr>
<?php		if (isAnyAdminLoggedIn($session)) { ?>
<tr>
<td class="strong">Is Billable</td>
<td><select name="isbillable" <?php echo $disableMemberControls?>>
<option value="0" <?php if ($isBillable==0 ) echo("selected")?>>No</option>
<option value="1" <?php if ($isBillable==1 ) echo("selected")?>>Yes</option>
</select>
</td>
</tr>
<?php		}?>
</table>
<?php		if ((isAnyAdminLoggedIn($session)) || ($thisIsMe)) { ?>
<input type="submit" class="btn" value="Update Account Information" name="change" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<?php
				// Payment history only for billable members
				if ($isBillable) {
					$pageMode = "expand"; ?>
<h4 class="expandable"><a class="linkopacity" href="javascript:togglerender('paymenthist', 'paymenthistory','include-payment-history.php?<?php echo returnRequiredParams($session)?>&id=<?php echo $userid?>&teamid=<?php echo $teamid?>&pagemode=embedded' )">Payment History<img src="img/a_expand.gif" alt="expand section" id="paymenthist_img" border="0"></a></h4>
<div class="hideit" id="paymenthist">
<iframe src=""
	id="paymenthistory" name="paymenthistory"
	style="width: 800px;
	height: 380px;
	border:none"
></iframe>
</div>
<?php 		}
		} ?>
</div>
</div>
<?php
		}  // non admin ?>
<h4 class="expandable"><a class="linkopacity" href="javascript:void(0)" onclick="javascript:togglevis('personalinfo');return false;">Personal Information<img src="img/a_expand.gif" alt="expand section" id="personalinfo_img" border="0"></a></h4>
<div class="hideit" id="personalinfo">
<div class="indented-group-noborder">
<table class="noborders">
<tr>
<td class="strong">First Name</td>
<td><input type="text" value="<?php echo htmlspecialchars($firstname)?>" name="firstname" <?php echo $enableControl?>></td>
</tr>
<tr>
<td class="strong">Last Name</td>
<td><input type="text" value="<?php echo htmlspecialchars($lastname)?>" name="lastname" <?php echo $enableControl?>></td>
</tr>
<tr>
<td class="strong">Street Address</td>
<td><input type="text" value="<?php echo htmlspecialchars($userprops["address"])?>" name="address" <?php echo $enableControl?>></td>
</tr>
<tr>
<td class="strong">City</td>
<td><input type="text" value="<?php echo htmlspecialchars($userprops["city"])?>" name="city" <?php echo $enableControl?>></td>
</tr>
<tr>
<td class="strong">State</td>
<td ><input type="text" value="<?php echo htmlspecialchars($userprops["state"])?>" name="state" <?php echo $enableControl?>></td>
</tr>
<tr>
<td class="strong">Zip code</td>
<td ><input type="text" value="<?php echo htmlspecialchars($userprops["postalcode"])?>" name="postalcode" <?php echo $enableControl?>></td>
</tr>
<tr>
<td class="strong">Mobile Phone<?php if (isUser($session, Role_TeamAdmin)) echo '<br/><span class="normal">'.$teamterms["termadmin"].' must be able to receive text messages</span>'?></td>
<td ><input type="text" value="<?php echo htmlspecialchars($userprops["smsphone"])?>" name="smsphone" <?php echo $enableControl?>></td>
</tr>
<tr>
<td class="strong">Mobile phone carrier<br/><span class="normal">Mobile phone carrier is required to send text messages</span></td>
<td ><select name="smsphonecarrier" <?php echo $enableControl?>>
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
<td class="strong">Phone 2</td>
<td ><input type="text" value="<?php echo htmlspecialchars($userprops["phone2"])?>" name="phone2" <?php echo $enableControl?>></td>
</tr>
<tr>
<?php
		// You can either see your own data. Admins see all.
		if (isThisMe( $session, $userid) || isAnyAdminLoggedIn($session) ) { ?>
<td class="strong">User login name</td>
<td><input type="text" name="login" value="<?php echo $userprops["login"]?>" <?php echo $enableControlApplicationAdmin?>></td>
</tr>
<?php
		} // End if you are seeing your own data or you are admin

		if (( isRoleNonAdmin($roleid)) && ($canAdmin)) { ?>
<tr>
<td class="strong">Birthdate</td>
<td ><input type="text" value="<?php echo htmlspecialchars($userprops["birthdate"])?>" dojoType="dijit.form.DateTextBox" name="birthdate" <?php echo $enableControl?>></td>
</tr>
<?php		// Only admins can see gender, notes, referral, stop date and reason
			if (isAnyAdminLoggedIn($session)) { ?>
<tr>
<td class="strong">Gender</td>
<td ><select name="gender">
<?php if (normalizeGender($userprops["gender"]) == 0) echo '<option value="0" selected>Select...</option>';?>
<option value="F" <?php if (normalizeGender($userprops["gender"]) == Gender_Female){ echo "selected";}?>><?php echo Gender_Female?></option>
<option value="M" <?php if (normalizeGender($userprops["gender"]) == Gender_Male){ echo "selected";}?>><?php echo Gender_Male?></option>
</select></td>
</tr>
<tr>
<td class="strong">Notes</td>
<td ><input type="text" size="80" maxlength="80" value="<?php echo htmlspecialchars($userprops["notes"])?>" name="notes"></td>
</tr>
<tr>
<td class="strong">Referred by</td>
<td ><input type="text" value="<?php echo htmlspecialchars($userprops["referredby"])?>" name="referredby"></td>
</tr>
<tr>
<td class="strong">Stop Date</td>
<td ><input type="text" value="<?php echo htmlspecialchars($userprops["stopdate"])?>" dojoType="dijit.form.DateTextBox" name="stopdate"></td>
</tr>
<tr>
<td class="strong">Stop Reason</td>
<td ><input type="text" size="80" maxlength="80" value="<?php echo htmlspecialchars($userprops["stopreason"])?>" name="stopreason"></td>
</tr>
<?php		} // End Only admins can see gender, notes, referral, stop date and reason?>
<tr>
<td class="strong">Emergency contact</td>
<td ><input type="text" value="<?php echo htmlspecialchars($userprops["emergencycontact"])?>" name="emergencycontact"></td>
</tr>
<tr>
<td class="strong">EC Phone 1</td>
<td ><input type="text" value="<?php echo htmlspecialchars($userprops["ecphone1"])?>" name="ecphone1"></td>
</tr>
<tr>
<td class="strong">EC Phone 2</td>
<td ><input type="text" value="<?php echo htmlspecialchars($userprops["ecphone2"])?>" name="ecphone2"></td>
</tr>
<?php
		} // End non admin?>
</table>
<?php
		// Only display the submit button if (they are looking at their own data, unless admin
		if (isThisMe( $session, $userid) || (isAnyAdminLoggedIn($session)) ) { ?>
<p>
<input type="button" class="btn" value="Update" name="change" onclick="validateForm()" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" <?php echo $enableControl?>/>
</p>
<?php
		}	// End Only display the submit button if (they are looking at their own data, unless admin ?>
</div>
</div>
<h4 class="expandable"><a class="linkopacity" href="javascript:void(0)" onclick="javascript:togglevis('otherinfo');return false;">Team Information<img src="img/a_expand.gif" alt="expand section" id="otherinfo_img" border="0"></a></h4>
<div class="hideit" id="otherinfo">
<div class="indented-group-noborder">
<table class="noborders">
<?php
			if (isAnyAdminLoggedIn($session)){?>
<tr>
<td class="bold" valign="top">User Role</td>
<td>
<?php // note use of [] in form element name. This tells php to process multiple values on the back-end ?>
<select name="roleid[]" dojoType="dijit.form.MultiSelect" size="4" multiple="true">
<?php
				if ((isAnyAdminLoggedIn($session)) && (doesRoleContain($roleid, Role_TeamAdmin))) { ?>
<option value="<?php echo Role_TeamAdmin?>" <?php if (doesRoleContain($roleid, Role_TeamAdmin) ) { echo(" selected"); }?>><?php echo roleToStr(Role_TeamAdmin, $teamterms)?></option>
<?php
				} ?>
<option value="<?php echo Role_Member?>" <?php if (doesRoleContain( $roleid, Role_Member) ) { echo(" selected"); }?>><?php echo roleToStr(Role_Member, $teamterms)?></option>
<option value="<?php echo Role_Coach?>" <?php if (doesRoleContain( $roleid, Role_Coach) ) { echo(" selected"); }?>><?php echo roleToStr(Role_Coach, $teamterms)?></option>
</select>
</td>
</tr>
<?php
			} ?>
<tr>
<td class="strong">Member Since</td>
<?php
		// If there is a start date, calculate time in class
		if (! is_null($userprops["startdate"]) ) {
			$datejoined = htmlspecialchars($userprops["startdate"]);
			$startdate = $datejoined;
			$timeIn = getMembershipDuration($userid, $dbconn);
		} else {
			$datejoined = "";
			$timeIn = 0;
		}	// End: if there is a start date ?>
<td><input type="text" name="startdate" id="startdate" value="<?php echo $datejoined?>" <?php if ($canAdmin) echo 'dojoType="dijit.form.DateTextBox"';?> required="true" <?php echo $enableControl?>/>
<?php
		// Only display calculated time in membership if they are active members
		if ((strlen($datejoined) > 0) && ($accountStatus == UserAccountStatus_Active) || (!doesRoleContain($roleid, Role_Member))) {
			echo($timeIn . "\n");
		} // End: if there is a date joined to display for active member?>
</td>
</tr>
<tr>
<td class="strong">Team</td>
<td>
<?php
		$teamid = $userprops["teamid"];
		// Only an app admin sees this pulldown. Otherwise they just see their own team name
		if (isUser( $session, Role_ApplicationAdmin) ) {
			$strSQL = "SELECT * FROM teams ORDER BY name;";
			$teamsResults = executeQuery($dbconn, $strSQL, $bError);

			$rowCount = 0;
			$loopMax = count($teamsResults );
			if ($loopMax > 0){?>
<select name="team" <?php echo $enableControl?>>
<?php
				while ($rowCount < $loopMax) {
					echo( "<option value=\"");
					echo( $teamsResults[$rowCount]["id"]);
					echo( "\"");
					if ($teamsResults[$rowCount]["id"] == $teamid ) {
						echo( "selected=\"true\"");
					}
					echo( ">");
					echo( $teamsResults[$rowCount]["name"] );
					echo( "</option>\n");
					$rowCount ++;
				} ?>
</select>
<?php
			}
		} else {	// Else not app admin, show team name and add a hidden field for teamid ?>
<input type="hidden" name="team" value="<?php echo $teamid?>">
<?php echo $teamname?>
<?php
		}	// End: else not app admin, show team name and add a hidden field for teamid ?>
</td>
</tr>
<?php
		if ( isRoleNonAdmin($roleid)) { // Member and coach has a coach ?>
<tr>
<td class="strong"><?php echo $teamterms["termcoach"]?></td>
<td >
<?php
			// coach
			$currentSelection = $userprops["coachid"];
			$strSQL = "SELECT * FROM users WHERE roleid & " . Role_Coach . " = " . Role_Coach . " and teamid = ? ORDER BY firstname ;";
			$coachResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));

			$rowCount = 0;
			$loopMax = count($coachResults);
			if ($loopMax > 0){
				echo '<select name="coachid"' . $disableMemberControls .">\n";
				if (!isValidUserID($currentSelection)) echo '<option value="' . User::UserID_Undefined . '">Select a ' . $teamterms["termcoach"] . '...</option>';
				// Build an option list of coaches (only can be modified if not a member)
				while ($rowCount < $loopMax) {
					echo( "<option value=\"");
					echo( $coachResults[$rowCount][ "id"]);
					echo( "\"");
					if ($coachResults[$rowCount][ "id"] == $currentSelection ) {
						echo( "selected=\"true\"");
					}
					echo( ">");
					echo( trim($coachResults[$rowCount][ "firstname"]) . " " . trim($coachResults[$rowCount][ "lastname"]));
					echo( "</option>\n");
					$rowCount ++;
				}	// End: Build an option list of coaches (only can be modified if not a member)
				echo "</select>	";
			} else {
				echo 'No coaches are defined for ' . $teamname . '.';
			} 	?>
</td>
</tr>
<?php
		} // End: non-admin ?>
</table>
<?php
		// Only team admins and admins can change coach
		if (isAnyAdminLoggedIn( $session) ) {
?>
<p><input type="button" class="btn" value="Update" name="change" onclick="validateForm()" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" <?php echo $enableControl?>/><p>
<?php
		} // End: Only team admins and admins can change coach ?>
</div>
</div>
</form>
<?php	// If Role_Member, show payment, attendance, promotions histories, custom fields (block 4)
		if ( isRoleNonAdmin($roleid)) {
			$pageMode = "embedded";
			$whoMode = "user";

			if (isTeamUsingLevels($session, $teamid)) {
			// Promotions toggle ?>
<h4 class="expandable"><a class="linkopacity" href="javascript:void(0)" onclick="javascript:togglerender('promotionshist', 'promotionshistory','include-promotions.php<?php buildRequiredParams($session)?>&mode=<?php echo $whoMode?>&pagemode=<?php echo $pageMode?>&id=<?php echo $userid?>&teamid=<?php echo $teamid ?>');return false;">Promotions History<img src="img/a_expand.gif" alt="expand section" id="promotionshist_img" border="0"></a></h4>
<div class="hideit" id="promotionshist">
<iframe src="empty.html"
	id="promotionshistory" name="promotionshistory"
	style="width: 800px;
	height: 300px;
	border:none"
></iframe>
</div>
<?php		}
			// Image
			if ((isAnyAdminLoggedIn( $session)) || ((!is_null($userprops["imageid"])) || ($userprops["imageid"] != ImageID_Undefined))) {
?>
<h4 class="expandable"><a class="linkopacity" href="javascript:togglevis('userimg')">Picture<img src="img/a_expand.gif" alt="expand section" id="userimg_img" border="0"></a></h4>
<div class="hideit" id="userimg">
<div class="group">
<div class="indented-group-noborder">
<?php		// Conditionally display image
			if ((!is_null($userprops["url"])) && (strlen($userprops["url"]) > 0)) {?>
<img src="<?php echo $userprops["url"]?>" id="" border=0" alt="user image">
<?php
			}
			// Only admins get form
			if (isAnyAdminLoggedIn( $session)) {?>
<p>Select an existing image by URL</p><form action="image-upload.php" method="post" enctype="multipart/form-data">
<!--<input type="file" name="image" name="image" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>-->
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid?>"/>
<input type="hidden" name="type" value="<?php echo ImageType_User?>"/>
<input type="hidden" name="objid" value="<?php echo $userid ?>"/>
<p class="strong">Image URL&nbsp;<input type="text" value="<?php echo htmlspecialchars($userprops["url"] )?>" name="url"></p>
<input type="submit" value="Save Picture" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
</form>
<?php		} ?>
</div></div></div>
<?php
		} // End image div

		// Custom fields support
		$strSQL = "SELECT customfields.id as customfieldsid, customfields.name as customfieldname, * FROM customfields LEFT OUTER JOIN customdata ON (customdata.customfieldid = customfields.id and customdata.memberid = ? AND customfields.teamid = ?) WHERE customfields.teamid = ? ORDER BY customfields.listorder;";
		$customdataResults= executeQuery($dbconn, $strSQL, $bError, array($userid, $teamid, $teamid));
		$loopMax = count( $customdataResults);
		if ($loopMax > 0) {?>
<form action="/1team/user-props-custom.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="id" value="<?php echo $userid ?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<h4 class="expandable"><a class="linkopacity" href="javascript:void(0)" onclick="javascript:togglevis('custominfo');return false">Custom Information<img src="img/a_expand.gif" alt="expand section" id="custominfo_img" border="0"></a></h4>
<div class="hideit" id="custominfo">
<div class="indented-group-noborder">
<table class="noborders">
<?php
				$rowCount = 0;

				while ($rowCount < $loopMax) {
					// Get the data type of the custom field so you know how to display it
					$datatype = $customdataResults[$rowCount]["customdatatypeid"];
					$customfieldid = $customdataResults[$rowCount]["customfieldsid"];
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
							echo '<input type="text" value="' . $customdataValue . '" name="customfield'.$customfieldid.'value' . $rowCount .'" ' . $disableMemberControls . '>';
							break;
						case CustomDataType_Num:
							$customdataValue = $customdataResults[$rowCount]["valueint"];
							echo '<input type="text" value="' . $customdataValue . '" name="customfield'.$customfieldid.'value' . $rowCount .'" ' . $disableMemberControls . '>';
							break;
						case CustomDataType_Float:
							$customdataValue = $customdataResults[$rowCount]["valuefloat"];
							echo '<input type="text" value="' . $customdataValue . '" name="customfield'.$customfieldid.'value' . $rowCount .'" ' . $disableMemberControls . '>';
							break;
						case CustomDataType_Bool:
							$customdataValue = $customdataResults[$rowCount]["valuebool"];
							echo '<input type="checkbox" name="customfield'.$customfieldid.'value' . $rowCount .'"';
							if ($customdataValue == true) echo 'checked="checked"' ;
							echo " " . $disableMemberControls . " />";
							break;
						case CustomDataType_Date:
							$customdataValue = $customdataResults[$rowCount]["valuedate"];
							echo '<input type="text" value="'.$customdataValue.'" dojoType="dijit.form.DateTextBox" name="customfield'.$customfieldid.'value' . $rowCount .'"' . $disableMemberControls . '>';
							break;
						case CustomDataType_List:
							$customdataValue = $customdataResults[$rowCount]["valuelist"];

							// if the datatype is a list, build the select (and conditionally select the item)
							$strSQL = "SELECT customlistdata.id as customlistdataid, * FROM customlists, customlistdata WHERE customlistdata.customlistid = customlists.id and customlists.id = ? and customlists.teamid = ? ORDER BY listorder;";

							$customlistResults = executeQuery($dbconn, $strSQL, $bError, array($customdataResults[$rowCount]["customlistid"], $teamid));
							$loopMaxList = count( $customlistResults);
							$rowCountList = 0;
							if ($loopMaxList > 0) {
								echo '<select value="0" name="customfield'.$customfieldid.'value' . $rowCount .'" ' . $disableMemberControls .">\n";

								if (((isAnyAdminLoggedIn($session)) && ($customdataValue == 0) || (strlen($customdataValue) == 0))) echo "<option selected>Select...</option>\n";
								// Build an option list of coaches (only can be modified if not a member)
								while ($rowCountList < $loopMaxList) {
									echo( "<option value=\"");
									echo( $customlistResults[$rowCountList][ "customlistdataid"]);
									echo( "\"");
									if ($customlistResults[$rowCountList][ "customlistdataid"] == $customdataValue ) {
										echo( "selected=\"true\"");
									}
									echo( ">");
									echo( $customlistResults[$rowCountList][ "listitemname"]);
									echo( "</option>\n");
									$rowCountList ++;
								}
								echo "</select>	";
							} else {
								echo "List not defined.";
							}
							break;
						default:
							$customdataValue = Error;
							echo $customdataValue;
							break;
					}
					echo "</td></tr>\n";

					$rowCount ++;
				} // End while Display rsCustom custom field form elements ?>
</table>
<?php
				if (($loopMax > 0) && (isAnyAdminLoggedIn($session))) { ?>
<input type="submit" class="btn" value="Update custom fields" name="updatecustom" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<?php
				} // End: If has custom and user is admin ?>
<input type="hidden" value="<?php echo htmlspecialchars($firstname)?>" name="firstname">
<input type="hidden" value="<?php echo htmlspecialchars($lastname)?>" name="lastname">
</form>
</div>
</div>
<?php
			// End if there are any custom fields defined for this team (block 5 ln 524)
			}

		// End non-admin
		}
	// End if authorized (block 3 ln 82)
	}
}

// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "The " . $teamterms["termmember"] . " was not saved successfully.", "");
} else if (isset($_GET["done"])){
	showMessage("Success", "The " . $teamterms["termmember"] . " was saved successfully.");
} else if (isset($_GET["new"])){
	showMessage('Success', 'The ' . $teamterms["termmember"] . ' was created successfully. If you have not <a href="reset-password-form.php?' . returnRequiredParams($session).'&id='.$userid_input.'">Reset their password</a>, you must before they attempt to sign in.');
}
if ($pagemode == pagemodeStandalone){

	// Start footer section
	include('footer.php');
}?>
<script type="text/javascript">
function validateForm(){
	<?php
	// if team admin, generate javascript to test smsphone for value
	if ( doesRoleContain( $roleid, Role_TeamAdmin)){
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
