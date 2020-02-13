<?php
// Only admins or coaches can execute this script. Header.php enforces this.
$isadminorcoachrequired = true;
$title = " Prepare for Attendance Scanning";
include('header.php');
?>
<script type="text/javascript">
dojo.require("dijit.form.DateTextBox");
</script>

<?php

$bError = false;
$teamid = NotFound;

// teamid depends on who is calling
if (!isUser($session, Role_ApplicationAdmin)){
	$teamid = getTeamID($session);
} else {
	if (isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	} else{
		$bError = true;
	}
}

$teamterms = getTeamTerms($teamid, getDBH($session));
$teaminfo = getTeamInfo($teamid );

// Must be run from team admin console
//$isTeamAttendanceConsole is set in the header if you are a coach or admin
if ($isTeamAttendanceConsole){?>
<h3><?php echo $title . " for " . $teaminfo["teamname"] . " " . $teamterms["termmember"] . "s"?></h3>
<p>Select the event to prepare scanning.</p>
<form name="scaneventform" action="/1team/scan-form.php" method="post">
<?php buildRequiredPostFields($session) ?>
<table class="noborders">
<tr><td class="strong">Event</td><td><?php

	$strSQL = "SELECT * FROM events WHERE teamid = ? and scannable = TRUE ORDER by listorder;";
	$dbconn = getConnection();
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
<input type="hidden" name="eventname" value="<?php if ($loopMax > 0) echo $eventResults[0]["name"]?>"/>
</td></tr>
<tr><td colspan="2"><input type="submit" value="Begin scanning" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/></form></td></tr>
</table>
</form>
<?php
// If not team admin console
} else {
	Header(" Location: default.php");
}
// Start footer section
include('footer.php'); ?>
