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
$sqlBase = "SELECT COUNT(*) AS ROW_COUNT FROM users where teamid = ?";

for ($enrollmentLoop = 0; $enrollmentLoop < $numMonths; $enrollmentLoop++){
	$thismonth = $nextmonth->format("m-d-Y");
	$nextmonth->modify("+1 month");
	// Finish the query by adding a where clause covering one month beyond the last query
	$strSQL = $sqlBase . " and (startdate >= '" . $thismonth . "' and startdate < '" . $nextmonth->format("m-d-Y") . "');";
	$pdostatementStart = $dbh->prepare($strSQL);
	if ($pdostatementStart->execute(array($teamid))){
	     $enrollmentArrayStarted[$enrollmentLoop] = $pdostatementStart->fetchColumn();
	} else {
	     $enrollmentArrayStarted[$enrollmentLoop] = 0;
	     $bError = true;
	}

	// Finish the query by adding a where clause covering one month beyond the last query
	$strSQL = $sqlBase . " and (stopdate >= '" . $thismonth . "' and stopdate < '" . $nextmonth->format("m-d-Y") . "');";
	$pdostatementStop = $dbh->prepare($strSQL);
	if ($pdostatementStop->execute(array($teamid))){
	     $enrollmentArrayStopped[$enrollmentLoop] = $pdostatementStop->fetchColumn();
	} else {
	     $enrollmentArrayStopped[$enrollmentLoop] = 0;
	     $bError = true;
	}
}

$enrollmentCount = 0;
$monthseries = new DateTime($startdatetime->format("d-m-Y"));
$datastring = "<chart>" . "<series>";
for ($enrollmentLoop = 0 ;  $enrollmentLoop <= $numMonths-1; $enrollmentLoop++){
	$monthseries->modify("+1 month");
	$datastring = $datastring . "<value xid='" . $enrollmentLoop . "'>" . $monthseries->format("m/d/Y") . "</value>" ;
}
$datastring = $datastring . "</series>";

// This graph data includes the number of members in enrollment for each month the team has been in 1TeamWeb
$datastring = $datastring . "<graphs>";
$datastring = $datastring . "<graph gid='2' title='Number of " . $teamterms["termmember"] . "s'>";
for ($enrollmentLoop = 0;  $enrollmentLoop <= $numMonths-1; $enrollmentLoop++){
	$enrollmentCount = $enrollmentCount + $enrollmentArrayStarted[$enrollmentLoop] - $enrollmentArrayStopped[$enrollmentLoop];
	$datastring = $datastring . "<value xid='" . $enrollmentLoop . "'>" . $enrollmentCount . "</value>";
}
$datastring = $datastring . "</graph>";
$datastring = $datastring . "</graphs>";
$datastring = $datastring . "</chart>";
?>
<!-- amline script-->
<script type="text/javascript" src="amline/swfobject.js"></script>
<div id="altcontent">
<strong>You need to upgrade your Flash Player</strong>
</div>
<script type="text/javascript">
// <![CDATA[
var flashvars = {
  path: "amline/",
  settings_file: escape("amline/amline_settings.xml"),
  chart_data: "<?php echo $datastring?>",
  loading_settings: "Preparing attendance report",
  loading_data: "Preparing attendance report",
  preloader_color: "#999999"
};
swfobject.embedSWF("amline/amline.swf", "altcontent", "520", "400", "8.0.0", "amline/expressInstall.swf", flashvars);
// ]]>
</script>
<!-- end of amline script -->
<?php
// Start footer section
include('footer.php'); ?>
</body>
</html>
