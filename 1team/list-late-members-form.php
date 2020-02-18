<?php
// Only admins and coaches can execute this script. Header.php enforces this.
$isadminrequired = true;
ob_start(); 	// This caches non-header output, allowing us to redirect after header.php

$title = "Who Hasn't Purchased ";
include('header.php');


$rowCount = 0;
// Set up teamid from session or input
$bError = FALSE;

// teamid depends on who is calling
if ( !isUser($session, Role_ApplicationAdmin)){
	if ( !isset($session["teamid"])){
		$bError = true;
		$err = "A team must be selected.";
	} else {
		$teamid = $session["teamid"];
	}
} else {
	if ( isset($_REQUEST["teamid"])){
		$teamid = $_REQUEST["teamid"];
	} else {
		$bError = true;
		$err = "t";
	}
}
if (isset($_REQUEST["skuid"])) {
	if (!is_array($_REQUEST["skuid"])){
		$bError = true;
		$err = "s";
	} else {
		$skuidsposted = $_REQUEST["skuid"];
		// Build IN sql clause to get all skuids included in request
		$skusql = " IN (";
		$numskuidsposted = count($_REQUEST["skuid"]);
		$countskuids = 0;
		foreach ($skuidsposted as $skuidposted){
			$skusql .= $skuidposted;
			$countskuids++;
			if ($numskuidsposted > $countskuids) $skusql .= ",";
		}
		$skusql .=")";
	}
} else {
	$skuidsposted = array(Sku::SkuID_Undefined);
	$skuname = "...";
}

