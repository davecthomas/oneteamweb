<?php
include_once("utils.php");
// Manage who mode: user or team
$whomode = "user";
if (isset($_REQUEST["whomode"])) {
	$whomode = getCleanInput($_REQUEST["whomode"]);
}

// Manage page mode: embedded or standalone
if (isset($_REQUEST["pagemode"])) {
	$pagemode = getCleanInput($_REQUEST["pagemode"]);
} else {
	$pagemode = "standalone";
}
// Standalone: init the session
if ($pagemode == "standalone") {
	$title= " Attendance History " ;
	include('header.php');
	// id is required and you must be able to admin this id
	if (isset($_REQUEST["id"])){
		if ($whomode == "user")	{
			$userid = $_REQUEST["id"];
			if (!canIAdministerThisUser( $session, $userid)) redirectToLogin();
			$id = $userid;
		}
		else {
			if (isUser($session, Role_TeamAdmin)) $teamid = $session["teamid"];
			else $teamid = $_REQUEST["id"];
			$id = $teamid;
		}
	} else {
		redirect( $_SERVER['HTTP_REFERER'] . "&err=1");
	}
}
if (isset($session["teamid"])){
	$teamid = $session["teamid"];
}

if (isset($_REQUEST["programid"])) {
	$programid = $_REQUEST["programid"];
	if ($programid == Program_Undefined) $sqlprogram = "";
	else $sqlprogram = " AND programid = " .$programid;
} else {
	// Default triggers all programs
	$programid = Program_Undefined;
	$sqlprogram = "";
}
 // Get Event Date
if (isset($_REQUEST["EventDate"])) {
	$eventdatetime = new DateTime(getCleanInput($_REQUEST["EventDate"]));
} else {
	$eventdatetime = new DateTime();
}
$dbh = getDBH($session);  
// User mode: make sure they can adminster this user
if ($whomode == "user") {
	$objid = $userid;
	$objname = getUserName2($userid, $dbh);
	$sqlwhere = "attendance.memberid"; ?>
<h5>Attendance for <a target="_top" href="user-props-form.php<?php buildRequiredParams($session)?>&id=<?php echo $userid?>" target="_top"><?php echo $objname?></a> for <?php echo $eventdatetime->format("F")?>, <?php echo $eventdatetime->format("Y")?></h5>
<?php
} else {
	$sqlwhere = "attendance.teamid";
	$objid = $teamid;
	$objname = getTeamName2($teamid, $dbh); ?>
<h5>Attendance for <a target="_top" href="team-props-form.php<?php buildRequiredParams($session)?>&id=<?php echo $teamid?>" target="_top"><?php echo $objname?></a> for <?php echo $eventdatetime->format("F")?>, <?php echo $eventdatetime->format("Y")?></h5>
<?php
}



