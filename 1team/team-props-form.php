<?php 
// The title is set here and rendered in header.php
$title= " Team Properties " ;
include('header.php');
$bError = false;
// TeamID is passed in for application admin,  else { it's in the session
if ((!isUser( $session, Role_ApplicationAdmin)) && (isset($session["teamid"]))) { 
	$teamid = $session["teamid"];
} else { 
	$teamid = 0;
}
// if (they are an application admin, get from GET
if ((isUser( $session, Role_ApplicationAdmin)) && (isset($_GET["teamid"]))) {
	$teamid = $_GET["teamid"];
}

$dbh = getDBH($session);  
$teamname = getTeamName2( $teamid, $dbh);
 
$title.= " : " . $teamname;

// This controls what is editable for app admins
$enableControlAppAdmin = "";
if (!isUser( $session, Role_ApplicationAdmin)) {
	$enableControlAppAdmin = "disabled";
}

// This controls what is editable for team admins
$enableControlTeamAdmin = "";
if (! isAnyAdminLoggedIn($session)) {
	$enableControlTeamAdmin = "disabled";
}

if ($bError) { 
	redirectToLogin();
} else {	
	$strSQL = "SELECT teams.id as id_team, teams.*, teamaccountinfo.*, images.* FROM teamaccountinfo, teams LEFT OUTER JOIN images ON (images.teamid = teams.id and images.type = ?) WHERE teamaccountinfo.teamid = teams.id AND teams.id = ?";
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array(ImageType_Team, $teamid, $teamid));
	$row = $pdostatement->fetch(PDO::FETCH_ASSOC);
