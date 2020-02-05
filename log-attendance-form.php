<?php
// Only admins can execute this script. Header.php enforces this.
$isadminorcoachrequired = true;
$title= " Log Attendance " ;
include('header.php'); ?>
<script type="text/javascript"> 
dojo.require("dijit.form.DateTextBox");
</script>
<?php
$bError = false;
$teamid = NotFound;
$err = "";
// teamid depends on who is calling
if (isUser($session, Role_TeamAdmin)){
	$teamid = getTeamID($session);
} else {
	if (isset($_REQUEST["teamid"])){
		$teamid = $_REQUEST["teamid"];
	} else{
		$bError = true;
		$err = "t";
	}
}

if (isset($_GET["id"])){
	$userid = trim($_GET["id"]);
} else {
	// This setting will force select list to be uninitialized
	$userid = 0;
} 
$dbh = getDBH($session); 
$bDisplayUserSelector = true;
// Conditionally include user name in title
if ( $userid >= UserID_Base ) { 
	// $bDisplayUserSelector = false;?>
<h3><?php echo $title?>&nbsp;for&nbsp;<a href="user-props-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $userid?>"><?php echo getUserName($userid)?></a></h3>
<?php
} else { 
	echo '<h3>' . $title . ' for ' . getTeamName2($teamid, $dbh) . ' ' . $teamterms["termmember"] . '</h3>';
} 

$bOkForm = true;
// If invalid userID, Build a select list of all billable and active students to allow selection
if ($bDisplayUserSelector) {
	// Team admin and coach must get team id from session
	if (!isUser( $session, Role_ApplicationAdmin)) {
		$teamid = $session["teamid"];
		$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid, useraccountinfo.isbillable FROM users, useraccountinfo WHERE status = " . UserAccountStatus_Active . " and users.teamid = ? and users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
		$pdostatement = $dbh->prepare($strSQL);
		$bError = ! $pdostatement->execute(array($teamid));
	// App Admin query isn't team specific
	} else {
		$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid, useraccountinfo.isbillable FROM users, useraccountinfo WHERE status = " . UserAccountStatus_Active . " and users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
		$pdostatement = $dbh->prepare($strSQL);
		$bError = ! $pdostatement->execute();
	} 
	$userResults = $pdostatement->fetchAll();
	$countRows = 0;	
	$numRows = count($userResults); 
	
	// If no members, tell them so and don't display the form
	if ($numRows == 0) { 
		echo "<p>No " . $teamterms["termmember"]. "s exist in the team " .getTeamName2($teamid, $dbh) . "<br>\n";
		echo '<a href="/1team/new-user-form.php?' . returnRequiredParams($session) . '">Create a team member</a></p>';
		echo "\n";
		$bOkForm = false;
	} 
	
// User passed in, check to see if they are on time with orderitems
} else { 
	// Find out if they are billable and active or not
	$strSQL = "SELECT useraccountinfo.isbillable, useraccountinfo.status FROM useraccountinfo where useraccountinfo.userid = ? AND teamid = ?";
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array($userid, $teamid));
	$useraccountprops = $pdostatement->fetchAll();
	if (count($userprops) == 1){
		$isbillable = $useraccountprops[0]["isbillable"];
		$status = $useraccountprops[0]["status"];  				
	} else {
		$bError = true;
	}
	
	if (($isbillable) && ($status == UserAccountStatus_Active)){
		$strSQL = "SELECT users.firstname, users.lastname, useraccountinfo.isbillable, programs.name as programname, skus.*, skus.name as skuname, orderitems.id as payid, orderitems.* FROM (useraccountinfo INNER JOIN (programs INNER JOIN (users INNER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid) on orderitems.programid = programs.id) on users.useraccountinfo = useraccountinfo.id) WHERE users.id = orderitems.userid and users.id = ? AND orderitems.teamid = ? AND orderitems.numeventsremaining <> 0 and (paymentdate + expires >= current_date) ORDER by paymentdate DESC;";
		$pdostatement = $dbh->prepare($strSQL);
		$pdostatement->execute(array($userid, $teamid));
		$userprops = $pdostatement->fetchAll();
	
		if (count($userprops) > 0){
			$bOkForm = true;
			
		} else {
			echo '<h5 class="billingissue">No payments are found. You must <a href="/1team/new-orderitem-form.php?' . returnRequiredParams($session) . '&teamid=' . $teamid . '&id=' . $userid . '">add an order item</a> before any attendance can be logged.</h5>';
			$bOkForm = false;
		}
	
	// not billable, ok to log attendance
	} elseif ((!$isbillable) && ($status == UserAccountStatus_Active)){
		$bOkForm = true;
	// Inactive, not ok to log
	} else {
		$bOkForm = false;
	} 
}

