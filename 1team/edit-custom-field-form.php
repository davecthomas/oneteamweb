<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Edit Custom Field";
include('header.php');
?>
<script type="text/javascript">
dojo.require("dijit.form.DateTextBox");
</script>
<?php

echo "<h3>" . getTitle($session, $title) . "</h3>";
$teamid = NotFound;
$bError = false;

// teamid depends on who is calling
if (isUser($session, Role_TeamAdmin)){
	if (isset($session["teamid"])){
		$teamid = $session["teamid"];
	}
} else {
	if (isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	}
}

if (isset($_GET["id"])) {
	$customfieldid = $_GET["id"];
} else {
	$bError = true;
}

if (!$bError) {
	$strSQL = "SELECT * FROM customfields WHERE id = ? AND teamid = ?;";
  $dbconn = getConnectionFromSession($session);
	$customfieldResults = executeQuery($dbconn, $strSQL, $bError, array($customfieldid, $teamid));

	if (count($customfieldResults) > 0) { ?>
<h4>Edit custom field "<?php echo $customfieldResults[0]["name"]?>" for <?php echo getTeamName($teamid, $dbconn)?></h4>
<div class="indented-group-noborder">
<form name="editcustomfield" action="/1team/edit-custom-field.php" method="post">
<input type="hidden" name="id" value="<?php echo $customfieldid ?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<?php 	buildRequiredPostFields($session) ?>
<script type="text/javascript" >
function hidealldisplayinfo(){
	hideit('useraccountdisplaycondition');
	hideit('displayconditionfield_status');
	hideit('userdisplaycondition');

	hideit('displayconditionfield_programid');
	hideit('displayconditionfield_gender');
	hideit('displayconditionfield_coachid');
	hideit('displayconditionfield_roleid');
	hideit('displayconditionfield_birthdate');
}

function getDataType(){
	if (document.editcustomfield.datatype.value == <?php echo CustomDataType_List?>){
		showit('displaylisttype');
	} else {
		hideit('displaylisttype');
	}
}

function getDisplayConditions(){
	if (document.editcustomfield.hasdisplaycondition.value == "on"){
		showit('displayinfo');
		// Show these fields as a default. User can select others, but this helps get their bearings
		showit('userdisplaycondition');
		showit('displayconditionfield_birthdate');
	} else {
		hideit('displayinfo');
		hidealldisplayinfo();
	}
}

function changeDisplayConditionObject(){
	if (document.editcustomfield.displayconditionobject.value == "<?php echo DisplayConditionObject_User?>"){
		showit('userdisplaycondition');
		showit('displayconditionfield_birthdate');
		hideit('useraccountdisplaycondition');

		hideit('displayconditionfield_isbillable');
		hideit('displayconditionfield_status');

	} else {
		showit('useraccountdisplaycondition');
		showit('displayconditionfield_status');
		hideit('userdisplaycondition');

		hideit('displayconditionfield_programid');
		hideit('displayconditionfield_gender');
		hideit('displayconditionfield_coachid');
		hideit('displayconditionfield_roleid');
		hideit('displayconditionfield_birthdate');

	}

}

function changeDisplayConditionUser(){
	hideit('displayconditionfield_isbillable');
	hideit('displayconditionfield_status');
	switch (document.editcustomfield.displayconditionuser.value){
		case "<?php echo DisplayConditionUserColumn_birthdate?>":
			showit('displayconditionfield_birthdate');
			hideit('displayconditionfield_gender');
			hideit('displayconditionfield_coachid');
			hideit('displayconditionfield_roleid');
			hideit('displayconditionfield_programid');
		break;
		case "<?php echo DisplayConditionUserColumn_gender?>":
			showit('displayconditionfield_gender');
			hideit('displayconditionfield_birthdate');
			hideit('displayconditionfield_coachid');
			hideit('displayconditionfield_roleid');
			hideit('displayconditionfield_programid');
		break;
		case "<?php echo DisplayConditionUserColumn_coachid?>":
			showit('displayconditionfield_coachid');
			hideit('displayconditionfield_gender');
			hideit('displayconditionfield_birthdate');
			hideit('displayconditionfield_roleid');
			hideit('displayconditionfield_programid');
		break;
		case "<?php echo DisplayConditionUserColumn_roleid?>":
			showit('displayconditionfield_roleid');
			hideit('displayconditionfield_gender');
			hideit('displayconditionfield_coachid');
			hideit('displayconditionfield_birthdate');
			hideit('displayconditionfield_programid');
		break;
		case "<?php echo DisplayConditionUserColumn_programid?>":
			showit('displayconditionfield_programid');
			hideit('displayconditionfield_gender');
			hideit('displayconditionfield_coachid');
			hideit('displayconditionfield_roleid');
			hideit('displayconditionfield_birthdate');
		break;
	}
}

function changeDisplayConditionUserAccount(){
	hideit('displayconditionfield_programid');
	hideit('displayconditionfield_gender');
	hideit('displayconditionfield_coachid');
	hideit('displayconditionfield_roleid');
	hideit('displayconditionfield_birthdate');
	switch (document.editcustomfield.displayconditionuseraccount.value){
		case "<?php echo DisplayConditionUserAccountColumn_status?>":
			showit('displayconditionfield_status');
			hideit('displayconditionfield_isbillable');
		break;
		case "<?php echo DisplayConditionUserAccountColumn_isbillable?>":
			showit('displayconditionfield_isbillable');
			hideit('displayconditionfield_status');
		break;
	}
}

</script>
<table width="75%" class="noborders">
<tr><td width="50%">Custom Field name</td><td width="50%"><input type="text" name="name" size="60" maxlength="80" value="<?php echo $customfieldResults[0]["name"]?>"></td></tr>
<tr><td width="50%">Data type</td><td width="50%"><select name="datatype" onchange="getDataType()">
<?php
	$strSQL = "SELECT * FROM customdatatypes ORDER BY typename;";
	$customdatatypesResults = executeQuery($dbconn, $strSQL, $bError);

	$rowCount = 0;
	$loopMax = count($customdatatypesResults);

	while ($rowCount < $loopMax) {
		echo  "<option ";
		echo  'value="' . $customdatatypesResults[$rowCount]["id"] . '"';
		if ($customdatatypesResults[$rowCount]["id"] == $customfieldResults[0]["customdatatypeid"]) {
			echo " selected ";
		}
		echo  ">";
		echo $customdatatypesResults[$rowCount]["typename"];
		echo  "</option>";
		$rowCount++;
	}

	$hasDisplayCondition = $customfieldResults[0]["hasdisplaycondition"];
	$displayConditionOperator = $customfieldResults[0]["displayconditionoperator"];
	$displayConditionObject = $customfieldResults[0]["displayconditionobject"];
	$displayConditionField = $customfieldResults[0]["displayconditionfield"];
	$displayConditionValue = $customfieldResults[0]["displayconditionvalue"];	?>
</select>
<div class="
<?php
	if ($customfieldResults[0]["customdatatypeid"] == CustomDataType_List) {
		echo "showit";
	} else {
		echo "hideit";
	} ?>
" id="displaylisttype"><select name="customlistid">
<?php
	if ($customfieldResults[0]["customlistid"] == CustomList_Undefined) {
		echo '<option value="' . CustomList_Undefined . '" selected>Select a list type...</option>';
	}
	$strSQL = "SELECT * FROM customlists WHERE teamid = ? ORDER BY name;";
	$customlistsResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));

	$rowCount = 0;
	$loopMax = count($customlistsResults);

	while ($rowCount < $loopMax) {
		echo  "<option ";
		echo  'value="' . $customlistsResults[$rowCount]["id"] . '"';
		if ($customlistsResults[$rowCount]["id"] == $customfieldResults[0]["customlistid"]) {
			echo " selected ";
		}
		echo  ">";
		echo $customlistsResults[$rowCount]["name"];
		echo  "</option>";
		$rowCount++;
	} ?>
