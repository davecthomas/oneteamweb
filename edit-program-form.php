<?php  
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Edit Program"; 
include('header.php');
$dbh = getDBH($session);
$teamid = NotFound;
$bError = false;
// teamid depends on who is calling
if (isUser($session, Role_TeamAdmin)){
	$teamid = getTeamID($session);
} else {
	if (isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	} else{
		$bError = true;
	}
}

if (isset($_GET["id"])) {
	$programid = $_GET["id"];
} else {
	$programid = NotFound;
}

$strSQL = "SELECT * FROM programs WHERE id = ?;";
$pdostatement = $dbh->prepare($strSQL);
$pdostatement->execute(array($programid));

$programResults = $pdostatement->fetchAll();

if (count($programResults) > 0) { ?>
<h3>Modify a program for <?php echo getTeamName2($teamid, $dbh)?></h3>	
<div class="indented-group-noborder">
<form action="/1team/edit-program.php" method="post">
<input type="hidden" name="id" value="<?php echo $programid ?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<?php buildRequiredPostFields($session) ?>
<table class="noborders">
<tr><td class="strong">Program name</td><td><input type="text" name="name" size="60" maxlength="80" value="<?php echo $programResults[0]["name"]?>"></td></tr>
<tr><td class="strong">Associate attendance event with program</td><td><?php
	// Event selector
	if ( isUser($session, Role_ApplicationAdmin) ) {
		$strSQL = "SELECT * FROM events order by listorder;";
		$pdostatement = $dbh->prepare($strSQL);
		$pdostatement->execute();		
	} else {
		$strSQL = "SELECT * FROM events WHERE teamid = ? order by listorder;";
		$pdostatement = $dbh->prepare($strSQL);
		$pdostatement->execute(array($session["teamid"]));		
	} 
	$eventResults = $pdostatement->fetchAll();
	$numEvents = count($eventResults);
	$rowCount = 0;
	if ($numEvents > 0){ ?>
<select name="eventid">
<?php 
		if ((is_null($programResults[0]["eventid"])) || ($programResults[0]["eventid"] == eventidUndefined)) { ?>
<option value="<?php echo eventidUndefined?>">Associate an attendance event with this program...</option>
<?php
		} 
		while ($rowCount < $numEvents) { 
			$eventdate = $eventResults[$rowCount]["eventdate"];
			if (( strlen($eventdate) < 1 ) || ( is_null($eventdate) )) { 
				$eventdate = "any date";
			}
			$location = $eventResults[$rowCount]["location"];
			if (( strlen($location) < 1 ) || ( is_null($eventdate) )) {
				$location = "any location";
			}
			echo '<option value="';
			echo $eventResults[$rowCount]["id"];
			echo '"';
			if ($eventResults[$rowCount]["id"] == $programResults[0]["eventid"]) echo " selected";
			echo ">";
			echo $eventResults[$rowCount]["name"] . " on " . $eventdate . " at " . $location;
			echo "</option>";
			$rowCount++;
		} ?>
</select>
<?php 
	} else { 
		echo 'No events have been defined. <a href="/1team/manage-events-form.php?' . returnRequiredParams($session) . '">Define events</a> to associate with programs.';
	}?>
</td></tr>
<td class="strong">Program list order</td><td><?php if (is_null($programResults[0]["listorder"]) ) echo "No order set"; else echo $programResults[0]["listorder"]?></td></tr>
</table>
</div>
<input type="submit" value="Modify program" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'manage-programs-form.php?<?php echo returnRequiredParams($session) . "&teamid=" . $teamid?>'"/>
</form>
<?php 
} 
// Start footer section
include('footer.php'); ?>
</body>
</html>