// Standalone: init the session
if ($pagemode == "standalone") {
	$expandimg = "collapse";
	$expandclass = "showit"; 

	// Display previous and next links at the top of the table
	// Only display prev/next for app admin, since this data spans teams
	if ((isUser( $session, Role_ApplicationAdmin)) && ($whomode == "user")) { ?>
<p></p>
<div class="navtop">
<ul id="nav">
<li><a href="<?php if ($userid > 1 ) echo "user-props-form.php?id=" . ($userid-1) . buildRequiredParamsConcat($session)?>"><img src="img/a_previous.gif" border="0" alt="previous">Previous member</a></li>
<li><a class="linkopacity" href="user-props-form.php?id=<?php echo($userid+1) . buildRequiredParamsConcat($session)?>">Next member<img src="img/a_next.gif" border="0" alt="next"></a></li>
</ul>
</div><p></p>
<?php
	}?>
<script type="text/javascript">
function updateProgramID() {
	document.forms['selectprogramform'].submit();
}
</script>
<form name="selectprogramform" action="/1team/include-attendance-calendar.php" method="post">
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<input type="hidden" name="whomode" value="<?php echo $whomode ?>"/>
<input type="hidden" name="id" value="<?php echo $id ?>"/>
<?php buildRequiredPostFields($session) ?>
<?php
	$dbh = getDBH($session);  
	// GEt payment methods for this team
	$strSQL = "SELECT * FROM programs WHERE teamid = ?";
	$pdostatementP = $dbh->prepare($strSQL);
	$bError = ! $pdostatementP->execute(array($teamid));
	$programResults = $pdostatementP->fetchAll();
	$rowCountP = count( $programResults);

	// Display programs for this team
	if ($rowCountP > 0) {
		$countRowsP = 0; ?>
<table class="noborders">
<tr><td class="bold">Display attendance for program</td><td>
<select name="programid" onchange="updateProgramID();">
<option value="<?php echo Program_Undefined?>" <?php if ($programid == Program_Undefined) echo " selected"?>>All programs</option>
<?php
		while ($countRowsP < $rowCountP) {
			echo '<option value="';
			echo $programResults[$countRowsP]["id"];
			echo '"';
			if ($programid == $programResults[$countRowsP]["id"]) echo " selected";
			echo ">";
			echo $programResults[$countRowsP]["name"];
			echo "</option>\n";
			$countRowsP ++;
		} ?>
</select>
</td></tr>
</table>
</form>
<?php
	} else {
		echo '<p>There are no programs defined for the team ' . getTeamName2($teamid, $dbh). '. <a href="/1team/manage-programs-form.php?' . returnRequiredParams($session) . '&teamid=' . $teamid .'">Define programs</a>.';
	} ?>
<div class="push"></div>
<div class="indented-group-noborder">

<?php
// Embedded mode: userid is required for user mode. Teamid is required for team mode
} else {
	$expandimg = "expand";
	$expandclass = "hideit";

	// These are required in embedded mode	
	if (($whomode == "user") && (!isset($userid))) {
		redirectToLogin();
	} 
	if (($whomode == "team") && (!isset($teamid))) {
		redirectToLogin();
	} 
} 

// Team mode must be admin
if ( $whomode == "team") {

	redirectToLoginIfNotAdmin( $session); 
}

$urlencodedName = htmlspecialchars($objname);


// Today's numeric day of the month
$todaysDayOfMonth = date("j");
$todaysMonthNum = date("m");

$CurMonthNum = $eventdatetime->format("n");
$CurMonthName = $eventdatetime->format("M");
$CurYearNum = $eventdatetime->format("Y");
// Number of days in month
$numDaysInMonth = $eventdatetime->format("t");

// Get the date of the first day of the month. 
$FirstDayofMontharray = explode("-",$eventdatetime->format("m-d-Y"));
$FirstDayofMonthdatetime = new DateTime("01-" . $FirstDayofMontharray[0] . "-" . $FirstDayofMontharray[2]);
$CurDayNumdatetime = $FirstDayofMonthdatetime;
$temp = new DateTime("01-" . $FirstDayofMontharray[0] . "-" . $FirstDayofMontharray[2]);

// For links to prev/next
$prevmonth = new DateTime($eventdatetime->format("F j, Y"));
$prevmonth->modify("-1 month");
$nextmonth = new DateTime($eventdatetime->format("F j, Y"));
$nextmonth->modify("+1 month");
$prevyear = new DateTime($eventdatetime->format("F j, Y"));
$prevyear->modify("-1 year");
$nextyear = new DateTime($eventdatetime->format("F j, Y"));
$nextyear->modify("+1 year");

// Get the 1-based index of the first day of the month. Needed to correctly pad the first day of the month in the table
$FirstDayIndex = $FirstDayofMonthdatetime->format("N");
// This is off by 1, since the result has Monday = 1, but our calendar has Sunday as 1
if ($FirstDayIndex == 7) $FirstDayIndex = 1;
else $FirstDayIndex ++;
//echo "FirstDayIndex  =" . $FirstDayIndex  . "<br>";
$CurDayNum = $FirstDayofMonthdatetime->format("j");

