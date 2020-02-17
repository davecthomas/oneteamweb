<?php
// The title is set here and rendered in header.php
$title= "Teams Roster " ;
include('header.php');
$bError = false;
echo "<h3>" . $title ."</h3>";
if (!isUser( $session, Role_ApplicationAdmin)){
	$bError = true;
}

if (!$bError){
	// set up sort order
	$sortRequest = "name";
	if (isset($_GET["sort"])) {
		$sortRequest = $_GET["sort"];
		$sortRequest = cleanSQL($sortRequest);
	}

	// Figure out how the roster should be filtered
	$filterRequest = "";
	if (isset($_GET["filter"])) {
		$filterRequest = $_GET["filter"];
		$filterRequest = cleanSQL($filterRequest);
		if ((strlen($filterRequest) > 0) && (! is_numeric($filterRequest)) ) {
			$filterRequestSQL = " AND " . $filterRequest;
		} else {
			$filterRequestSQL = "";
		}
	} else {
		$filterRequestSQL = "";
	}

	$strSQL = "SELECT teams.id as id_team, teams.*, teamaccountinfo.*, images.* FROM teamaccountinfo, teams LEFT OUTER JOIN images ON (images.teamid = teams.id AND images.type = " . ImageType_Team . ") WHERE teamaccountinfo.teamid = teams.id " . $filterRequestSQL . " ORDER BY " . $sortRequest . ";";
	$dbconn = getConnectionFromSession($session);
	$teams_records = executeQuery($dbconn, $strSQL, $bError); ?>
<table>
<thead class="head"><tr>
<th valign="top"><a href="teams-roster.php?sort=<?php echo(sortModifier("name",$sortRequest))?>">Name</a></th>
<th valign="top"><a href="teams-roster.php?sort=<?php echo(sortModifier("activityname",$sortRequest))?>">Activity</a></th>
<th valign="top"><a href="teams-roster.php?sort=<?php echo(sortModifier("startdate",$sortRequest))?>">Start Date</a></th>
<th valign="top"><a href="teams-roster.php?sort=<?php echo(sortModifier("status",$sortRequest))?>">Status</a></th>
<th valign="top">Max Members</th>
<th valign="top"><a href="teams-roster.php?sort=<?php echo(sortModifier("planduration",$sortRequest))?>">Plan Duration (months)</a></th>
<th valign="top">Current Members</th>
<th valign="top">Actions</th></tr>
</thead>
<tbody>
<?php
	$rowCount = 0;

	foreach ($teams_records as $row) {
		$rowCount ++;
		$accountStatus = $row["status"];
		$plan = $row["plan"];
		$planduration = $row["planduration"];
?>
<tr class="<?php if ((bool)( $rowCount % 2 )) echo("even"); else echo("odd") ?>">
<td><?php
if (is_url($row["url"])){
	$bhasurl = true;
?>
<span onmouseout="document.getElementById('dynamicimage').className = 'hideit'" onmouseover="setDynamicImage('<?php echo $row["url"]?>', this, <?php echo dynamicimagediv_Height/2?>)">
<?php
} else {
	$bhasurl = false;
} ?>
<a href="team-props-form.php<?php buildRequiredParams($session)?>&teamid=<?php echo $row["id_team"]?>"><?php echo $row["name"]?>
<?php
if ($bhasurl){ ?>
&nbsp;<img src="img/pix.gif" border="0" alt="picture available"></span>
<?php
} ?>
</a></td>
<td><?php echo $row["activityname"] ?></td>
<td><?php echo $row["startdate"]?></td>
<td><?php echo $aTeamStatus[$accountStatus+TeamAccountStatus_ArrayOffset]?></td>
<td><?php echo $plan . " " . $teamterms["termmember"]?>s</td>
<td><?php
		if ($planduration == TeamAccountPlanDuration_Unlimited) {
			echo("Unlimited");
		} else {
			echo($planduration);
		}
?>
</td>
<td><?php // Get the member count and flag it if they are at or over the max
		$memberCount = getMemberCount($session, $row["id_team"], $dbconn);
		if ($memberCount >= $row["plan"]) {
			echo('<span class="error">');
		} else {
			echo("<span>");
		}
		echo($memberCount);
?></span></td>
<td>
<a href="delete-team-form.php?<?php echo returnRequiredParams($session)?>&id=<?php echo $row["id_team"]?>" title="Delete <?php echo $row["name"]?>"><img src="img/delete.gif" border="0" alt="delete"></a>
</td>
</tr><?php
	} ?>
</tbody>
</table>
<p><?php echo($rowCount)?> teams listed. </p>
<div id="dynamicimage" class="hideit">
<img name="dynimg" src="" height="<?php echo dynamicimagediv_Height?>" alt="team">
</div>
<?php
}
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "There was an error: ".  $_GET["err"],"");
} else if (isset($_GET["done"])){
	showMessage("Success", "Success.");
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