</select></div>
</td></tr>
<tr><td width="50%">Custom Field list order</td><td width="50%"><?php if ($customfieldResults[0]["listorder"] == "") echo "No order set"; else echo $customfieldResults[0]["listorder"];?></td></tr>
<tr><td width="50%">Display this field for any <?php echo $teamterms["termmember"]?> who meets special conditions</td><td width="50%"><input type="checkbox" name="hasdisplaycondition" <?php if ($hasDisplayCondition) echo "checked='checked'";?> onchange="getDisplayConditions()"/></td></tr>
</table>
<div class="
<?php
	if ($hasDisplayCondition) {
		echo "showit";
	} else {
		echo "hideit";
	} ?>
" id="displayinfo">
<div >
<table class="noborders">
<tr>
<td >Display this custom field if&nbsp;
<select name="displayconditionobject" onchange="changeDisplayConditionObject()">
<option value="<?php echo DisplayConditionObject_User?>" <?php echo getDisplayConditionSelection(DisplayConditionObject_User, $displayConditionObject)?>>the <?php echo $teamterms["termmember"]?>'s</option>
<option value="<?php echo DisplayConditionObject_UserAccount?>" <?php echo getDisplayConditionSelection(DisplayConditionObject_UserAccount, $displayConditionObject)?>><?php echo $teamterms["termmember"]?>&nbsp;account setting</option>
</select>
<div class="<?php if ($displayConditionObject == DisplayConditionObject_User) echo "showit"; else echo "hideit";?>" id="userdisplaycondition">
<select name="displayconditionuser" onchange="changeDisplayConditionUser()">
<option value="<?php echo DisplayConditionUserColumn_birthdate ?>" <?php if ($displayConditionField == DisplayConditionUserColumn_birthdate) echo "selected" ?>>Birth date</option>
<option value="<?php echo DisplayConditionUserColumn_gender ?>" <?php if ($displayConditionField == DisplayConditionUserColumn_gender) echo "selected" ?>>Gender</option>
<option value="<?php echo DisplayConditionUserColumn_coachid ?>" <?php if ($displayConditionField == DisplayConditionUserColumn_coachid) echo "selected" ?>>Coach</option>
<option value="<?php echo DisplayConditionUserColumn_roleid ?>" <?php if ($displayConditionField == DisplayConditionUserColumn_roleid) echo "selected" ?>>Role</option>
<option value="<?php echo DisplayConditionUserColumn_programid ?>" <?php if ($displayConditionField == DisplayConditionUserColumn_programid) echo "selected" ?>>Program</option>
</select>
is
</div>
<div class="<?php if ($displayConditionObject == DisplayConditionObject_UserAccount) echo "showit"; else echo "hideit";?>" id="useraccountdisplaycondition">
<select name="displayconditionuseraccount" onchange="changeDisplayConditionUserAccount()">
<option value="<?php echo DisplayConditionUserAccountColumn_status ?>" <?php if ($displayConditionField == DisplayConditionUserAccountColumn_status) echo "selected" ?>>Account Status</option>
<option value="<?php echo DisplayConditionUserAccountColumn_isbillable ?>" <?php if ($displayConditionField == DisplayConditionUserAccountColumn_isbillable) echo "selected" ?>>Is Billable</option>
</select>
is
</div>
<select name="displayconditionoperator">
<option value="=" <?php echo getDisplayConditionSelection("=", $displayConditionOperator)?>>=</option>
<option value=">" <?php echo getDisplayConditionSelection(">", $displayConditionOperator)?>>&gt;</option>
<option value="<" <?php echo getDisplayConditionSelection("<", $displayConditionOperator)?>>&lt;</option>
<option value="<>" <?php echo getDisplayConditionSelection("<>", $displayConditionOperator)?>>&lt;&gt;</option>
</select>
<div class="
<?php
	if ($displayConditionField == DisplayConditionUserColumn_birthdate) {
		echo "showit";
	} else {
		echo "hideit";
	} ?>
