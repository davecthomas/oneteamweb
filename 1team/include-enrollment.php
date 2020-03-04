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

$teamname = getTeamName( $teamid);

$title.= " : " . $teamname;
// Check inputs
// $whoMode controls if this displays promotions for a single user or the whole team. Default is "team"
$whoMode = getCleanInput($_GET["mode"]);
$teamid = getCleanInput($_GET["teamid"]);
?>
<h3><?php echo $teamname?> Enrollment Trend</h3>

<?php
// Get start Date for team
if (isset($_GET["startdate"])) {
	$startdatetime = new DateTime($_GET["startdate"]);
} else {
     $startdatetime = new DateTime();
	$bError = true;
}
$CurMonthNum = $startdatetime->format("n");
$CurMonthName = $startdatetime->format("M");
$CurYearNum = $startdatetime->format("Y");

// Get the date of the first day of the start month.
$FirstDayofMontharray = explode("-",$startdatetime->format("m-d-Y"));
$FirstDayofMonthdatetime = new DateTime("01-" . $FirstDayofMontharray[0] . "-" . $FirstDayofMontharray[2]);
$CurDayNumdatetime = $FirstDayofMonthdatetime;

// Count the number of months we need to track enrollment. This will be from EventDate to today
$numMonths = datediff("m", $startdatetime->format("m/d/Y"), date("m/d/Y"));

// This type of cursor is required since I'm counting rows with the recordset.rowcount property
// Display previous and next links at the top of the table ?>
<p>Enrollment starting <?php echo $startdatetime->format("M d, Y")?> and running for <?php echo $numMonths?> months.</p>
<?php
$nextmonth = new DateTime($startdatetime->format("d-m-Y"));

// Only looking for a count with this query
$sqlBase = "SELECT * FROM users where teamid = ?";
$dbconn = getConnectionFromSession($session);
$enrollmentArrayStarted = array();
$enrollmentArrayStopped = array();
for ($enrollmentLoop = 0; $enrollmentLoop < $numMonths; $enrollmentLoop++){
	$thismonth = $nextmonth->format("Y-m-d");
	$nextmonth->modify("+1 month");
	// Finish the query by adding a where clause covering one month beyond the last query
	$strSQL = $sqlBase . " and (startdate >= '" . $thismonth . "' and startdate < '" . $nextmonth->format("Y-m-d") . "');";
  $results_rows = executeQuery($dbconn, $strSQL, $bError, array($teamid));
  if ((is_array($results_rows)) && (!$bError)){
	     $enrollmentArrayStarted[$enrollmentLoop] = count($results_rows);
	} else {
	     $enrollmentArrayStarted[$enrollmentLoop] = 0;
	}

	// Finish the query by adding a where clause covering one month beyond the last query
	$strSQL = $sqlBase . " and (stopdate >= '" . $thismonth . "' and stopdate < '" . $nextmonth->format("Y-m-d") . "');";
	$results_rows = executeQuery($dbconn, $strSQL, $bError, array($teamid));
	if ((is_array($results_rows)) && (!$bError)){
	     $enrollmentArrayStopped[$enrollmentLoop] = count($results_rows);
	} else {
	     $enrollmentArrayStopped[$enrollmentLoop] = 0;
	}
}
$enrollmentCount = 0;
$monthseries = new DateTime($startdatetime->format("d-m-Y"));
$datastring = "[\n";
// This graph data includes the number of members in enrollment for each month the team has been in 1TeamWeb
for ($enrollmentLoop = 0;  $enrollmentLoop <= $numMonths-1; $enrollmentLoop++){
	$enrollmentCount = $enrollmentCount + $enrollmentArrayStarted[$enrollmentLoop] - $enrollmentArrayStopped[$enrollmentLoop];
	
	$nextmonth = $monthseries->modify("+1 month");
	$nextmonth_js_timestamp = $nextmonth->getTimestamp() * 1000;
	$datastring = $datastring . "{date: " . "new Date( " . $nextmonth_js_timestamp.")" . ",\n";
	$datastring = $datastring . "enrollment: " .$enrollmentCount . "}\n";
	if ($enrollmentLoop<$numMonths-1){
		$datastring = $datastring . ",\n";
	}
}
$datastring = $datastring . "]\n";
?>
<style>
#chartdiv {
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

// Create chart
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
valueAxis.title.text = "Members";
valueAxis.min = 0;

var series = chart.series.push(new am4charts.LineSeries());
series.dataFields.dateX = "date";
series.dataFields.valueY = "enrollment";
series.tooltipText = "Enrollment: [bold]{valueY}[/]";
series.fillOpacity = 0.3;


chart.cursor = new am4charts.XYCursor();
chart.cursor.lineY.opacity = 0;
chart.scrollbarX = new am4charts.XYChartScrollbar();
chart.scrollbarX.series.push(series);


dateAxis.start = 0;
dateAxis.keepSelection = true;

}); // end am4core.ready()
</script>

<!-- HTML -->
<div id="chartdiv"></div>
<?php
// Start footer section
include('footer.php'); ?>
</body>
</html>