if (($bOkForm ) && (!$bError)) { ?>
<form action="/1team/log-attendance-admin.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<p>
<table class="noborders">
<?php 
if ($bDisplayUserSelector) { ?>
<tr>
<td class="bold">Member name:</td>
<td><select name="id">
<?php
		echo('<option value="0" selected>Select user...</option>');
		while ($countRows < $numRows) {
			echo "<option value=\"";
			echo $userResults[$countRows]["id"];
			echo "\"";
			if ( $userid == $userResults[$countRows]["id"] ) {
				echo(" selected");
			}
			echo ">";
			echo $userResults[$countRows]["firstname"];
			echo " ";
			echo $userResults[$countRows]["lastname"];
			echo " " . roleToStr($userResults[$countRows]["roleid"], $teamterms) ;
			echo "</option>\n";
			$countRows ++;
		}  ?>
</select></td>
</tr>
<?php
} else { ?>
<input type="hidden" name="id" value="<?php echo $userid ?>"/>
<?php
} ?>
<tr>
<td class="bold">Date</td>
<td><?php 
// Admins can change date. Coaches can only log for today
if ( isAnyAdminLoggedIn($session)){ ?>
<input type="text" name="date" id="date" value="<?php echo date("Y-m-d")?>" dojoType="dijit.form.DateTextBox" required="true" />
<?php 
} else {
	echo date("Y-m-d");?>
<input type="hidden" name="date" value="<?php echo date("Y-m-d") ?>"/>
<?php	
}?>
</td>
<?php

	if ( isUser($session, Role_ApplicationAdmin) ) {
		$strSQL = "SELECT * FROM events order by listorder;";
		$pdostatement = $dbh->prepare($strSQL);
		$pdostatement->execute();		
	} else {
		$strSQL = "SELECT * FROM events WHERE teamid = ? order by listorder;";
		$pdostatement = $dbh->prepare($strSQL);
		$pdostatement->execute(array($session["teamid"]));		
	} ?>
<tr><td class="bold">Event:</td>
<td><select name="eventid">
<?php 
	$rowCount = 0;
	foreach ($pdostatement as $row) { 
		$rowCount++;
		$eventdate = $row["eventdate"];
		if (( strlen($eventdate) < 1 ) || ( is_null($eventdate) )) { 
			$eventdate = "any date";
		}
		$location = $row["location"];
		if (( strlen($location) < 1 ) || ( is_null($eventdate) )) {
			$location = "any location";
		}
		echo '<option value="';
		echo $row["id"];
		echo '"';
		echo ">";
		echo $row["name"] . " on " . $eventdate . " at " . $location;
		echo "</option>";
	}
	if ($rowCount < 1) {
		$bError = true;
		$errorStr = "No members found";
	} ?>
</select></td></tr>
</table>
<input type="submit" value="Certify" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'">
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'home.php?<?php echo returnRequiredParams($session)?>'"/>
</form>
<?php
} 
if ( $bError ) { ?>
<h4 class="usererror">Error: <?php echo $err?></h4>
<?php
} 
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "The attendance was not logged: " . $_GET["err"], "");
} 
// Start footer section
include('footer.php'); ?>
</body>
</html>