<?php  
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Manage Programs"; 
include('header.php');
?>
<script type="text/javascript"> 
dojo.require("dojo.parser");
dojo.require("dojo.dnd.Source");
</script> 
<?php
echo "<h3>" . getTitle($session, $title) . "</h3>";


$bError = false;
$teamid = NotFound;
$err = "";
// teamid depends on who is calling
if (isUser($session, Role_TeamAdmin)){
	$teamid = getTeamID($session);
} else {
	if (isset($_REQUEST["teamid"])){
		$teamid = $_REQUEST["teamid"];
	} else{
		$bError = true;
		$err = "t";
	}
}?>
<h4>Programs for <?php echo getTeamName($teamid, $dbconn);?></h4>
<div class="helpbox">
<div class="helpboxtitle"><h5><a class="linkopacity" href="javascript:togglevis('overview')">What are Programs?<img src="/1team/img/a_expand.gif" id="overview_img" border="0" alt="expand region"></a></h5></div>
<div class="hideit" id="overview">
<div class="helpboxtext">
<p>Programs are associated with payments. If you would like your <?php echo $teamterms["termmember"]?>s to be able to pay for different types of programs, create and manage them from this page. If you segment your customers based on the amount they pay for the services you provide, you should consider using programs. Programs are required to be set up if payments are tracked.</p>
<p>If you track attendance, each program can have an event associated with it. This event is the used to track attendance if you have automated attendance scanning in use for your <?php echo $teamterms["termmember"]?>s. For example, if a customer is enrolled in a Masters Swimming program, you may want to have their attendance recorded as a Masters swimming event by default.</p>
</div></div></div>
<?php 
	// Note! This left outer join is necessary to return all programs since events may be null. The side effect of this is that other team programs are returned. These 
	// are filtered out below in the loop
	$strSQL = "SELECT programs.*, events.name as eventname FROM programs LEFT OUTER JOIN events ON programs.eventid = events.id AND programs.teamid = ? ORDER BY listorder;";
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array($teamid));
	
	$programResults = $pdostatement->fetchAll();

	$rowCount = 0;
	$loopMax = count($programResults);?>
<table width="65%">
<thead>
<tr>
<th width="50%">Program Name</th>
<th width="40%">Event for program attendance logging</th>
<th width="10%">Actions</th>
</tr>
</thead>
</table>
<div dojoType="dojo.dnd.Source" id="programlist" jsId="programlist" class="container"> 
<script type="dojo/method" event="creator" args="item, hint"> 

	// this is custom creator, which changes the avatar representation
	node = dojo.doc.createElement("div"), s = String(item);
	
	node.id = dojo.dnd.getUniqueId();
	node.className = "dojoDndItem";
	node.innerHTML = s; // "Reordering program. Drop in desired order location.";
	return {node: node, data: item, type: ["text"]};
</script> 
<?php
	while ($rowCount < $loopMax) { 
		// Filter out results not associated with this team
		if ($programResults[$rowCount]["teamid"] != $teamid) {
			$rowCount ++;
			continue;
		}?>		
<div class="dojoDndItem"> 
<span id="program<?php echo $rowCount?>" style="display:none" class="programorderitem"><?php echo $programResults[$rowCount]["id"]?></span><table width="65%"><tr class="even">
<td width="50%"><?php echo $programResults[$rowCount]["name"]?></td>
<td width="40%"><?php echo $programResults[$rowCount]["eventname"]?></td>
<td width="10%">
<a href="delete-program.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $programResults[$rowCount]["id"]?>" title="Delete"><img src="img/delete.gif" alt="Delete" border="0"></a>&nbsp;
<a href="edit-program-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $programResults[$rowCount]["id"]?>" title="Edit"><img src="img/edit.gif" alt="Edit" border="0"></a>
</td>
</tr>
</table></div>
<?php 
			$rowCount ++;
	}	?>
</div> 
<?php
	if ($rowCount > 1){?>
<table width="65%">
<tr class="odd"><td colspan="4"><span class="bigstrong">Reorder Programs</span></td></tr>
<tr class="even"><td colspan="3" width="70%"><form name="reorderprogram" action="/1team/reorder-program.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid?>"/>
<input type="hidden" name="programorder" value="" />
To reorder the program list, click and drag them, then press the "Reorder programs" button.</td>
<td width="10%" align="center"><input type="submit" value="Reorder programs" onclick="getProgramOrder()" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/></td>
</tr>
</table>
</form>
<?php
	}?>
<table width="65%">
<tr class="odd"><td colspan="2"><span class="bigstrong">Add new Program</span></td></tr>
<tr class="even">
<td width="50%"><form name="newprogram" action="/1team/new-program.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="text" name="name" size="80" maxlength="80" value="New Program Name"></td>
<td width="40%"><?php
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
<option value="<?php echo eventidUndefined?>">Select an event to associate with new program...</option>
<?php
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
</td>
<td width="10%"><a href="#" title="Add Program" onClick="newprogram.submit()"><img src="img/add.gif" alt="Add program" border="0"></a></td>
</tr>
</table>
</form>
<script type="text/javascript">
function getProgramOrder(){
	dndSource = new dojo.dnd.Source('programlist');
	// The idea is to get all nodes from the DnD Source 
	// create an array of node program ids where the index of the array+1 is the order 
	// and the content of the array is the program id
	var childnodes = new Array(); 
	for (var i = 0; i < dndSource.getAllNodes().length; i++) {
		childnodes[i] = dndSource.getAllNodes()[i].childNodes[1].innerHTML;
	}
	document.reorderprogram.programorder.value = childnodes.toString();
}
</script>
<?php 
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "The program was not saved successfully.", "");
} else if (isset($_GET["done"])){
	showMessage("Success", "The program was saved successfully.");
} 
// Start footer section
include('footer.php'); ?>
</body>
</html>