if (isset($_REQUEST["EventDateEnd"])) {
	$lastdaydateReqdatetime = new DateTime($_REQUEST["EventDateEnd"]);
	$lastDayofMonthdatetime = $lastdaydateReqdatetime;
} else {
	$lastDayofMonthdatetime = new DateTime($FirstDayofMonthdatetime->format("d-m-Y") . " +1 month");
}

$strSQL = "SELECT attendance.*, events.* FROM events INNER JOIN attendance on events.id = attendance.eventid WHERE " . $sqlwhere . " = ? AND (attendancedate >= ? and attendancedate < ?)" . $sqlprogram . " ORDER BY attendancedate;";

$pdostatement = $dbh->prepare($strSQL);
$pdostatement->execute(array($objid, $FirstDayofMonthdatetime->format("m-d-Y"), $lastDayofMonthdatetime->format("m-d-Y")));

// Get all attendances in one array and store them as DateTime objects in another array
$attendanceDates = $pdostatement->fetchAll( PDO::FETCH_COLUMN, 1);
$numAttendances = count($attendanceDates );
$attendanceDatetimes = array();
$daysWithAttendance = array_unique($attendanceDates);
$membersOnDates = array_count_values($attendanceDates);

// This isn't used yet, ok to delete?
for ($i = 0; $i < $numAttendances; $i++){
	$attendanceDatetimes[$i] = new DateTime($attendanceDates[$i]);
}

$attendance_numRows = 0;
$rowCountAttendance = 0;
$urlencodedName = htmlspecialchars($objname);
$tmpHTML="";
$tmpHTML = $tmpHTML . "<table summary=\"calendar\" id=\"calendar\" cellspacing=\"0\">" . "\n";
$tmpHTML = $tmpHTML . "<tr id=\"title\">" . "\n";
$tmpHTML = $tmpHTML . "<th align=\"left\">" . "\n";
$tmpHTML = $tmpHTML . "<a title=\"previous year\" href=\"?EventDate=" . htmlspecialchars($prevyear->format("d-m-Y")) . buildRequiredParamsConcat($session) . "&id=" . $_REQUEST["id"] . "&whomode=" . $whomode . "\"><img src=\"img/a_previous.gif\"></a></th>" . "\n";
$tmpHTML = $tmpHTML . "<th colspan=\"5\">" . $CurYearNum . "</th>" . "\n";
$tmpHTML = $tmpHTML . "<th align=\"right\"><a title=\"next year\" href=\"?EventDate=" . htmlspecialchars($nextyear->format("d-m-Y")) . buildRequiredParamsConcat($session). "&id=" . $_REQUEST["id"] . "&whomode=" . $whomode . "\"><img src=\"img/a_next.gif\"></a>";
$tmpHTML = $tmpHTML . "</th>" . "\n" . "</tr>" . "\n" ;
$tmpHTML = $tmpHTML . "<tr id=\"title\">" . "\n";
$tmpHTML = $tmpHTML . "<th align=\"left\">" . "\n";
$tmpHTML = $tmpHTML . "<a title=\"previous month\" href=\"?EventDate=" . htmlspecialchars($prevmonth->format("d-m-Y")) . buildRequiredParamsConcat($session). "&id=" . $_REQUEST["id"] . "&whomode=" . $whomode . "\"><img src=\"img/a_previous.gif\"></a></th>" . "\n";
$tmpHTML = $tmpHTML . "<th colspan=\"5\">" . $CurMonthName . "</th>" . "\n";
$tmpHTML = $tmpHTML . "<th align=\"right\"><a title=\"next month\" href=\"?EventDate=" . htmlspecialchars($nextmonth->format("d-m-Y")) . buildRequiredParamsConcat($session). "&id=" . $_REQUEST["id"] . "&whomode=" . $whomode . "\"><img src=\"img/a_next.gif\"></a></th>" . "\n" . "</tr>" . "\n" ;
$tmpHTML = $tmpHTML . "<tr id=\"days\">";
echo($tmpHTML);

