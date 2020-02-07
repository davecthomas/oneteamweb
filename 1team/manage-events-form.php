<?php  
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Manage Meeting Types";
include('header.php');
?>
<script type="text/javascript"> 
dojo.require("dijit.form.DateTextBox");
dojo.require("dojo.parser");
dojo.require("dojo.dnd.Source");
</script> 
<?php
$dbh = getDBH($session); 
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
}
?>
<h4>Meeting Types for <?php echo getTeamName2($teamid, $dbh);?></h4>
<div class="helpbox">
<div class="helpboxtitle"><h5><a class="linkopacity" href="javascript:togglevis('overview')">What are Meeting Types?<img src="/1team/img/a_expand.gif" id="overview_img" border="0" alt="expand region"></a></h5></div>
<div class="hideit" id="overview">
<div class="helpboxtext">
<p>Meetings are gatherings of your team and are associated with attendance at those meetings. Each time your <?php echo $teamterms["termmember"]?>s participate in a team activity, you can associate that attendance with the event that represents this activity. For example, if you have an monthly members' meeting, you can create a meeting type called "Monthly Meeting" and track the attendance of your team at this meeting. <br/>
You can have meetings that are associated with a specific location or date or repeating meetings that are not related to locations or dates. If you have multiple locations where your team meets, you can distinguish the events based on location. If you would like your <?php echo $teamterms["termmember"]?>s to be able to attend different types of meetingss, create and manage them from this page.</p></div></div>
<div class="push"></div>
</div>
<?php
	$strSQL = "SELECT * FROM events WHERE teamid = ? ORDER BY listorder;";
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array($teamid));
	
	$eventResults = $pdostatement->fetchAll();

	$rowCount = 0;
	$loopMax = count($eventResults);?>
<table width="65%">
<thead>
<tr>
<th width="30%">Meeting Type Name</th>
<th width="20%">Date</th>
<th width="20%">Location</th>
<th width="10%">Actions</th>
</tr>
</thead>
</table>
<div dojoType="dojo.dnd.Source" id="eventlist" jsId="eventlist" class="container"> 
<script type="dojo/method" event="creator" args="item, hint"> 

	// this is custom creator, which changes the avatar representation
	node = dojo.doc.createElement("div"), s = String(item);
	
	node.id = dojo.dnd.getUniqueId();
	node.className = "dojoDndItem";
	node.innerHTML = s; // "Reordering event. Drop in desired order location.";
	return {node: node, data: item, type: ["text"]};
</script> 
<?php
	while ($rowCount < $loopMax) { ?>
				
<div class="dojoDndItem"> 
<span id="event<?php echo $rowCount?>" style="display:none" class="eventorderitem"><?php echo $eventResults[$rowCount]["id"]?></span><table width="65%"><tr class="even">
<td width="30%"><?php echo $eventResults[$rowCount]["name"]?></td>
<td width="20%"><?php echo $eventResults[$rowCount]["eventdate"]?></td>
<td width="20%"><?php echo $eventResults[$rowCount]["location"]?></td>
<td width="10%">
<a href="delete-event.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $eventResults[$rowCount]["id"]?>" title="Delete"><img src="img/delete.gif" alt="Delete" border="0"></a>&nbsp;
<a href="edit-event-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $eventResults[$rowCount]["id"]?>" title="Edit"><img src="img/edit.gif" alt="Edit" border="0"></a>
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
<tr class="odd"><td colspan="4"><span class="bigstrong">Reorder Meeting Types</span></td></tr>
<tr class="even"><td colspan="3" width="70%"><form name="reorderevent" action="/1team/reorder-event.php" method="post"/>
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid?>"/>
<input type="hidden" name="eventorder" value="" />
Ordering only changes the order of lists within <?php appname?>. To reorder the list, click and drag them, then press the "Reorder Meeting Types" button.</td>
<td width="10%" align="center"><input type="submit" value="Reorder Meeting Types" onclick="getEventOrder()" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/></td>
</tr>
</table>
</form>
<?php
	}?>
<table width="65%">
<tr class="odd"><td colspan="4"><span class="bigstrong">Add New Meeting Type</span></td></tr>
<tr class="even">
<td width="30%"><form name="newevent" action="/1team/new-event.php" method="post"/>
<?php buildRequiredPostFields($session) ?>
<input type="text" name="name" size="60" maxlength="80" value=""></td>
<td width="20%"><input type="text" name="eventdate" id="eventdate" value="<?php echo date("Y-m-d")?>" dojoType="dijit.form.DateTextBox" /></td>
<td width="20%"><input type="text" name="location" size="40" maxlength="80" value=""></td>
<td width="10%"><a href="#" title="Add Meeting Type" onClick="newevent.submit()"><img src="img/add.gif" alt="Add Meeting Type" border="0"></a></td>
</tr>
</table>
</form>
<script type="text/javascript">
function getEventOrder(){
	dndSource = new dojo.dnd.Source('eventlist');
	// The idea is to get all nodes from the DnD Source 
	// create an array of node event ids where the index of the array+1 is the order 
	// and the content of the array is the event id
	var childnodes = new Array(); 
	for (var i = 0; i < dndSource.getAllNodes().length; i++) {
		childnodes[i] = dndSource.getAllNodes()[i].childNodes[1].innerHTML;
	}
	document.reorderevent.eventorder.value = childnodes.toString();
}
</script>
<?php 
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "The event was not saved successfully.", "");
} else if (isset($_GET["done"])){
	showMessage("Success", "The event was saved successfully.");
} 
// Start footer section
include('footer.php'); ?>
</body>
</html>
