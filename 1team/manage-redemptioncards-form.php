<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Redemption Card Management";
include('header.php');
?>
<script type="text/javascript">
dojo.require("dijit.form.DateTextBox");
</script>

<?php
$bError = false;

echo "<h3>" . getTitle($session, $title) . "</h3>";

$teamid = NotFound;
$bError = false;
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
$objname = getTeamName($teamid, $dbconn);

// set up sort redemptioncard
$sortRequest = "firstname";
if (isset($_REQUEST["sort"])) {
	$sortRequest = trim($_REQUEST["sort"]) . "";
	$sortRequest = cleanSQL($sortRequest);
}
if (isset($_REQUEST["programid"])) {
	$programid = (int) $_REQUEST["programid"];
	if (!is_int($programid )){
		$programid = Program_Undefined;
	}
} else {
	// Default triggers all programs
	$programid = Program_Undefined;
}

if ((isset($_REQUEST["year"])) && (is_numeric($_REQUEST["year"]))) {
	$createdateyear = $_REQUEST["year"];
	$expand = "showit";
	$expandimg = "collapse";
} else {
	$createdateyear = date("Y");
	$expand = "hideit";
	$expandimg = "expand";
}
$disablenextyearlink = ($createdateyear+1 > (int)date("Y"));

if (!$bError){
	$teamname = getTeamName($teamid, $dbconn);

	// All new redemptioncarditems
	if ($programid == Program_Undefined){
		$strSQL = "select redemptioncards.userid as uid, redemptioncards.teamid as redemptioncardteamid, redemptioncards.id as redemptioncardid, redemptioncards.*,
				users.id as userid, users.firstname, users.lastname, skus.programid, skus.name as skuname
				FROM redemptioncards, users, skus
				WHERE redemptioncards.userid = users.id
				AND createdate >= '1/1/" . $createdateyear . "' AND createdate <= '12/31/" . $createdateyear . "'
				AND skuid = skus.id
				AND redemptioncards.teamid = ?
				GROUP BY redemptioncardid, userid, redemptioncards.teamid, skuid, createdate, amountpaid, facevalue, numeventsremaining,
				paymentmethod, redemptioncards.description, redemptioncards.expires, type, code, users.id, firstname, lastname, skus.programid, skus.name
				ORDER BY createdate DESC";
		$dbconn = getConnectionFromSession($session);
		$results = executeQuery($dbconn, $strSQL, $bError, array($teamid));

		// Now get guest cards, if any
		$strSQLG = "select redemptioncards.userid as uid, redemptioncards.teamid as redemptioncardteamid, redemptioncards.id as redemptioncardid, redemptioncards.*,
				skus.programid, skus.name as skuname
				FROM redemptioncards, skus
				WHERE createdate >= '1/1/" . $createdateyear . "' AND createdate <= '12/31/" . $createdateyear . "'
				AND skuid = skus.id
				AND userid = ?
				AND redemptioncards.teamid = ?
				GROUP BY redemptioncardid, userid, redemptioncards.teamid, skuid, createdate, amountpaid, facevalue, numeventsremaining,
				paymentmethod, redemptioncards.description, redemptioncards.expires, type, code, skus.programid, skus.name
				ORDER BY createdate DESC";
		$resultsG = executeQuery($dbconn, $strSQL, $bError, array(User::UserID_Guest, $teamid));
	} else {
		$strSQL = "select redemptioncards.userid as uid, redemptioncards.teamid as redemptioncardteamid, redemptioncards.id as redemptioncardid, redemptioncards.*,
				users.id as userid, users.firstname, users.lastname, skus.programid, skus.name as skuname
				FROM redemptioncards, users, skus
				WHERE redemptioncards.userid = users.id
				AND createdate >= '1/1/" . $createdateyear . "' AND createdate <= '12/31/" . $createdateyear . "'
				AND skuid = skus.id
				AND skus.programid = ?
				AND redemptioncards.teamid = ?
				GROUP BY redemptioncardid, userid, redemptioncards.teamid, skuid, createdate, amountpaid, facevalue, numeventsremaining,
				paymentmethod, redemptioncards.description, redemptioncards.expires, type, code, users.id, firstname, lastname, skus.programid, skus.name
				ORDER BY createdate DESC";
		$results = executeQuery($dbconn, $strSQL, $bError, array($programid, $teamid));

		// Now get guest cards, if any
		$strSQL = "select redemptioncards.userid as uid, redemptioncards.teamid as redemptioncardteamid, redemptioncards.id as redemptioncardid, redemptioncards.*,
				skus.programid, skus.name as skuname
				FROM redemptioncards, skus
				WHERE createdate >= '1/1/" . $createdateyear . "' AND createdate <= '12/31/" . $createdateyear . "'
				AND skuid = skus.id
				AND skus.programid = ?
				AND userid = ?
				AND redemptioncards.teamid = ?
				GROUP BY redemptioncardid, userid, redemptioncards.teamid, skuid, createdate, amountpaid, facevalue, numeventsremaining,
				paymentmethod, redemptioncards.description, redemptioncards.expires, type, code, skus.programid, skus.name
				ORDER BY createdate DESC";
		$resultsG = executeQuery($dbconn, $strSQL, $bError, array($programid, User::UserID_Guest, $teamid));

	}
	$resultsMerge = array_merge($results, $resultsG);

	$numRedemptionCards = count($resultsMerge );

	// Now get the sum of the face value since this should be very interesting for guest passes and other give-aways
	$strSQL = "select SUM(redemptioncards.facevalue) as sumfacevalue from redemptioncards where teamid = ?";
	$sumfacevalue = executeQueryFetchColumn($dbconn, $strSQL, $bError, array($teamid));
	// ?>

<h4>Filter Redemption Cards</h4>
<div class="indented-group-nobredemptioncard">
<script type="text/javascript">
function updateProgramID() {
	document.forms['selectprogramform'].submit();
}
</script>
<form name="selectprogramform" action="/1team/manage-redemptioncards-form.php" method="post">
<input type="hidden" name="sort" value="<?php echo $sortRequest ?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<input type="hidden" name="year" value="<?php echo $createdateyear?>"/>
<?php buildRequiredPostFields($session) ?>
<?php

			// GEt payment methods for this team
			$strSQL = "SELECT * FROM programs WHERE teamid = ?";
			$programResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));
			$rowCountP = count( $programResults);

			// Display programs for this team
			if ($rowCountP > 0) {
				$countRowsP = 0; ?>
<p class="bold">Display redemption cards for program&nbsp;<select name="programid" onchange="updateProgramID();">
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
</form>
<?php
			} else {
				echo '<p>There are no programs defined for the team ' . $teamname . '. <a href="/1team/manage-programs-form.php?' . returnRequiredParams($session) . '&teamid=' . $teamid .'">Define programs</a>.';
			}?>