?>
<h3><?php echo $title?></h3>
<?php
	// App admin can page thru the list of teams
	if (isUser( $session, Role_ApplicationAdmin)) { 
		// Get the num of teams
		$strSQL = "SELECT COUNT(*) AS ROW_COUNT FROM teams;";
		$pdostatementnumteams = $dbh->prepare($strSQL);
		$pdostatementnumteams->execute();
		$numteams = $pdostatementnumteams->fetchColumn();
?>
<div class="navtop">
<ul> 
<li><div align="left"><a id="prev" class="linkopacity" 
<?php
			if ($teamid > 1) { ?>
href="team-props-form.php?<?php echo returnRequiredParams($session) ?>&id=<?php echo $teamid-1?>"
<?php
			} ?>
><img src="img/a_previous.gif" border="0" alt="previous">Previous team</a></div></li>	
<li><a href="member-roster.php<?php buildRequiredParams($session)?>&teamid=<?php echo $teamid?>">Team roster</a></li>	
<li><div align="right"><a id="next" class="linkopacity" 
<?php
			if ((($teamid+1) <= $numteams)) { 
?>
href="team-props-form.php?<?php echo returnRequiredParams($session) ?>&id=<?php echo $teamid+1?>"
<?php
			}
?>
><img src="img/a_next.gif" border="0" alt="next">Next team</a></div></li>
</ul>
</div>
<?php
	}
	if (isset($row["id_team"])) { 
		$adminid = $row["adminid"];

?>
<form action="/1team/team-props.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="id" value="<?php echo  $teamid ?>"/>	
<h4 class="expandable"><a class="linkopacity" href="javascript:togglevis('teaminfo')">Team Information<img src="img/a_collapse.gif" alt="collapse section" id="teaminfo_img" border="0"></a></h4>
<div class="showit" id="teaminfo">
<div class="group">
<div class="indented-group-noborder">
<table class="noborders">
<tr >
<td><b>Team Name</b></td>
<td><input type="text" length="80" value="<?php echo $row["name"]?>" name="teamname" dojoType="dijit.form.ValidationTextBox" required="true" propercase="true" promptMessage="Enter team name." invalidMessage="Team name is required." trim="true" <?php echo $enableControlAppAdmin?>></td>
</tr>
<tr >
<td><b>Team Activity</b></td>
<td><input type="text" length="80" value="<?php echo htmlspecialchars($row["activityname"] )?>" name="activityname" <?php echo $enableControlTeamAdmin?>></td>
</tr>
<?php
		if (isUser( $session, Role_ApplicationAdmin)) { ?>
<tr >
<td valign="top"><b><?php echo $teamterms["termadmin"]?></b></td>
<td>
<?php
			// Figure out if there is a current Team Admin. If so create a link to the admin. If not, create a link to create a new user.
			$strSQL = "SELECT * from users where roleid & " . Role_TeamAdmin . " = " . Role_TeamAdmin . " and teamid = ?;";
			$pdostatementadmin = $dbh->prepare($strSQL);
			$pdostatementadmin->execute(array($teamid));	
			$adminuserResults = $pdostatementadmin->fetchAll();
			$adminuserCount = count($adminuserResults);
			if ($adminuserCount < 1) {?>
No administrative users exist for the team <?php echo $row["name"]?>.<br /> <a href="/1team/new-user-form.php?<?php echo returnRequiredParams($session)?>&teamid=<?php echo $teamid?>&roleid=<?php echo Role_TeamAdmin?>">Create an admin</a>.
<?php			
			} else {
				foreach ($pdostatementadmin as $rowadmin) { 
					$adminname = $rowadmin["firstname"] . " " . $rowadmin["lastname"];
	?>
	<a href="user-props-form.php<?php buildRequiredParams($session)?>&id=<?php echo $rowadmin["id"]?>"><?php echo $adminname?></a><br>
	<?php
				} 
				// In any case, give the app admin the option of selecting another team admin 
 				$strSQL = "SELECT * FROM users WHERE teamid = ? AND roleid & " . Role_TeamAdmin . " = " . Role_TeamAdmin . " ORDER BY firstname;";
				$pdostatementuser = $dbh->prepare($strSQL);
				$pdostatementuser->execute(array($teamid));	
?>
To change administrator, select another user. The user must have a role of <?php echo $teamterms["termadmin"]?> to be selected as the <?php echo $teamterms["termadmin"]?>.<br>
<select name="adminid">
<option value="<?php echo User::UserID_Undefined?>"></option>
<?php 
				foreach ($pdostatementuser as $rowuser) { 
					echo( '<option value="');
					echo( $rowuser["id"]);
					echo( '"');
					if (($rowuser["id"] == $adminid)) { 
						echo(" selected");
					}
					echo( ">");
					echo( $rowuser["firstname"]);
					echo( " ");
					echo( $rowuser["lastname"] . ": " . roleToStr($rowuser["roleid"], $teamterms));
					echo( "</option>");
				}?>
</select>	
<?php
			} ?>			
</td>
</tr>
<?php
		// End set team admin
		} 
		// Set coach
		if (isAnyAdminLoggedIn( $session)) { 
?>
<tr >
<td valign="top"><b>Team <?php echo $teamterms["termcoach"]?></b></td>
<td>
<?php
			// Figure out if there is a Team Coach. If so create a link to the coach. If not, create a link to create a new coach user.
			$coachid = $row["coachid"];
			if ((strlen($coachid) > 0) && ($coachid != 0)) {?>
<a href="user-props-form.php<?php buildRequiredParams($session)?>&id=<?php echo $coachid?>"><?php echo getUserName($coachid )?></a><br>
<?php
			// No coach user found, give option of creating a new user and put the coachid in a hidden form field 
			} else { 
				$coachid = 0;
			}	
			
			// In any case, give the app coach the option of selecting another team coach 
			// A coach can be a coach or an admin role
			$strSQL = "SELECT * FROM users WHERE teamid = ? AND ((roleid & " . Role_Coach . " = " . Role_Coach . ") OR (roleid & " . Role_TeamAdmin . " = " . Role_TeamAdmin . ")) ORDER BY firstname;";
			$pdostatementuser = $dbh->prepare($strSQL);
			$pdostatementuser->execute(array($teamid));	
			$coachuserResults = $pdostatementuser->fetchAll();
			$coachuserCount = count($coachuserResults);
			if ($coachuserCount < 1) {?>
No <?php echo $teamterms["termcoach"]?> users exist for the <?php echo $teamterms["termteam"]?> <?php echo $row["name"]?>.<br /> <a href="/1team/new-user-form.php?<?php echo returnRequiredParams($session)?>&teamid=<?php echo $teamid?>&roleid=<?php echo Role_Coach?>">Create a <?php echo $teamterms["termcoach"]?></a>.
<?php			
			} else {
?>
To change <?php echo $teamterms["termcoach"]?>, select another <?php echo $teamterms["termmember"]?>. The <?php echo $teamterms["termmember"]?> must have a <?php echo $teamterms["termcoach"]?> role to be selected as the <?php echo $teamterms["termteam"]?> <?php echo $teamterms["termcoach"]?>.<br>
<select name="coachid">
<option value="0">Remove <?php echo $teamterms["termcoach"]?> assignment</option>
<?php 
				$countRows = 0;
				while ($countRows < $coachuserCount) { 
					echo( '<option value="');
					echo( $coachuserResults[$countRows]["id"]);
					echo( '"');
					if (($coachuserResults[$countRows]["id"] == $coachid)) { 
						echo(" selected");
					}
					echo( ">");
					echo( $coachuserResults[$countRows]["firstname"]);
					echo( " ");
					echo( $coachuserResults[$countRows]["lastname"] . ": " . roleToStr($coachuserResults[$countRows]["roleid"], $teamterms));
					echo( "</option>");
					$countRows ++;
				}?>
</select>				
<?php		} ?>
</td>
</tr>
<?php
		// End set coach
		}
?>
<?php
		$startdate = $row["startdate"];
?>
<tr >
<td><b>Date started <?php echo appname?></b></td>
<td><input type="text" value="<?php echo $startdate?>" name="startdate" <?php echo $enableControlAppAdmin?>></td>
</tr>
<tr >
<td><b>Street Address</b></td>
<td><input type="text" value="<?php echo htmlspecialchars($row["address1"] )?>" name="address1" <?php echo $enableControlTeamAdmin?>></td>
</tr>
<tr >
<td><b>Street Address 2</b></td>
<td><input type="text" value="<?php echo htmlspecialchars($row["address2"] )?>" name="address2" <?php echo $enableControlTeamAdmin?>></td>
</tr>
<tr >
<td><b>City</b></td>
<td><input type="text" value="<?php echo htmlspecialchars($row["city"] )?>" name="city" <?php echo $enableControlTeamAdmin?>></td>
</tr>
<tr >
<td><b>State</b></td>
<td ><input type="text" value="<?php echo htmlspecialchars($row["state"] )?>" name="state" <?php echo $enableControlTeamAdmin?>></td>
</tr>
<tr >
<td><b>Zip code</b></td>
<td ><input type="text" value="<?php echo htmlspecialchars($row["postalcode"] )?>" name="postalcode" <?php echo $enableControlTeamAdmin?>></td>
</tr>
<tr >
<td><b>Phone</b></td>
<td ><input type="text" value="<?php echo htmlspecialchars($row["phone"] )?>" name="phone" <?php echo $enableControlTeamAdmin?>></td>
</tr>
<tr >
<td><b>Email</b></td>
<td><input type="text" value="<?php echo htmlspecialchars($row["email"] )?>" name="email" <?php echo $enableControlTeamAdmin?>></td>
</tr>
<tr >
<td><b>Web Site</b></td>
<td><input type="text" value="<?php echo htmlspecialchars($row["website"] )?>" name="website" <?php echo $enableControlTeamAdmin?>></td>
</tr>
<tr >
<td><b>Payment Web Site</b></td>
<td><input type="text" value="<?php echo htmlspecialchars($row["paymenturl"] )?>" name="paymenturl" <?php echo $enableControlTeamAdmin?>></td>
</tr>
<?php
		if (isUser( $session, Role_ApplicationAdmin)) { 
?>	
<tr >
<td><b>Notes</b></td>
<td ><input type="text" value="<?php echo htmlspecialchars($row["notes"] )?>" name="notes"></td>
</tr>
<?php
		} 
		
		// Set logo
		if (isAnyAdminLoggedIn($session)) { 
?>
<tr>
<td><span class="bold">Introductory email body</span><br>Use this field to customize the initial email<br>your <?php echo $teamterms["termmember"]?>s get after you create their<br>account and generate a password.</td><td><textarea rows="5" cols="120" value="" name="introtext" wrap="hard"><?php echo $row["introtext"]?></textarea></td></tr>
<?php 		// End Admin set of logo, IP Address, and regular class event
		} ?>
</table>
<?php
		// Only admins can submit
		if (isAnyAdminLoggedIn($session)) { ?>	
<input type="submit" value="Update" name="new" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<?php
		}
?>
</form>
</div>
</div>
</div>
<?php
		// Team image
		if ((isAnyAdminLoggedIn( $session)) || ((!is_null($row["imageid"])) || ($row["imageid"] != ImageID_Undefined))) { 
?>
<h4 class="expandable"><a class="linkopacity" href="javascript:togglevis('teamimg')">Team Picture<img src="img/a_collapse.gif" alt="collapse section" id="teamimg_img" border="0"></a></h4>
<div class="showit" id="teamimg">
<div class="group">
<div class="indented-group-noborder">
<?php		// Conditionally display image
			if ((!is_null($row["url"])) && (is_url($row["url"]))) {?>
<img src="<?php echo $row["url"]?>" id="" border=0">
<?php		
			} else if (isset($row["filename"])){ ?>
<img src="<?php echo uploadsDir."/$teamid/".$row["filename"]?>" id="" border=0">
<?php
			}
			// Only admins get form
			if (isAnyAdminLoggedIn( $session)) {?>
<form action="image-upload.php" method="post" enctype="multipart/form-data">
<p>Set your <?php echo $teamterms["termteam"]?> image by selecting a web-based image address (URL) or upload a file. All images must be in JPEG, GIF, or PNG format. Any web-based image
overrides file uploads.</p>
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid?>"/>	
<input type="hidden" name="type" value="<?php echo ImageType_Team?>"/>	
<input type="hidden" name="objid" value="<?php echo  $teamid ?>"/>	
<input type="hidden" name="teamname" value="<?php echo $teamname ?>" />	
<p class="strong">Team Image URL&nbsp;<input type="text" value="<?php echo htmlspecialchars($row["url"] )?>" name="url"></p>
<p class="strong">File&nbsp;<input type="file" name="image" name="image" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/></p>
<input type="submit" value="Save Team Picture" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
</form>
<?php		}
		} // End image div ?>
</div></div></div>
<?php 	// Team Account info. Must be admin to see account info. Application admin can edit. Team admin can only read.
		if (isAnyAdminLoggedIn( $session)) { ?>
<form id="formteamaccountinfo" action="/1team/team-account-props.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="id" value="<?php echo  $teamid ?>"/>	
<input type="hidden" name="teamname" value="<?php echo $teamname ?>"/>	
<h4 class="expandable"><a class="linkopacity" href="javascript:togglevis('teamaccountinfo')">Team Account Information<img src="img/a_expand.gif" alt="expand section" id="teamaccountinfo_img" border="0"></a></h4>
<div class="hideit" id="teamaccountinfo">
<div class="group">
<div class="indented-group-noborder">
<table class="noborders">
<?php
			$accountStatus = $row["status"];
			$isbillable = $row["isbillable"];
			$plan = $row["plan"];
			$planduration = $row["planduration"];
?>
<tr>
<td class="attention">Account status</td>
<td>
<select name="status" <?php echo $enableControlAppAdmin?>>
<option value="<?php echo TeamAccountStatus_Inactive?>" <?php if ($accountStatus == TeamAccountStatus_Inactive) { echo("selected"); }?>><?php echo $aTeamStatus[1]?></option>
<option value="<?php echo TeamAccountStatus_Active?>" <?php if ($accountStatus == TeamAccountStatus_Active) { echo("selected"); }?>><?php echo $aTeamStatus[2]?></option>
<option value="<?php echo TeamAccountStatus_Overdue?>" <?php if ($accountStatus== TeamAccountStatus_Overdue) { echo("selected"); }?>><?php echo $aTeamStatus[3]?></option>
</select>				
</td>
</tr>
<tr>
<td class="attention">Billable?</td>
<td><?php
			if (!$isbillable) { ?>
<input type="checkbox" name="isbillable" value="0" <?php echo $enableControlAppAdmin?>>
<?php		} else { ?>
<input type="checkbox" name="isbillable" value="1" checked="checked" <?php echo $enableControlAppAdmin?>>
<?php 		} ?>
</td>
</tr>
<tr>
<td class="attention">Plan</td>
<td>
<select name="plan" <?php echo $enableControlAppAdmin?>>
<?php
			$numPlans = count($aTeamCost);
			// Skip first array entry since that represents "undefined"
			for ($loopPlans = 1; $loopPlans < $numPlans; $loopPlans++){
?>
<option value="<?php echo $aTeamPlanMaxMembers[$loopPlans]?>" <?php if ($plan == $aTeamPlanMaxMembers[$loopPlans]) { echo("selected"); }?>>Up to <?php echo  $aTeamPlanMaxMembers[$loopPlans]. " " . $teamterms["termmember"]?>s</option>
<?php
			}?>
</select>
</td>
</tr>
<tr>
<td class="attention">Plan duration</td><td>
<select name="planduration" <?php echo $enableControlAppAdmin?>>
<option value="<?php echo TeamAccountPlanDuration_Undefined?>" <?php if ($plan == TeamAccountPlan_Undefined) { echo("selected"); }?>>Select a plan duration</option>
<option value="12" <?php if ($planduration == 12) { echo("selected"); }?>>12 months</option>
</select>
</td>
</tr>
</table>
<?php
			// Only application admin gets the submit button
			if (isUser( $session, Role_ApplicationAdmin)) { 
?>	
<input type="submit" value="Update" name="new" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<?php
			}
			
			// Continue with Team Settings that only admins can see: terms
?>		
</form>
</div>
</div>
</div>

<form id="formteamterminology" action="/1team/team-terms-props.php" method="post">
<input type="hidden" name="id" value="<?php echo  $teamid ?>"/>	
<input type="hidden" name="teamname" value="<?php echo  $teamname ?>"/>	
<?php buildRequiredPostFields($session) ?>
<h4 class="expandable"><a class="linkopacity" href="javascript:togglevis('teamterminology')">Team Terminology Settings<img src="img/a_expand.gif" alt="expand section" id="teamterminology_img" border="0"></a></h4>
<div class="hideit" id="teamterminology">
<div class="group">
<div class="indented-group-noborder">
<table class="noborders">
<tr>
<td class="attention">What do you call the group or team?</td>
<td><input type="text" value="<?php echo $teamterms["termteam"]?>" name="termteam"></td>
</tr>
<tr>
<td class="attention">What do you call the person who administers finance and operations?</td>
<td><input type="text" value="<?php echo $teamterms["termadmin"]?>" name="termadmin"></td>
</tr>
<tr>
<td class="attention">What do you call the person who runs team meetings?</td>
<td><input type="text" value="<?php echo $teamterms["termcoach"]?>" name="termcoach"></td>
</tr>
<tr>
<td class="attention">What do you call members of your team?</td>
<td><input type="text" value="<?php echo $teamterms["termmember"]?>" name="termmember"></td>
</tr>
<tr>
<td class="attention">What do you call team meetings?</td>
<td><input type="text" value="<?php echo $teamterms["termclass"]?>" name="termclass"></td>
</tr>
<tr>
<td><input type="submit" value="Update" name="new" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
</td>
</table>
</form>
</div>
</div>
</div>
<?php	
		// End admin Team Account Info
		}
	}
// End bError = FALSE
}
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["done"])){
	showMessage("Team Properties Updated", appname . "&nbsp;has updated the settings for the team " . $teamname);
} else if (isset($_GET["new"])){
	showMessage("New Team Created", appname . "&nbsp;has created the team " . $teamname);
} else if (isset($_GET["err"])){
	showError("Error updating properties", appname . "&nbsp;could not update the team properties. Code:" . $_GET["err"], "");
} 
 
// Start footer section
include('footer.php'); ?>
</body>
</html>

