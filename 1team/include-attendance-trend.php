<?php
include_once("utils.php");

// Manage who mode: user or team
$whomode = "user";
if (isset($_GET["whomode"])) {
	$whomode = getCleanInput($_GET["whomode"]);
}

// Manage page mode: embedded or standalone
$pagemode = "embedded";
if (isset($_GET["pagemode"])) {
	$pagemode = getCleanInput($_GET["pagemode"]);
} else {
	$pagemode = "standalone";
}

// Standalone: init the session
if ($pagemode == "standalone") {
	$title= " Attendance Trendline " ;
	include('header.php');
	$expandimg = "collapse";
	$expandclass = "showit";
	// id is required and you must be able to admin this id
	if (isUser($session, Role_TeamAdmin)) {
		$teamid = $session["teamid"];
	}

	if (isset($_GET["id"])){
		if ($whomode == "user")	{
			$userid = $_GET["id"];
			if (!canIAdministerThisUser( $session, $userid)) redirectToLogin();
		} else {
			$teamid = $_GET["id"];
		}
	} else {
		redirectToLogin();
	}
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

$dbconn = getConnectionFromSession($session);

// User mode: make sure they can adminster this user
if ($whomode == "user") {
	$objid = $userid;
	$objname = getUserName($userid, $dbconn);
} else {
	$objid = $teamid;
	$objname = getTeamName($teamid, $dbconn);
}
$urlencodedName = urlencode($objname);

// Get Event Date
if (isset($_GET["EventDate"])) {
	$eventdatetime = new DateTime(getCleanInput($_GET["EventDate"]));
} else {
	// Base event date on start date of team or user (depends on whomode)
	if ($whomode == "user")	$strSQL = "SELECT users.startdate FROM users where users.id = ?";
	else $strSQL = "SELECT teams.name, teams.startdate FROM teams where teams.id = ?";
	$startdatearray = executeQuery($dbconn, $strSQL, $bError, array($objid));
	$eventdatetime = new DateTime($startdatearray[0]["startdate"]);
}

// Get the date of the first day of the month.
$FirstDayofMontharray = explode("-",$eventdatetime->format("m-d-Y"));
$FirstDayofMonthdatetime = new DateTime("01-" . $FirstDayofMontharray[0] . "-" . $FirstDayofMontharray[2]);
$CurMonthdatetime = $FirstDayofMonthdatetime;
// Make a copy to use later
$saveFirstDayofMonthdatetime = new DateTime("01-" . $FirstDayofMontharray[0] . "-" . $FirstDayofMontharray[2]);

$graphTitle = $objname . " attendance since " . $eventdatetime->format("m-d-Y");

// The type of graph is affected by the mode we are in
// these magic numbers refer to the xml settings file, and drive title, color, etc
if ($whomode == "user") {
	$graphGID= "3";
} else {
	$graphGID = "0";
}

// Count the number of months we need to track attendance. This will be from EventDate to today
$numMonths  = dateDiffNumMonths($eventdatetime->format("Y-m-d"), date("Y-m-d"), $dbconn);
if ($numMonths == 0) $numMonths = 1;	// Tweak on first months (zero months counts as 1)
$attendancespan_str = getYearsMonthsFromMonths($numMonths);

if (isset($_GET["EventDateEnd"])) {
	$lastdaydateReqdatetime = new DateTime($_GET["EventDateEnd"]);
	$lastDayofMonthdatetime = $lastdaydateReqdatetime;
} else {
	$lastDayofMonthdatetime = new DateTime($FirstDayofMonthdatetime->format("d-m-Y") . " +1 month");
}
// Make a copy to use later
$savelastDayofMonthdatetime = clone $lastDayofMonthdatetime;

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
}