<h4>Redemption Cards Previously Created for <?php echo $teamname?></h4>
<form>
<table class="memberlist">
<thead class="head">
<tr>
<th align="left"><a target="_top" href="manage-redemptioncards-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&year=<?php echo $createdateyear-1?>&sort=<?php echo $sortRequest?>&programid=<?php echo $programid?>"><img src="img/a_previous.gif" border="0" alt="previous">Previous year</a></th>
<th align="center" colspan="7"><span class="bigstrong"><?php echo $createdateyear ?></span></th>
<th align="right"><a target="_top" class="linkopacity" href="<?php
			if (!$disablenextyearlink){
				echo "manage-redemptioncards-form.php?". returnRequiredParams($session);
				echo "&teamid=". $teamid."&year=".($createdateyear+1);
				echo "&sort=". $sortRequest."&programid=".$programid;
			} else echo "#";?>">Next year<img src="img/a_next.gif" border="0" alt="next"></a></th>
</tr>
<tr>
<th valign="top">Creation date</th>
<th valign="top">Expires</th>
<th valign="top">Description</th>
<th valign="top"><?php echo $teamterms["termmember"]?></th>
<th valign="top">For SKU</th>
<th valign="top" ># Meetings</th>
<th valign="top">Face Value</th>
<th valign="top">Amount paid</th>
<th valign="top" >Actions</th>
</tr>
</thead>
<tbody>
<?php
	$rowCountRedemptionCards = 0;

	while ($rowCountRedemptionCards < $numRedemptionCards) {
		$description = $resultsMerge[$rowCountRedemptionCards]["description"];
		$createdate = $resultsMerge[$rowCountRedemptionCards]["createdate"];
		$expires = $resultsMerge[$rowCountRedemptionCards]["expires"];
		$uid = $resultsMerge[$rowCountRedemptionCards]["uid"];
		$facevalue = $resultsMerge[$rowCountRedemptionCards]["facevalue"];
		$amountpaid = $resultsMerge[$rowCountRedemptionCards]["amountpaid"];
		$skuid = $resultsMerge[$rowCountRedemptionCards]["skuid"];
		$skuname = $resultsMerge[$rowCountRedemptionCards]["skuname"];
		$numeventsremaining = $resultsMerge[$rowCountRedemptionCards]["numeventsremaining"];
?>
<tr class="<?php
		if ( ($rowCountRedemptionCards+1) % 2 ) echo("even");
		else echo("odd");?>">
<td><?php
	 	echo $createdate?></td>
<td><?php
	 	echo $expires?></td>
<td><a href="edit-redemptioncard-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $resultsMerge[$rowCountRedemptionCards]["id"]?>&teamid=<?php echo $teamid?>" title="Edit redemption card"><?php
	 	echo $description?></a></td>
<td><?php
		if ($resultsMerge[$rowCountRedemptionCards]["uid"] == User::UserID_Guest) {
			$username = User::Username_Guest;
			echo $username;
		} else {
			$username = $resultsMerge[$rowCountRedemptionCards]["firstname"] . ' ' . $resultsMerge[$rowCountRedemptionCards]["lastname"];?>
<a target="_top" href="user-props-form.php?<?php echo returnRequiredParams($session)?>&id=<?php echo $resultsMerge[$rowCountRedemptionCards]["uid"]?>"><?php echo $username?></a>
	<?php
		} ?>
</td>
<td><a href="edit-sku-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $resultsMerge[$rowCountRedemptionCards]["skuid"]?>"><?php echo $resultsMerge[$rowCountRedemptionCards]["skuname"]?></a></td>
<td><?php
		if ($numeventsremaining == Sku::NumEventsUnlimited) $numEventsTxt = 'Unlimited';
		else $numEventsTxt = $numeventsremaining;
		echo $numEventsTxt ?></td>
<td>$<?php echo $facevalue?></td>
<td>$<?php echo $amountpaid ?></td>
<td><a href="print-redemptioncard.php<?php buildRequiredParams($session) ?>&id=<?php echo $resultsMerge[$rowCountRedemptionCards]["id"]?>&teamid=<?php echo $teamid?>" title="Print redemption card"><img src="img/printer.png" alt="Print" border="0"></a>&nbsp;
<a href="edit-redemptioncard-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $resultsMerge[$rowCountRedemptionCards]["id"]?>&teamid=<?php echo $teamid?>" title="Edit redemption card"><img src="img/edit.gif" alt="Edit" border="0"></a>&nbsp;
<a href="#" onClick="confDelete('<?php echo $username?>', '<?php echo $createdate?>', '<?php echo $description?>',<?php echo $resultsMerge[$rowCountRedemptionCards]["id"]?>)" title="Delete redemption card"><img src="img/delete.png" alt="Delete redemption card..." border="0"></a></td>
</tr>
<?php
		$rowCountRedemptionCards ++;
	}
	//
	if ($numRedemptionCards == 0) { ?>
<tr><td colspan="9">No redemption cards in this time period.</td></tr>
<?php
	}