" id="displayconditionfield_birthdate"><input type="text" name="displayconditionvalue" id="displayconditionvalue" value="<?php echo $displayConditionValue?>" dojoType="dijit.form.DateTextBox" required="true" /></div>
<div class="
<?php
	if ($displayConditionField == DisplayConditionUserColumn_gender) {
		echo "showit";
	} else {
		echo "hideit";
	} ?>
" id="displayconditionfield_gender"><select name="displayconditionvalue">
<option value="F" <?php if ($displayConditionValue == "F") echo "selected"?>>female</option>
<option value="M" <?php if ($displayConditionValue == "M") echo "selected"?>>male</option>
</select></div>
<div class="
<?php
	if ($displayConditionField == DisplayConditionUserColumn_coachid) {
		echo "showit";
	} else {
		echo "hideit";
	} ?>
" id="displayconditionfield_coachid">
<?php
	// coach
	$strSQL = "SELECT * FROM users WHERE (roleid & " . Role_Coach  . ") = " . Role_Coach . " and teamid = ? ORDER BY firstname ;";
	$coaches = executeQuery($dbconn, $strSQL, $bError, array($teamid));
?>
<select name="displayconditionvalue">
<?php
	// Build an option list of coaches (only can be modified if not a member)
	foreach ($coaches as $rowcoach) {
		echo( "<option value=\"");
		echo( $rowcoach[ "id"]);
		echo( "\"");
		if ($rowcoach[ "id"] == $displayConditionValue ) {
			echo( " selected");
		}
		echo( ">");
		echo( trim($rowcoach[ "firstname"]) . " " . trim($rowcoach[ "lastname"]));
		echo( "</option>\n");
	}
