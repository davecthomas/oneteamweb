<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Edit Meeting Types";
include('header.php');
?>
<script type="text/javascript">
dojo.require("dijit.form.DateTextBox");
</script>

<?php

echo "<h3>" . getTitle($session, $title) . "</h3>";
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
	$eventid = $_GET["id"];
} else {
	$eventid = NotFound;
}

$strSQL = "SELECT * FROM events WHERE id = ?;";
$dbconn = getConnectionFromSession($session);
$eventResults = executeQuery($dbconn, $strSQL, $bError, array($eventid));

if (count($eventResults) > 0) { ?>
<h4>Modify an event for <?php echo getTeamName($teamid, $dbconn)?></h4>
<div class="indented-group-noborder">
<form action="/1team/edit-event.php" method="post"/>
<input type="hidden" name="id" value="<?php echo $eventid ?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<?php buildRequiredPostFields($session) ?>
<table class="noborders">
<tr><td class="strong">Meeting name</td><td><input type="text" name="name" size="60" maxlength="80" value="<?php echo $eventResults[0]["name"]?>"></td></tr>
<tr><td class="strong">Meeting date</td><td><input type="text" name="eventdate" id="eventdate" value="<?php echo $eventResults[0]["eventdate"]?>" dojoType="dijit.form.DateTextBox" /></tr>
<tr><td class="strong">Meeting location</td><td><input type="text" name="location" size="60" maxlength="80" value="<?php echo $eventResults[0]["location"]?>"></td></tr>
<tr><td class="strong">Is the meeting used in attendance scanning?</td><td><input type="checkbox" name="scannable" <?php if ($eventResults[0]["scannable"]) echo "checked='checked'";?> /></td></tr>
<tr><td class="strong">Program</td>
<td>
<?php
	$strSQL = "SELECT * FROM programs WHERE teamid = ?;";
	$programResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));

	$rowCount = 0;
	$loopMax = count($programResults);

	if (count($loopMax ) > 0) { ?>

<select name="programid" onchange="if (this.selectedIndex.value != <?php echo Program_Undefined?>) document.all.programnamelabel.innerText = this.options[this.selectedIndex].text">
<?php
		if ((empty($eventResults[0]["programid"])) || ($eventResults[0]["programid"] == Program_Undefined)) {?>
<option value="<?php echo Program_Undefined?>" selected>Select Program...</option>
<?php
		}
		while ($rowCount < $loopMax) {
			echo  "<option ";
			echo  'value="' . $programResults[$rowCount]["id"] . '"';
			if ($programResults[$rowCount]["id"] == $eventResults[0]["programid"]) {
				echo " selected ";
				$programname = $programResults[$rowCount]["name"];
			}
			echo  ">";
			echo $programResults[$rowCount]["name"];
			echo  "</option>";
			$rowCount++;
		}?>
</select>
<?php
	}  else {
		echo 'No programs are defined for ' . $teamname . '. <a href="/1team/manage-programs-form.php?teamid=' . $teamid . buildRequiredParamsConcat($session) . '">Define Programs</a>.';
	} ?>
</td></tr>
<tr><td class="strong">Meeting Type list order</td><td><?php if (is_null($eventResults[0]["listorder"]) ) echo "Not defined"; else echo $eventResults[0]["listorder"]?></td></tr>
</table>
</div><input type="submit" value="Modify meeting" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'manage-events-form.php?<?php echo returnRequiredParams($session) . "&teamid=" . $teamid?>'"/>
</form>
<?php
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