if ($whomode == "user") { ?>
<h5>Attendance for <a target="_top" href="user-props-form.php<?php buildRequiredParams($session)?>&id=<?php echo $userid?>" target="_top"><?php echo $objname?></a> since <?php echo $eventdatetime->format("F")?>, <?php echo $eventdatetime->format("Y")?></h5>
<p>Each point on the graph represents the number of events attended in that month.</p>
<?php
} else { ?>
<h5>Attendance for <a target="_top" href="team-props-form.php<?php buildRequiredParams($session)?>&id=<?php echo $teamid?>" target="_top"><?php echo $objname?></a> since <?php echo $eventdatetime->format("F")?>, <?php echo $eventdatetime->format("Y")?></h5>
<p>This graph shows:
<ol>
<li>How many unique <?php echo $teamterms["termmember"]?>s attended events per month</li>
<li>The average number of <?php echo $teamterms["termmember"]?>s attending each <?php echo $teamterms["termclass"]?>.</li>
</ol>
</p>
<?php
}

$sqlBase = "SELECT COUNT(DISTINCT(attendance.attendancedate)) as classesinmonth,COUNT( attendance.memberid) as attendancecount, COUNT( DISTINCT(attendance.memberid)) as uniquemembers FROM attendance where teamid = ?";
if ( $whomode == "user" ) {
	$sqlBase = $sqlBase . " and memberid = ?";
}?>
<p>Attendance starting <?php echo $eventdatetime->format("m-d-Y")?> and running for <?php echo $attendancespan_str?>.</p>
<?php
// Arrays for storing results
$attendanceArrayAvgPerClass = array();
$attendanceArrayUniqueMember = array();
for ($attendanceLoop = 0; $attendanceLoop <= $numMonths; $attendanceLoop++){
	// Finish the query by adding a where clause covering one month beyond the last query
	$strSQL = $sqlBase . " and (attendance.attendancedate >= ? and attendance.attendancedate < ?)";
	if ( $whomode == "team" ) {
		$attendance_records = executeQuery($dbconn, $strSQL, $bError, 
																			array($teamid, $FirstDayofMonthdatetime->format("Y-m-d"), 
																			$lastDayofMonthdatetime->format("Y-m-d")));
	} else {
		if (!isset($teamid)) $teamid = $session["teamid"];
		$attendance_records = executeQuery($dbconn, $strSQL, $bError, 
													array($teamid, $objid, $FirstDayofMonthdatetime->format("Y-m-d"), 
													$lastDayofMonthdatetime->format("Y-m-d")));
	}
	// Get the team attendance
	foreach ($attendance_records as $row) {
		if ( $whomode == "team" ) {
			if ( $row["classesinmonth"] > 0 ) {
				$attendanceArrayAvgPerClass[$attendanceLoop] = round($row["attendancecount"]/$row["classesinmonth"]);
			} else {
				$attendanceArrayAvgPerClass[$attendanceLoop] = 0;
			}
			$attendanceArrayUniqueMember[$attendanceLoop] = $row["uniquemembers"];
		// Get the member attendance
		} else {
			$attendanceArrayAvgPerClass[$attendanceLoop] = $row["attendancecount"];
		}
	}

	$FirstDayofMonthdatetime->modify("+1 month");
	$lastDayofMonthdatetime->modify("+1 month");
}
$attendanceCount = 0;

$monthdate = $saveFirstDayofMonthdatetime;
$datastring = "[\n";
$monthseries = new DateTime($savelastDayofMonthdatetime->format("d-m-Y"));

for ($attendanceLoop = 0; $attendanceLoop<= $numMonths; $attendanceLoop++){
	$nextmonth = $monthseries->modify("+1 month");
	$nextmonth_js_timestamp = $nextmonth->getTimestamp() * 1000;
	$datastring = $datastring . "{date: " . "new Date( " . $nextmonth_js_timestamp.")" . ",\n";
	$datastring = $datastring . "attendance: " .$attendanceArrayAvgPerClass[$attendanceLoop] . "}";
	if ($attendanceLoop<$numMonths){
		$datastring = $datastring . ",\n";
	}
}
$datastring = $datastring . "];\n";

