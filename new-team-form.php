<?php 
// The title is set here and rendered in header.php
$title= " New Team " ;
include('header.php'); 
$bError = false;

if (!isUser( $session, Role_ApplicationAdmin)){
	$bError = true;
}

$dbh = getDBH($session);  

if (!$bError){ ?>
<h3><?php echo $title?></h3>
<form action="/1team/new-team.php" method="post">
<?php buildRequiredPostFields($session) ?>
<h4><a class="linkopacity" href="javascript:togglevis('teaminfo')">Team Information<img src="img/a_collapse.gif" alt="collapse section" id="teaminfo_img" border="0"></a></h4>
<div class="showit" id="teaminfo">
<div class="group">
<div class="indented-group-noborder">
<table class="noborders">
<tr >
<td><b>Team Name</b></td>
<td><input type="text" value="" name="teamname" ></td>
</tr>
<tr >
<td><b>Team Activity</b></td>
<td><input type="text" value="" name="activityname" ></td>
</tr>
<tr >
<td><b>Date started <?php echo appname?></b></td>
<td><input type="text" name="startdate" id="date" value="<?php echo date("Y-m-d")?>" dojoType="dijit.form.DateTextBox" required="true" />
</td>
</tr>
<tr >
<td><b>Street Address</b></td>
<td><input type="text" value="" name="address1" ></td>
</tr>
<tr >
<td><b>Street Address 2</b></td>
<td><input type="text" value="" name="address2"></td>
</tr>
<tr >
<td><b>City</b></td>
<td><input type="text" value="" name="city" ></td>
</tr>
<tr >
<td><b>State</b></td>
<td ><input type="text" value="" name="state"></td>
</tr>
<tr >
<td><b>Zip code</b></td>
<td ><input type="text" value="" name="postalcode" ></td>
</tr>
<tr >
<td><b>Phone</b></td>
<td ><input type="text" value="" name="phone" ></td>
</tr>
<tr >
<td><b>Email</b></td>
<td><input type="text" value="" name="email" ></td>
</tr>
<tr >
<td><b>Web Site</b></td>
<td><input type="text" value="http://" name="website" ></td>
</tr>
<tr >
<td><b>Payment Web Site</b></td>
<td><input type="text" value="http://" name="paymenturl"></td>
</tr>
<tr >
<td><b>Notes</b></td>
<td ><input type="text" value="" name="notes"></td>
</tr>
<tr >
<td><b>Referred by</b></td>
<td ><input type="text" value="" name="referredby"></td>
</tr>
<tr>
<td class="attention">Is Billable</td>
<td><input type="checkbox" name="isbillable" value="1" checked="checked" >
</td>
</tr>
<tr>
<td class="attention">Plan</td>
<td>
<select name="plan">
<option value="<?php echo TeamAccountPlan_Undefined?>" selected>Select a plan...</option>
<?php
			$numPlans = count($aTeamCost);
			// Skip first array entry since that represents "undefined"
			for ($loopPlans = 1; $loopPlans < $numPlans; $loopPlans++){
?>
<option value="<?php echo $aTeamPlanMaxMembers[$loopPlans]?>">Up to <?php echo  $aTeamPlanMaxMembers[$loopPlans]. " " . $teamterms["termmember"]?>s</option>
<?php
			}?>
</select>
</td>
</tr>
<tr>
<td class="attention">Plan duration</td><td>
<select name="planduration" >
<option value="<?php echo TeamAccountPlanDuration_Undefined?>" selected>Select a plan duration...</option>
<option value="<?php echo TeamAccountPlanDuration_Unlimited?>" ><?php echo $aTeamPlanDuration[1]?></option>
<option value="1" >1 month</option>
<option value="3" >3 months</option>
<option value="6" >6 months</option>
<option value="12" >12 months</option>
<option value="24" >24 months</option>
<option value="36" >36 months</option>
</select>	
</td>
</tr>
</table>
<input type="submit" value="Create new team" name="new" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
</form>
</div>
</div>
</div>
<?php
}
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["created"])){
	showMessage("New Team Created", appname . "&nbsp;has created the team " . $teamname);
} 
// Start footer section
include('footer.php'); ?>
</body>
</html>