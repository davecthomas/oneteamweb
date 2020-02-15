<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Attendance on Date";
include('header.php');
?>
<script type="text/javascript">
dojo.require("dijit.form.DateTextBox");
</script>

<?php
echo "<h3>" . getTitle($session, $title) . "</h3>";

$teamid = NotFound;
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

// Default to today
$inputdate = date("Y-m-d");

// Accept a date on GET or POST
if ((isset($_POST['date']))) {
	$inputdate = $_POST['date'];
} else if ((isset($_GET['date']))) {
	$inputdate = $_GET['date'];
}

// If bad date, default to today
if (! check_date($inputdate) ) {
	$formdate = date("Y-m-d");
	$thedatesql = "current_date";
} else {
	$formdate = $inputdate;
	$thedatesql = ""; // Init below
}

// The date format is Y-m-d due to the dojo calendar widget.
// Create a prettier one for user consumption
$datearray = explode("-",$formdate);
$attendanceCheckDate = new DateTime($datearray[2] . "-" . $datearray[1] . "-" . $datearray[0]);

if (strcmp($thedatesql, "current_date") != 0){
	$thedatesql = "'" . $attendanceCheckDate->format("m-d-Y") . "'";
}
// set up sort order
$sortRequest = "firstname";
if (isset($_GET["sort"])) {
	$sortRequest = trim($_GET["sort"]) . "";
	$sortRequest = cleanSQL($sortRequest);
}

if (isset($_GET["filter"])) {
	$filterRequest = trim($_GET["filter"]);
	$filterRequest = cleanSQL($filterRequest);
} else {
	$filterRequest = "";
}
?>
<form action="/1team/attendance-on-date.php" method="post">
<?php buildRequiredPostFields($session) ?>
<table class="noborders">
<tr>
<td class="bold">Enter a date</td>
<td><input type="text" name="date" id="date" value="<?php echo $formdate ?>" dojoType="dijit.form.DateTextBox" required="true" /></td>
<td>
<input type="submit" value="List attendees" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'">
</td>
</tr>
</table>
</form>
<?php
//	$strSQL = "SELECT attendance.id as attendanceid, attendance.*, events.id as eventid, events.*, users.id as userid, users.*, useraccountinfo.* FROM (events INNER JOIN (attendance INNER JOIN users ON attendance.memberid = users.id) on events.id = attendance.eventid), useraccountinfo WHERE attendance.teamid = ? AND attendancedate = ? and users.useraccountinfo = useraccountinfo.id ORDER BY attendance.attendancedate DESC";
	$strSQL = "SELECT attendance.id as attendanceid, attendance.*, events.id as eventid, events.*, users.id as userid, users.*, useraccountinfo.*, images.* FROM useraccountinfo, events INNER JOIN attendance INNER JOIN teams RIGHT OUTER JOIN images RIGHT OUTER JOIN users ON users.imageid = images.id ON images.teamid = teams.id ON attendance.memberid = users.id on events.id = attendance.eventid WHERE attendance.teamid = ? AND attendancedate = ? and users.useraccountinfo = useraccountinfo.id ORDER BY attendance.attendancedate DESC;";

	$dbconn = getConnectionFromSession($session);
	$results = executeQuery($dbconn, $strSQL, $bError, array($session["teamid"],$thedatesql));
?>
<h4><?php echo getTeamName($teamid, $dbconn)?> Members attending on <?php echo $attendanceCheckDate->format("F j, Y") ?></h4>
<div id="bodycontent">
<?php
	// If none found
	if (count($results ) == 0) { ?>
<?php
	echo "<p>No members found.<br>\n";
// If only one result, go to props page for that user
} else {
	// More than one found, include roster
	$pagemode = pagemodeAttendanceOnDate;
	$referrer = "attendance-on-date.php";
	include('include-member-roster.php');
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
