<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Edit Level";
include('header.php');
?>
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

if (isset($_GET["id"])) {
	$levelid = $_GET["id"];
} else {
	$levelid = NotFound;
}

$strSQL = "SELECT * FROM levels WHERE id = ?;";
$dbconn = getConnection();
$levelResults = executeQuery($dbconn, $strSQL, $bError, array($levelid));

if (count($levelResults) > 0) { ?>
<h4>Modify a level for <?php echo getTeamName($teamid, $dbconn)?></h4>
<div class="indented-group-noborder">
<form action="/1team/edit-level.php" method="post">
<input type="hidden" name="id" value="<?php echo $levelid ?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<?php buildRequiredPostFields($session) ?>
<table class="noborders">
<tr>
<td class="strong">Level name</td><td><input type="text" name="name" size="60" maxlength="80" value="<?php echo $levelResults[0]["name"]?>"></td></tr>
<tr><td class="strong">Program</td><td><?php
	$strSQL = "SELECT * FROM programs WHERE teamid = ?;";
	$programResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));

	$rowCount = 0;
	$loopMax = count($programResults);?>
<select name="programid">
<?php
	while ($rowCount < $loopMax) {
		echo  "<option ";
		echo  'value="' . $programResults[$rowCount]["id"] . '"';
		if ($programResults[$rowCount]["id"] == $levelResults[0]["programid"]) {
			echo " selected ";
		}
		echo  ">";
		echo $programResults[$rowCount]["name"];
		echo  "</option>";
		$rowCount++;
	}
?>
</select></td></tr>
<tr>
<td class="strong">Level list order</td><td><?php if (is_null($levelResults[0]["listorder"]) ) echo "Not defined"; else echo $levelResults[0]["listorder"]?></td></tr>
</table>
</div>
<input type="submit" value="Modify level" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'manage-levels-form.php?<?php echo returnRequiredParams($session) . "&teamid=" . $teamid?>'"/>
</form>
<?php
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