$daynames = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
for ($DayLoop = 0; $DayLoop <7; $DayLoop++){
	echo("<th>" . $daynames[$DayLoop] . "</th>" . "\n");
}

echo("</tr>" . "\n" . "<tr class=\"firstweek\">");
if ( $FirstDayIndex != 1 ) {
	echo("<td colspan=\"" . ($FirstDayIndex -1) . "\" class=\"blank\">&nbsp;</td>" . "\n");
}
$DayCounter = $FirstDayIndex;
$CorrectMonth = true;

// Outside of loop, check number of rows, which is the total number of attended events for all members
// Loop each day in the month
for ($dayLoop = 1; $dayLoop <= $numDaysInMonth; $dayLoop++) {
	
	if (( $dayLoop == $todaysDayOfMonth ) && ($CurMonthNum == $todaysMonthNum)) {
		echo("<td class=\"today\">") ;	
	} else {
		echo("<td class=\"day" . $DayCounter . "\">");
	} 
	
	if (in_array($CurDayNumdatetime->format("Y-m-d"), $daysWithAttendance)){
		if ($whomode == "team") {
			echo('<a href="attendance-on-date.php?whomode=' . $whomode . '&pagemode=' . $pagemode . '&id=' . $objid . '&date=' . $CurDayNumdatetime->format("d-m-Y") . buildRequiredParamsConcat($session) . '" target="_top" title="View attendance detail on this date">');
		} else {
			echo '<a  title="' . $objname . ' attended an event.">';
		}
		echo($dayLoop);
		if ($whomode == "team") {
			echo "</a>";
			echo("<br><div id=\"datedetail\">" . $membersOnDates[$CurDayNumdatetime->format("Y-m-d")] . "</div>");	
		} else { 
			echo '</a>'; 
		}
	} else {
		echo($dayLoop);
	}

	$CurDayNumdatetime->modify("+1 day");
	echo("</td>" . "\n");

	$DayCounter = $DayCounter + 1;
	if ( $DayCounter > 7 ) {
		$DayCounter = 1;
		echo("</tr>" . "\n");
		echo("<tr");
		if ( date("n", $dayLoop+8) != $CurMonthNum ) {
			echo(" class=\"lastweek\"");
		}
		echo(">" . "\n");
	}
}
if ( $DayCounter != 1 ) {
	echo("<td colspan=\"" . (8-$DayCounter) . "\" class=\"blank\">&nbsp;</td>");
} else {
	echo("<td colspan=\"7\" class=\"blank\">&nbsp;</td>");
}
echo("</tr>" . "\n" . "</table>" . "\n");
// Display user total
if ( $whomode == "user" ) {
?>
<p><?php echo $objname?>  attended events on <?php echo count($daysWithAttendance)?> day<?php 
	if ( count($daysWithAttendance) != 1 ) { 
		echo("s</p>") ;
	}
// Display team total
} else {
?>
<p><?php echo $objname?> had events on <?php echo count($daysWithAttendance)?> day<?php 
	if ( count($daysWithAttendance) != 1 ) { 
		echo("s."); 
	}
	
// Average is the number of attended events for every member divided by the number of days we had events.
// Avoid div by zero
	if ( count($daysWithAttendance) > 0 ) {
?>
<br>Average per event: <?php echo round($numAttendances / count($daysWithAttendance))?> member<?php 
		if ( count($daysWithAttendance) != 1 ) { 
			echo("s.</p>"); 
		}
	}
} ?> 
<p><a href="include-attendance-table.php<?php buildRequiredParams($session) ?>&whomode=<?php echo $whomode ?>&EventDate=<?php echo $temp->format("d-m-Y")?>&pagemode=standalone&id=<?php echo $objid?>" title="Click for details on attendance during this period">Click for details on attendance during this period</a>.
</p>
</div>
<?php 
if ($pagemode == "standalone" ) { 
	// Start footer section
	include('footer.php'); ?>
</body>
</html>
<?php 
} ?>