if (!$bError) {?>
<h3><?php echo $title ?>&hellip;</h3>
<?php
	// set up sort order
	$sortRequest = "firstname";
	if (isset($_REQUEST["sort"])) {
		$sortRequest = trim($_REQUEST["sort"]) . "";
		$sortRequest = cleanSQL($sortRequest);
	}

	// set up time interval for filter
	$timeintervalsql = "1 year";
	$intervalsql = " AND paymentdate >= cast(current_date - cast('" . $timeintervalsql . "' as interval) as date)";
	$timeinterval_request = "1year";

	if (isset($_REQUEST["timeinterval"])) {
		$timeinterval_request = $_REQUEST["timeinterval"];
		switch ($timeinterval_request){
			case "1day":
				$timeintervalsql = "1 day";
				$intervalsql = " AND paymentdate >= cast(current_date - cast('" . $timeintervalsql . "' as interval) as date)";
				break;
			case "1week":
				$timeintervalsql = "7 days";
				$intervalsql = " AND paymentdate >= cast(current_date - cast('" . $timeintervalsql . "' as interval) as date)";
				break;
			case "1mon":
				$timeintervalsql = "1 mon";
				$intervalsql = " AND paymentdate >= cast(current_date - cast('" . $timeintervalsql . "' as interval) as date)";
				break;
			case "3mons":
				$timeintervalsql = "3 mons";
				$intervalsql = " AND paymentdate >= cast(current_date - cast('" . $timeintervalsql . "' as interval) as date)";
				break;
			case "6mons":
				$timeintervalsql = "6 mons";
				$intervalsql = " AND paymentdate >= cast(current_date - cast('" . $timeintervalsql . "' as interval) as date)";
				break;
			case "1year":
				$timeintervalsql = "1 year";
				$intervalsql = " AND paymentdate >= cast(current_date - cast('" . $timeintervalsql . "' as interval) as date)";
				break;
			case "never":
				$timeintervalsql = "";
				$intervalsql = "";
				break;
			default:
				$timeinterval_request = "1year";
				$timeintervalsql = "1 year";
				$intervalsql = " AND paymentdate >= cast(current_date - cast('" . $timeintervalsql . "' as interval) as date)";
				break;
		}
	}

	// $isBillable triggers special features on this page for next payment due
	$isBillable = 0;
	if (isset($_REQUEST["isbillable"]) && is_numeric($_REQUEST["isbillable"])) {
		$isBillable = $_REQUEST["isbillable"];
	}

	// GEt skus
	$strSQL = "SELECT * FROM skus WHERE teamid = ? ORDER BY listorder";
  $dbconn = getConnectionFromSession($session);
  $skuResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));
	$rowCountS = count( $skuResults);

	// Display skus for this team
	if ($rowCountS > 0) {
		$countRowsS = 0; ?>
<script type="text/javascript">
function doSkusSelected( ){
	document.forms['selectskuform'].submit();

}
</script>
<div class="helpbox">
<div class="helpboxtitle"><h5><a class="linkopacity" href="javascript:togglevis('overview');">Help with <?php echo $title?><img src="/1team/img/a_expand.gif" id="overview_img" border="0" alt="expand region"></a></h5></div>
<div class="hideit" id="overview">
<div class="helpboxtext">
<p>Get a list of your <?php echo $teamterms["termmember"]?>s who haven't purchased your products or services. Selecting one or more SKUs below and press the Filter button.</p>
</div></div></div>
<form name="selectskuform" action="/1team/list-late-members-form.php" method="post">
<input type="hidden" name="sort" value="<?php echo $sortRequest ?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<input type="hidden" name="year" value="<?php echo $paymentyear?>"/>
<?php buildRequiredPostFields($session) ?>
<table class="noborders">
<tr><td class="bigstrong" valign="bottom">Filter by a timeframe<br/>
<select name="timeinterval">
	<option value="1day" <?php if ($timeinterval_request == "1day") echo " selected"?>>Past day</option>
	<option value="7days"<?php if ($timeinterval_request == "7days") echo " selected"?>>Past week</option>
	<option value="1mon"<?php if ($timeinterval_request == "1mon") echo " selected"?>>Past month</option>
	<option value="3mons"<?php if ($timeinterval_request == "3mons") echo " selected"?>>Past 3 months</option>
	<option value="6mons"<?php if ($timeinterval_request == "6mons") echo " selected"?>>Past 6 months</option>
	<option value="1year"<?php if ($timeinterval_request == "1year") echo " selected"?>>Past year</option>
	<option value="2years"<?php if ($timeinterval_request == "2years") echo " selected"?>>Past 2 years</option>
	<option value="never"<?php if ($timeinterval_request == "never") echo " selected"?>>Never</option>
</select></td></tr>
<tr><td class="bigstrong" valign="bottom">Select one or more SKUs<br/>
<select name="skuid[]" dojoType="dijit.form.MultiSelect" size="<?php if ($rowCountS <= 10) echo $rowCountS; else echo '10';?>" multiple="true">
<?php
		$skuname = "<ul>";
		while ($countRowsS < $rowCountS) {
			echo "<option value=\"";
			echo $skuResults[$countRowsS]["id"];
			echo "\"";
			if (in_array($skuResults[$countRowsS]["id"], $skuidsposted)) {
				echo " selected";
				$skuname .= "<li>".$skuResults[$countRowsS]["name"] . "</li>";
			}
			echo ">";
			echo $skuResults[$countRowsS]["name"];
			echo "</option>\n";
			$countRowsS ++;
		}
		$skuname .= "</ul>"; ?>
</select></td><td valign="bottom">
<input type="button" value="Filter" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onClick="doSkusSelected();">
</td></tr></table>
<?php
		if ((is_array($skuidsposted)) && ($skuidsposted[0] != Sku::SkuID_Undefined)){
			$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.roleid, users.imageid, useraccountinfo.status, useraccountinfo.isbillable, images.* FROM useraccountinfo, teams RIGHT OUTER JOIN images RIGHT OUTER JOIN users ON users.imageid = images.id ON images.teamid = teams.id  WHERE users.id in (SELECT users.id as userid FROM useraccountinfo, teams WHERE users.useraccountinfo = useraccountinfo.id AND users.teamid = ? AND useraccountinfo.isbillable = TRUE and useraccountinfo.status = 1 EXCEPT (SELECT orderitems.userid as userid FROM programs INNER JOIN (paymentmethods INNER JOIN (users RIGHT OUTER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid) on orderitems.paymentmethod = paymentmethods.id) ON programs.id = orderitems.programid WHERE orderitems.teamid = ? AND orderitems.skuid ". $skusql. $intervalsql." )) AND users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
			$results = executeQuery($dbconn, $strSQL, $bError, array($teamid, $teamid));
			// If none found
			if (count($results) == 0) {
				echo "<p>No members found.<br>\n";
				echo '<a href="/1team/new-user-form.php?' . returnRequiredParams($session) . '">Create a team member</a></p>';
				// If only one result, go to props page for that user
			} else { ?>
<div id="bodycontent">
<?php
				// More than one found, include roster
				$pagemode = pagemodeRoster;
				$referrer = "member-roster.php";
				include('include-member-roster.php');?>
<p><span class="strong">This filtered view includes <?php echo $teamterms["termmember"]?>s who have no purchased the following SKUs over the past <?php echo $timeintervalsql?>:</span><br/>
<?php			echo $skuname;
			}
		}
// Error
	} else {
		echo 'No SKUs have been defined. <a href="/1team/manage-skus-form.php?' . returnRequiredParams($session) . '">Define SKUs</a> to filter payments.';
	}
}
if ($bError) {
	redirectToReferrer( "&err=" . $err);
}
ob_end_flush();

// Start footer section
include('footer.php'); ?>
</body>
</html>