?>
</select></div>
<div class="
<?php
	if ($displayConditionField == DisplayConditionUserColumn_roleid) {
		echo "showit";
	} else {
		echo "hideit";
	} ?>
" id="displayconditionfield_roleid">
<select name="displayconditionvalue">
<?php
	if (isUser($session, Role_ApplicationAdmin)) {?>
<option value="<?php echo Role_ApplicationAdmin ?>" <?php if ($displayConditionValue == Role_ApplicationAdmin) echo("selected") ?>><?php echo roleToStr( Role_ApplicationAdmin, $teamterms)?></option>
<option value="<?php echo Role_TeamAdmin?>" <?php if ($displayConditionValue ==Role_TeamAdmin) echo("selected") ?>><?php echo roleToStr( Role_TeamAdmin, $teamterms)?></option>
<?php
	} ?>
<option value="<?php echo Role_Coach?>" <?php if ($displayConditionValue == Role_Coach) echo("selected") ?>><?php echo roleToStr( Role_Coach, $teamterms)?></option>
<option value="<?php echo Role_Member?>" <?php if ($displayConditionValue == Role_Member) echo("selected") ?>><?php echo roleToStr( Role_Member, $teamterms)?></option>
</select>
</div>
<div class="
<?php
	if ($displayConditionField == DisplayConditionUserColumn_programid) {
		echo "showit";
	} else {
		echo "hideit";
	} ?>
" id="displayconditionfield_programid">
<?php
	// coach
	$strSQL = "SELECT * FROM programs WHERE teamid = ? ORDER BY name ;";
	$programs = executeQuery($dbconn, $strSQL, $bError, array($teamid));
?>
<select name="displayconditionvalue">
<?php
	foreach ($programs as $rowprogram) {
		echo( "<option value=\"");
		echo( $rowprogram[ "id"]);
		echo( "\"");
		if ($rowprogram[ "id"] == $displayConditionValue ) {
			echo( " selected");
		}
		echo( ">");
		echo( trim($rowprogram[ "name"]));
		echo( "</option>\n");
	}
?>
</select></div>
<div class="
<?php
	if ($displayConditionField == DisplayConditionUserAccountColumn_status) {
		echo "showit";
	} else {
		echo "hideit";
	} ?>
" id="displayconditionfield_status">
<select name="displayconditionvalue">
<option value="<?php echo UserAccountStatus_Inactive ?>" <?php if (UserAccountStatus_Inactive == $displayConditionValue) echo "selected"?>><?php echo $aStatus[UserAccountStatus_Inactive+UserAccountStatus_ArrayOffset]?></option>
<option value="<?php echo UserAccountStatus_Active ?>" <?php if (UserAccountStatus_Active == $displayConditionValue) echo "selected"?>><?php echo $aStatus[UserAccountStatus_Active+UserAccountStatus_ArrayOffset]?></option>
<option value="<?php echo UserAccountStatus_Overdue ?>" <?php if (UserAccountStatus_Overdue == $displayConditionValue) echo "selected"?>><?php echo $aStatus[UserAccountStatus_Overdue+UserAccountStatus_ArrayOffset]?></option>
<option value="<?php echo UserAccountStatus_Disabled ?>" <?php if (UserAccountStatus_Disabled == $displayConditionValue) echo "selected"?>><?php echo $aStatus[UserAccountStatus_Disabled+UserAccountStatus_ArrayOffset]?></option>
</select></div>
<div class="
<?php
	if ($displayConditionField == DisplayConditionUserAccountColumn_isbillable) {
		echo "showit";
	} else {
		echo "hideit";
	} ?>
" id="displayconditionfield_isbillable">
<select name="displayconditionvalue">
<option value="0" <?php if (0 == $displayConditionValue) echo "selected"?>>No</option>
<option value="1" <?php if (1 == $displayConditionValue) echo "selected"?>>Yes</option>
</select></div></td>
</tr>
</table>
</div>
</div>
</div>
<input type="submit" value="Save custom field" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'manage-custom-fields.php<?php buildRequiredParams($session) ?>'"/>
</form>
<?php
	}
}
// Start footer section
include('footer.php'); ?>
</body>
</html>

<?php
// Get the selection state of the customdisplayobject select form field
function getDisplayConditionSelection( $itemname, $displayConditionObject) {
	if (strcmp($displayConditionObject, $itemname) == 0) {
		return " selected ";
	} else {
		return "";
	}
}
?>