?>
</tbody>
</table>
<?php
	if ($numRedemptionCards > 0) { ?>
<p>Found <?php echo ($numRedemptionCards)?> redemption cards in this time period.</p>
<?php
	}
?>
</form>
<p><a href="edit-redemptioncard-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>" title="New redemption card..."><img src="img/add.gif" alt="Add item" border="0">Create a new redemption card...</a>&nbsp;<a href="delete-redemptioncard.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo RedemptionCardID_All?>" title="Delete expired and empty"><img src="img/delete.png" alt="Delete expired" border="0">Delete all expired and empty redemption cards</a></p>
<?php
}
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "There was an error processing this redemptioncard.", "");
} else if (isset($_GET["done"])){
	showMessage("Success", "The redemptioncard was processed successfully.");
}
// Start footer section
include('footer.php');
?>
<script type="text/javascript">
function confDelete(name, redemptioncarddate, description, id) {
	if (confirm('Are you sure you want to delete the redemption card by ' + name + ' on ' + redemptioncarddate + ' for ' + description + '?')) {
		document.location.href = 'delete-redemptioncard.php?<?php echo returnRequiredParams($session) . "&teamid=" . $teamid . "&id="?>' + id;
	}
}

function doEdit( epayid, uid, skuid ){
	document.location.href = 'edit-redemptioncard.php?<?php echo returnRequiredParams($session) . "&teamid=" . $teamid . "&id=" ?>' + epayid + '&uid=' + uid + '&skuid=' + skuid;
}
</script>
</body>
</html>
