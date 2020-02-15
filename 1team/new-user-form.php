<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
// The title is set here and rendered in header.php
$title= " New User" ;
include('header.php');
$title = "New " . $teamterms["termmember"];
$bError = false;
$bAllowAdd = false;



if (!$bError){
	if ( isset($_GET["roleid"])) {
		$roleid = $_GET["roleid"];
	} else {
		$roleid = Role_Member;
	}

	// teamid depends on who is calling
	if (isUser($session, Role_TeamAdmin)){
		if (isset($session["teamid"])){
			$teamid = $session["teamid"];
		}
	} else {
		if (isset($_GET["teamid"])){
			$teamid = $_GET["teamid"];
		} else {
			$teamid = 0; // This should trigger a select list for app admin
		}
	}

	// Get team account settings
	if ($teamid != 0){
		if (getTeam($session, $teamid, $teamResults) == RC_Success){
			$title .= " for " . $teamResults["name"];
	     	$bAllowAdd = canAddUsersToTeam($session, $teamid, $memberCount);
		} else $bAllowAdd = false;
	} ?>
<script type="text/javascript">
dojo.require("dijit.form.DateTextBox");
dojo.require("dijit.form.ValidationTextBox");
</script>
<script type="text/javascript">
function updateCityState() {
// TO do
}
function teamSelected(){
	if (document.newuser.teamid.value != 0){
		showit('teamselected');
	} else {
		hideit('teamselected');
	}
}
function changeUserRole(){
	// bitwise operator since role is bit field
	if (!(document.newuser.roleid.value & <?php echo Role_TeamAdmin?>)){
		showit('membersonly');
		hideit('teamadminonly')
	} else {
		hideit('membersonly');
		showit('teamadminonly');
	}
}

</script>
<h3><?php echo $title?></h3>
<?php
	// not allowed to add a member
	if (($teamid != 0) && (!$bAllowAdd)) { ?>
<p class="usererror">Your team has <?php echo $memberCount ." " . $teamterms["termmember"]?>s. Your team plan is currently full. <a href="help/contact.php">Contact us</a> to arrange a plan upgrade.</p>
<?php
	}
	// Allowed to add a member
	else { ?>
<form name="newuser" action="/1team/new-user.php" method="post">
<?php buildRequiredPostFields($session) ?>
<?php
		if ($teamid != 0) { ?>
<input type="hidden" name="teamid" value="<?php echo $teamid?>"/>
<?php
		// Select list for team if they are app admin
		} else {
		?>
<h4 class="expandable"><a class="linkopacity" href="javascript:togglevis('teaminfo')">Team<img src="img/a_collapse.gif" alt="collapse section" id="teaminfo_img" border="0"></a></h4>
<div class="showit" id="teaminfo">
<div class="group">
<div class="indented-group-noborder">
<table class="noborders">
<tr>
<td class="bold">Team</td>
<td>
<?php
			// In any case, give the app admin the option of selecting another team admin
			$strSQL = "SELECT teams.id, teams.name FROM teams ORDER BY name;";
      $dbconn = getConnectionFromSession($session);
  		$teams_records = executeQuery($dbconn, $strSQL, $bError);	?>
<select name="teamid"  onchange="teamSelected()">
<option value="0">Select a team...</option>
<?php
			foreach ($teams_records as $rowteampick) {
				echo( '<option value="');
				echo( $rowteampick["id"]);
				echo( '"');
				echo( ">");
				echo( $rowteampick["name"]);
				echo( "</option>");
			}?>
</select>
</td>
</tr>
</table>
</div></div></div>
<?php
		} ?>
<div class="<?php if ($teamid > 0) echo 'showit'; else echo 'hideit';?>" id="teamselected">
<h4 class="expandable"><a class="linkopacity" href="javascript:togglevis('personalinfo')">Personal Information<img src="img/a_collapse.gif" alt="collapse section" id="personalinfo_img" border="0"></a></h4>
<div class="showit" id="personalinfo">
<div class="group">
<div class="indented-group-noborder">
<table class="noborders">
<tr>
<td class="bold">User Role</td>
<td>
<select name="roleid" onchange="changeUserRole()">
<?php
		if (isUser($session, Role_ApplicationAdmin )) { ?>
<option value="<?php echo Role_TeamAdmin?>" <?php if (doesRoleContain($roleid, Role_TeamAdmin) ) { echo(" selected"); }?>><?php echo roleToStr(Role_TeamAdmin, $teamterms)?></option>
<?php
		} ?>
<option value="<?php echo Role_Member?>" <?php if (doesRoleContain( $roleid, Role_Member) ) { echo(" selected"); }?>><?php echo roleToStr(Role_Member, $teamterms)?></option>
<option value="<?php echo Role_Coach | Role_Member?>" <?php if (doesRoleContain($roleid, Role_Coach )) { echo(" selected"); }?>><?php echo roleToStr(Role_Coach, $teamterms)?></option>
</select>
</td>
</tr>
<tr>
<td class="bold">First Name</td>
<td><input type="text" value="" name="firstname" size="20" dojoType="dijit.form.ValidationTextBox" required="true" propercase="true" promptMessage="Enter first name." invalidMessage="First name is required." trim="true" ></td>
</tr>
<tr>
<td class="bold">Last Name</td>
<td><input type="text" value="" name="lastname" size="60" dojoType="dijit.form.ValidationTextBox" required="true" propercase="true" promptMessage="Enter last name." invalidMessage="Last name is required." trim="true" ></td>
</tr>
<tr>
<tr>
<td class="bold">Email</td>
<td ><input type="text" value="" name="email"></td>
</tr>
<tr>
<td class="bold">Date joined</td>
<td><input type="text" name="startdate" id="startdate" value="<?php echo date("Y-m-d")?>" dojoType="dijit.form.DateTextBox" required="true" promptMessage="yyyy/mm/dd"/></td>
</tr>
<tr>
<td class="bold">Street Address</td>
<td><input type="text" value="" name="address1"></td>
</tr>
<tr>
<td class="bold">Street Address 2</td>
<td><input type="text" value="" name="address2"></td>
</tr>
<tr>
<td class="bold">City</td>
<td><input type="text" value="" name="city"></td>
</tr>
<tr>
<td class="bold">State</td>
<td ><select id="state" name="state">
<option value="">Select...</option>
<optgroup label="U.S. States">
<option value="AK">Alaska</option>
<option value="AL">Alabama</option>
<option value="AR">Arkansas</option>
<option value="AZ">Arizona</option>
<option value="CA">California</option>
<option value="CO">Colorado</option>
<option value="CT">Connecticut</option>
<option value="DC">District of Columbia</option>
<option value="DE">Delaware</option>
<option value="FL">Florida</option>
<option value="GA">Georgia</option>
<option value="HI">Hawaii</option>
<option value="IA">Iowa</option>
<option value="ID">Idaho</option>
<option value="IL">Illinois</option>
<option value="IN">Indiana</option>
<option value="KS">Kansas</option>
<option value="KY">Kentucky</option>
<option value="LA">Louisiana</option>
<option value="MA">Massachusetts</option>
<option value="MD">Maryland</option>
<option value="ME">Maine</option>
<option value="MI">Michigan</option>
<option value="MN">Minnesota</option>
<option value="MO">Missouri</option>
<option value="MS">Mississippi</option>
<option value="MT">Montana</option>
<option value="NC">North Carolina</option>
<option value="ND">North Dakota</option>
<option value="NE">Nebraska</option>
<option value="NH">New Hampshire</option>
<option value="NJ">New Jersey</option>
<option value="NM">New Mexico</option>
<option value="NV">Nevada</option>
<option value="NY">New York</option>
<option value="OH">Ohio</option>
<option value="OK">Oklahoma</option>
<option value="OR">Oregon</option>
<option value="PA">Pennsylvania</option>
<option value="PR">Puerto Rico</option>
<option value="RI">Rhode Island</option>
<option value="SC">South Carolina</option>
<option value="SD">South Dakota</option>
<option value="TN">Tennessee</option>
<option value="TX">Texas</option>
<option value="UT">Utah</option>
<option value="VA">Virginia</option>
<option value="VT">Vermont</option>
<option value="WA">Washington</option>
<option value="WI">Wisconsin</option>
<option value="WV">West Virginia</option>
<option value="WY">Wyoming</option>
</optgroup>
<optgroup label="Canadian Provinces">
<option value="AB">Alberta</option>
<option value="BC">British Columbia</option>
<option value="MB">Manitoba</option>
<option value="NB">New Brunswick</option>
<option value="NF">Newfoundland</option>
<option value="NT">Northwest Territories</option>
<option value="NS">Nova Scotia</option>
<option value="NU">Nunavut</option>
<option value="ON">Ontario</option>
<option value="PE">Prince Edward Island</option>
<option value="QC">Quebec</option>
<option value="SK">Saskatchewan</option>
<option value="YT">Yukon Territory</option>
</optgroup>
</select></td>
</tr>
<tr>
<td class="bold">Zip code</td>
<td ><input type="text" value="" name="postalcode" onblur="updateCityState();"></td>
</tr>
<tr>
<td class="bold">Mobile Phone (<?php echo $teamterms["termadmin"]?> must be able to receive text messages)</td>
<td ><input type="text" value="" name="smsphone"></td>
</tr>
<tr>
<td class="strong">Mobile phone carrier</td>
<td ><select name="smsphonecarrier">
<?php
		if (isset($userprops["smsphonecarrier"])) $smsphonecarrier = $userprops["smsphonecarrier"];
		else $smsphonecarrier = smsphonecarrier_Undefined;
		if ((!isset($userprops["smsphonecarrier"]) || ($userprops["smsphonecarrier"] == smsphonecarrier_Undefined))){?>
		<option value="<?php echo smsphonecarrier_Undefined?>" selected>Select a mobile phone carrier...</option>
<?php	}?>
		<option value="att" <?php if ($smsphonecarrier == "att") echo "selected"?>>AT&T</option>
		<option value="verizon" <?php if ($smsphonecarrier == "verizon") echo "selected"?>>Verizon</option>
    <option value="googlefi" <?php if ($smsphonecarrier == "googlefi") echo "selected"?>>Google Fi</option>
		<option value="sprint" <?php if ($smsphonecarrier == "sprint") echo "selected"?>>Sprint or Credo</option>
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
<td class="bold">Phone 2</td>
<td ><input type="text" value="" name="phone2"></td>
</tr>
<tr>
<td class="bold">User login</td>
<td><input type="text" value="" name="login" size="60" dojoType="dijit.form.ValidationTextBox" required="true" promptMessage="Enter user login." invalidMessage="User login required." trim="true" ></td>
</tr>
<tr>
<td class="bold">Notes</td>
<td ><input type="text" value="" name="notes"></td>
</tr>
<tr>
<td class="bold">Referred by</td>
<td ><input type="text" value="" name="referredby"></td>
</tr>
<tr>
<td class="bold">Birthdate</td>
<td ><input type="text" name="birthdate" id="birthdate" value="<?php echo date("Y-m-d")?>" dojoType="dijit.form.DateTextBox" required="true" promptMessage="yyyy/mm/dd"/></td>
</tr>
<tr>
<td class="bold">Gender</td>
<td ><select name="gender">
<option value="0" selected>Select...</option>
<option value="F" ><?php echo Gender_Female?></option>
<option value="M" ><?php echo Gender_Male?></option>
</select>
</td>
</tr>
</table>
</div>
</div>
</div>
<?php
		if (doesRoleContain($roleid, Role_Member)) {?>
<div class="showit" id="membersonly">
<h4 class="expandable"><a class="linkopacity" href="javascript:togglevis('planinfo')">Plan Information<img src="img/a_collapse.gif" alt="collapse section" id="planinfo_img" border="0"></a></h4>
<div class="showit" id="planinfo">
<div class="group">
<div class="indented-group-noborder">
<table class="noborders">
<tr>
<td class="bold">Account Status</td>
<td>
<select name="status">
	<option value="<?php echo UserAccountStatus_Undefined?>" selected>Select...</option>
	<option value="<?php echo UserAccountStatus_Inactive?>"><?php echo $aStatus[UserAccountStatus_Inactive+UserAccountStatus_ArrayOffset]?></option>
	<option value="<?php echo UserAccountStatus_Active?>" selected='true'><?php echo $aStatus[UserAccountStatus_Active+UserAccountStatus_ArrayOffset]?></option>
	<option value="<?php echo UserAccountStatus_Overdue?>"><?php echo $aStatus[UserAccountStatus_Overdue+UserAccountStatus_ArrayOffset]?></option>
	<option value="<?php echo UserAccountStatus_Disabled?>"><?php echo $aStatus[UserAccountStatus_Disabled+UserAccountStatus_ArrayOffset]?></option>
</select>
</td>
</tr>
<tr>
<td class="bold">Does this <?php echo $teamterms["termmember"]?> pay for membership?</td>
<td><select name="isbillable" >
<option value="0" >No</option>
<option value="1" selected>Yes</option>
</select>
</td>
</tr>
</table>
</div>
</div>
</div>
<h4 class="expandable"><a class="linkopacity" href="javascript:togglevis('otherinfo')">Other Information<img src="img/a_collapse.gif" alt="collapse section" id="otherinfo_img" border="0"></a></h4>
<div class="showit" id="otherinfo">
<div class="group">
<div class="indented-group-noborder">
<table class="noborders">
<tr>
<td class="bold">Emergency contact</td>
<td ><input type="text" value="" name="emergencycontact"></td>
</tr>
<tr>
<td class="bold">EC Phone 1</td>
<td ><input type="text" value="" name="ecphone1"></td>
</tr>
<tr>
<td class="bold">EC Phone 2</td>
<td ><input type="text" value="" name="ecphone2"></td>
</tr>
<?php		// Team admins - set coach
			if ((!isUser($session, Role_ApplicationAdmin)) && ($session["teamid"] > 0)){ ?>
<tr>
<td class="bold"><?php echo $teamterms["termcoach"]?></td>
<td >
<?php
//				$strSQL = "SELECT * FROM users WHERE roleid & " . Role_Coach . " = " . Role_Coach . " AND teamid = ? ORDER BY firstname;";
				$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.roleid, users.imageid, useraccountinfo.status, useraccountinfo.isbillable, images.* FROM useraccountinfo, teams RIGHT OUTER JOIN images RIGHT OUTER JOIN users ON users.imageid = images.id ON images.teamid = teams.id WHERE useraccountinfo.status = " . UserAccountStatus_Active . " AND users.useraccountinfo = useraccountinfo.id AND (roleid & " . Role_Coach . ") = " . Role_Coach . " AND users.teamid = ? ORDER BY firstname;";
				$dbconn = getConnectionFromSession($session);
				$userResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));
				if ( count($userResults) > 0) {  ?>
<select name="coachid">
<option value="0" selected>Select <?php echo $teamterms["termcoach"]?>...</option>
<?php
					$rowCountCoach = 0;
					while ($rowCountCoach < count($userResults) ) {
						echo '<option value="';
						echo $userResults[$rowCountCoach]["userid"];
						echo '"';
						echo ">";
						echo $userResults[$rowCountCoach]["firstname"];
						echo " ";
						echo $userResults[$rowCountCoach]["lastname"];
						echo "</option>";
						$rowCountCoach++;
					} ?>
</select>
<?php
				// No coaches, tell them so
				} else {
					echo "No " . $teamterms["termcoach"] . " has been defined for this " . $teamterms["termteam"] . ". ";
				} ?>
</td>
</tr>
<?php		} ?>
</table>
</div>
</div>
</div>
<?php 		// end membersonly div ?>
</div>
<?php
			// If Member
		} ?>
<p class="bold">Email new password to&nbsp;<?php echo $teamterms["termuser"] ?>?&nbsp;<input type="checkbox" name="sendemail" value="1" checked="checked"></p>
<input type="submit" value="Create user" name="next" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<input type="button" value="Cancel" name="next" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="window.location='home.php'"/>
</form>
<?php // end team selected div ?>
</div>
<?php
	}
}
if (isset($_GET["err"])){
	showError("Error", "New user not created. Error: " . $_GET["err"], "");
} // Start footer section
include('footer.php'); ?>
</body>
</html>
