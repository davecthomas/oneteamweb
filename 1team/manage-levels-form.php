<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Manage Levels";
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
}
$dbconn = getConnectionFromSession($session);
?>
<h4>Levels for <?php echo getTeamName($teamid, $dbconn);?></h4>
<div class="helpbox">
<div class="helpboxtitle"><h5><a class="linkopacity" href="javascript:togglevis('overview');">What are Levels?<img src="/1team/img/a_expand.gif" id="overview_img" border="0" alt="expand region"></a></h5></div>
<div class="hideit" id="overview">
<div class="helpboxtext">
<p>Levels help you segment your customers based on their senority, skill level, promotions, etc. Since they may be related to your programs, you can define levels independently per program. For example, you may have a children's program with levels distinct from your adult's program.</p>
</div></div></div>
<?php
	$strSQL = "SELECT programs.name AS programs_name, levels.* FROM programs INNER JOIN levels on programs.id = levels.programid WHERE programs.teamid = ? ORDER BY levels.programid, listorder;";
	$levelResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));

	$rowCount = 0;
	$loopMax = count($levelResults);?>
<table width="65%">
<thead>
<tr>
<th width="50%">Level Name</th>
<th width="40%">Program</th>
<th width="10%">Actions</th>
</tr>
</thead>
</table>
<div dojoType="dojo.dnd.Source" id="levellist" jsId="levellist" class="container">
<script type="dojo/method" event="creator" args="item, hint">
	// this is custom creator, which changes the avatar representation
	node = dojo.doc.createElement("div");
	s = String(item);

	node.id = dojo.dnd.getUniqueId();
	node.className = "dojoDndItem";
	node.innerHTML = s; // "Reordering level. Drop in desired order location.";
	return {node: node, data: item, type: ["text"]};
</script>
<?php
	while ($rowCount < $loopMax) { ?>

<div class="dojoDndItem">
<span id="level<?php echo $rowCount?>" style="display:none" class="levelorderitem"><?php echo $levelResults[$rowCount]["id"]?></span><table width="65%"><tr class="even">
<td width="50%"><?php echo $levelResults[$rowCount]["name"]?></td>
<td width="40%"><?php echo $levelResults[$rowCount]["programs_name"]?></td>
<td width="10%">
<a href="delete-level.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $levelResults[$rowCount]["id"]?>" title="Delete"><img src="img/delete.gif" alt="Delete" border="0"></a>&nbsp;
<a href="edit-level-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $levelResults[$rowCount]["id"]?>" title="Edit"><img src="img/edit.gif" alt="Edit" border="0"></a>
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
<tr class="odd"><td colspan="4"><span class="bigstrong">Reorder Levels</span></td></tr>
<tr class="even"><td colspan="3" width="70%"><form name="reorderlevel" action="/1team/reorder-level.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid?>"/>
<input type="hidden" name="levelorder" value="" />
To reorder the level list, click and drag them, then press the "Reorder levels" button.</td>
<td width="10%" align="center"><input type="submit" value="Reorder levels" onclick="getLevelOrder()" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/></td>
</tr>
</table>
</form>
<?php
	} ?>
<table width="65%">
<tr class="odd"><td colspan="4"><span class="bigstrong">Add new level for <?php echo getTeamName($teamid, $dbconn)?>&nbsp;<?php echo $teamterms["termmember"]?>s</span></td></tr>
<tr class="even">
<td width="50%"><form name="newlevel" action="/1team/new-level.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="text" name="name" size="40" maxlength="80" placeholder="New Level Name"></td>
<td width="40">
<?php
	$strSQL = "SELECT * FROM programs WHERE teamid = ?;";
	$programResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));

	$rowCount = 0;
	$loopMax = count($programResults);?>
<select name="programid">
<option value="0" selected>Select a program to associate new level with...</option>
<?php
	while ($rowCount < $loopMax) {
		echo  "<option ";
		echo  'value="' . $programResults[$rowCount]["id"] . '"';
		echo  ">";
		echo $programResults[$rowCount]["name"];
		echo  "</option>";
		$rowCount++;
	}
?>
</select></td>
<td width="10%"><a href="#" title="Add Level" onClick="newlevel.submit()"><img src="img/add.gif" alt="Add level" border="0"></a></td>
</tr>
</table>
</form>
<script type="text/javascript">
function getLevelOrder(){
	dndSource = new dojo.dnd.Source('levellist');
	// The idea is to get all nodes from the DnD Source
	// create an array of node level ids where the index of the array+1 is the order
	// and the content of the array is the level id
	var childnodes = new Array();
	for (var i = 0; i < dndSource.getAllNodes().length; i++) {
		childnodes[i] = dndSource.getAllNodes()[i].childNodes[1].innerHTML;
	}
	document.reorderlevel.levelorder.value = childnodes.toString();
}
</script>
<?php
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "The level was not saved successfully.", "");
} else if (isset($_GET["done"])){
	showMessage("Success", "The level was saved successfully.");
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
