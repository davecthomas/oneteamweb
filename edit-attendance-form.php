<?php  
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Edit Attendance"; 
include('header.php');
?>
<script type="text/javascript"> 
dojo.require("dijit.form.DateTextBox");
dojo.require("dojo.parser");
dojo.require("dojo.dnd.Source");
</script> 
<?php
$dbh = getDBH($session);
$bError = false;
$teamid = NotFound;
// teamid depends on who is calling
if (isUser($session, Role_TeamAdmin)){
	$teamid = getTeamID($session);
} else {
	if (isset($_REQUEST["teamid"])){
		$teamid = $_REQUEST["teamid"];
	} else{
		$bError = true;
	}
}

if (isset($_GET["id"])) {
	$userid = $_GET["id"];
} else {
	$bError = true;
}

if (isset($_GET["eventid"])) {
	$eventid = $_GET["eventid"];
} else {
	$bError = true;
}

if (isset($_GET["attendanceid"])) {
	$attendanceid = $_GET["attendanceid"];
} else {
	$bError = true;
}

if (isset($_GET["date"])) {
	$attendancedate = $_GET["date"];
} else {
	$bError = true;
}

if (!$bError) { 
	$username = getUserName2($userid, $dbh); ?>
<h3><?php echo $title?>&nbsp;for <a href="user-props-form.php?<?php echo returnRequiredParams($session)?>&id=<?php echo $userid?>" target="_top"><?php echo $username?></a></h3>
<div class="indented-group-noborder">
<?php 
	$strSQL = "SELECT * FROM attendance WHERE id = ? and memberid = ? and teamid = ?;";
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array($attendanceid, $userid, $teamid));
	
	$rs = $pdostatement->fetchAll();
	
	$countResults = count($rs); 
	
	if ($countResults > 0) { ?>
<form action="/1team/edit-attendance.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="attendanceid" value="<?php echo $attendanceid ?>"/>
<input type="hidden" name="memberid" value="<?php echo $userid ?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<input type="hidden" name="objname" value="<?php echo $username ?>"/>
<input type="hidden" name="date" value="<?php echo $attendancedate ?>"/>
<table class="noborders">
<?php 
	
		$strSQL = "SELECT * FROM events WHERE teamid = ? order by listorder;";
		$pdostatement = $dbh->prepare($strSQL);
		$pdostatement->execute(array($teamid));
		
		$eventsResults = $pdostatement->fetchAll();
		
		$countResults = count($eventsResults); 
		
		if ($countResults > 0) { ?>
<tr><td class="bold">Event:</td>
<td><select name="eventid">
<?php
			$countRow = 0;
			while ($countRow < $countResults) {
				echo '<option value="';
				echo $eventsResults[$countRow]["id"];
				echo '"';
				if ($eventsResults[$countRow]["id"] == $eventid) { 
					echo(" selected");
				}
				echo ">";
				echo $eventsResults[$countRow]["name"] . " " . $eventsResults[$countRow]["eventdate"] . " " . $eventsResults[$countRow]["location"];
				echo "</option>\n";
				
				$countRow++;
			} ?>
</select></td></tr>
<?php
		}
		$datetimeevent = new DateTime($attendancedate);
?>
<tr><td class="bold">Date</td><td><input type="text" name="attendancedate" id="attendancedate" value="<?php echo $rs[0]["attendancedate"]?>" dojoType="dijit.form.DateTextBox" required="true" promptMessage="mm/dd/yyyy"/></td></tr>
<tr>
</table>
</div>
<input type="submit" value="Modify attendance" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'include-attendance-table.php?<?php echo returnRequiredParams($session) . "&mode=user&teamid=" . $teamid . "&id=" . $userid . "&pagemode=standalone&name=" . urlencode($username) . "&EventDate=01-" . $datetimeevent->format("m") . "-" . $datetimeevent->format("Y");?>'"/>
</form>
<?php
	}  
} else {
	// Can't do a redirect since we've already included the header
	echo "Error";
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