if ( $whomode == "team" ) {
	$datastring2 = "[\n";
	$monthseries = new DateTime($savelastDayofMonthdatetime->format("d-m-Y"));

	for ($attendanceLoop = 1; $attendanceLoop<= $numMonths; $attendanceLoop++){
		$nextmonth = $monthseries->modify("+1 month");
		$nextmonth_js_timestamp = $nextmonth->getTimestamp() * 1000;
		$datastring2 = $datastring2 . "// ".$nextmonth->format("Y-m-d")."\n";
		$datastring2 = $datastring2 . "{date: " . "new Date( " . $nextmonth_js_timestamp.")" . ",\n";
		$datastring2 = $datastring2 . "attendance: " . $attendanceArrayUniqueMember[$attendanceLoop] . "}" ;
		if ($attendanceLoop<$numMonths){
			$datastring2 = $datastring2 . ",\n";
		}
	}
	$datastring2 = $datastring2 . "];\n";
}

?>
<style>
#chartdiv {
  width: 100%;
  height: 500px;
}
#chart2div {
  width: 100%;
  height: 500px;
}
</style>

<!-- Resources -->
<script src="https://www.amcharts.com/lib/4/core.js"></script>
<script src="https://www.amcharts.com/lib/4/charts.js"></script>
<script src="https://www.amcharts.com/lib/4/themes/animated.js"></script>

<!-- Chart code -->
<script>
am4core.ready(function() {

// Themes begin
am4core.useTheme(am4themes_animated);
// Themes end

// Create Average Members per Class chart
var chart = am4core.create("chartdiv", am4charts.XYChart);
chart.paddingRight = 20;
chart.data = <?php echo $datastring?>;

var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
dateAxis.baseInterval = {
  "timeUnit": "month",
  "count": 1
};
dateAxis.tooltipDateFormat = "d MMMM YYYY";

var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
valueAxis.tooltip.disabled = true;
valueAxis.title.text = "Average Members per Class";
valueAxis.min = 0;

var series = chart.series.push(new am4charts.LineSeries());
series.dataFields.dateX = "date";
series.dataFields.valueY = "attendance";
series.tooltipText = "Attendance: [bold]{valueY}[/]";
series.fillOpacity = 0.3;


chart.cursor = new am4charts.XYCursor();
chart.cursor.lineY.opacity = 0;
chart.scrollbarX = new am4charts.XYChartScrollbar();
chart.scrollbarX.series.push(series);


dateAxis.start = 0;
dateAxis.keepSelection = true;

<?php
if ( $whomode == "team" ) {?>
// Create Unique Members per Class chart
var chart2 = am4core.create("chart2div", am4charts.XYChart);
chart2.paddingRight = 20;
chart2.data = <?php echo $datastring2?>;

var dateAxis2 = chart2.xAxes.push(new am4charts.DateAxis());
dateAxis2.baseInterval = {
  "timeUnit": "month",
  "count": 1
};
dateAxis2.tooltipDateFormat = "d MMMM YYYY";

var valueAxis2 = chart2.yAxes.push(new am4charts.ValueAxis());
valueAxis2.tooltip.disabled = true;
valueAxis2.title.text = "Unique Members per Class";
valueAxis2.min = 0;

var series2 = chart2.series.push(new am4charts.LineSeries());
series2.dataFields.dateX = "date";
series2.dataFields.valueY = "attendance";
series2.tooltipText = "Attendance: [bold]{valueY}[/]";
series2.fillOpacity = 0.3;


chart2.cursor = new am4charts.XYCursor();
chart2.cursor.lineY.opacity = 0;
chart2.scrollbarX = new am4charts.XYChartScrollbar();
chart2.scrollbarX.series.push(series);


dateAxis2.start = 0;
dateAxis2.keepSelection = true;
<?php
}?>

}); // end am4core.ready()
</script>

<!-- HTML -->
<div id="chartdiv"></div>
<?php
if ( $whomode == "team" ) {?>
<div id="chart2div"></div>
<?php
}?>
<?php
if ($pagemode == "standalone" ) {
	// Start footer section
	include('footer.php'); ?>
</body>
</html>
<?php
} ?>
