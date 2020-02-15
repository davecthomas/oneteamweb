<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "New Custom List";
include('header.php');?>
<script type="text/javascript">
dojo.require("dojo.parser");
dojo.require("dojo.dnd.Source");
</script>
<?php
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

// If we see a post variable, we assume we are to create a new list
if (isset($_POST["name"])) {
	$listname = $_POST["name"];
	$bCreate = true;
} else {
	$bCreate = false;
}

$dbconn = getConnectionFromSession($session);

if ($bCreate) {

	// First, insert the new list
	$strSQL = "INSERT INTO customlists VALUES (DEFAULT, ?, ?);";
	executeQuery($dbconn, $strSQL, $bError, array($listname, $teamid));
	// Now get the new list
	$strSQL = "SELECT * from customlists WHERE name = ? and teamid = ?;";
	$customlistResults = executeQuery($dbconn, $strSQL, $bError, array($listname, $teamid));

// Not a create case: require id
} else {

	if (isset($_GET["id"])) {
		$customlistid = $_GET["id"];
		$strSQL = "SELECT * from customlists WHERE id = ? and teamid = ?;";
		$customlistResults = executeQuery($dbconn, $strSQL, $bError, array($customlistid, $teamid));
	} else {
		$bError = true;
	}
}

if (!$bError) {
	// If we get here error free, we have a custom list query we can use
	$customlistid = $customlistResults[0]["id"];

	$rowCount = 0;
	$loopMax = count($customlistResults );

	if ($loopMax > 0) {
		$name = $customlistResults[0]["name"];
		?>
<h4><?php echo $title ?> &quot;<?php echo $name?>&quot; for <?php echo getTeamName($teamid, $dbconn);?></h4>
<?php
		$strSQL = "SELECT * FROM customlistdata WHERE customlistid = ? AND teamid = ? ORDER BY listorder;";
		$customlistdataResults = executeQuery($dbconn, $strSQL, $bError, array($customlistid, $teamid));

		$loopMaxListdata = count($customlistdataResults);

		if ($loopMaxListdata > 0){?>
<table width="65%">
<thead>
<tr>
<th align="left" width="90%">Custom List Members</th>
<th width="10%">Actions</th>
</tr>
</thead>
</table>
<div dojoType="dojo.dnd.Source" id="customlistlist" jsId="customlistlist" class="container">
<script type="dojo/method" event="creator" args="item, hint">

	// this is custom creator, which changes the avatar representation
	node = dojo.doc.createElement("div"), s = String(item);

	node.id = dojo.dnd.getUniqueId();
	node.className = "dojoDndItem";
	node.innerHTML = s; // "Reordering customlist. Drop in desired order location.";
	return {node: node, data: item, type: ["text"]};
</script>
<?php
			$rowCount = 0;
			while ($rowCount < $loopMaxListdata) { ?>
<div class="dojoDndItem">
<span id="customlist<?php echo $rowCount?>" style="display:none" class="customlistorderitem"><?php echo $customlistdataResults[$rowCount]["id"]?></span><table width="65%"><tr class="even">
<td width="90%"><?php echo $customlistdataResults[$rowCount]["listitemname"]?></td>
<td width="10%">
<a href="delete-customlist.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $customlistdataResults[$rowCount]["id"]?>" title="Delete"><img src="img/delete.gif" alt="Delete" border="0"></a>&nbsp;
<a href="edit-customlist-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $customlistdataResults[$rowCount]["id"]?>" title="Edit"><img src="img/edit.gif" alt="Edit" border="0"></a>
</td>
</tr>
</table></div>
<?php
				$rowCount ++;
			}	?>
</div>
<table width="65%">
<form name="reordercustomlist" action="/1team/reorder-custom-list.php" method="post"/>
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid?>"/>
<input type="hidden" name="customlistid" value="<?php echo $customlistid?>"/>
<input type="hidden" name="customlistorder" value="" />
<tr class="odd"><td colspan="4"><span class="bigstrong">Reorder Custom List</span></td></tr>
<tr class="even"><td colspan="3" width="90%">To reorder the customlist list, click and drag them, then press the "Reorder list" button.</td>
<td width="10%" align="center"><input type="submit" value="Reorder list" onclick="getCustomListOrder()" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/></td>
</tr>
</table>
</form>
<script type="text/javascript">
function getCustomListOrder(){
	dndSource = new dojo.dnd.Source('customlistlist');
	// The idea is to get all nodes from the DnD Source
	// create an array of node customlist ids where the index of the array+1 is the order
	// and the content of the array is the customlist id
	var childnodes = new Array();
	for (var i = 0; i < dndSource.getAllNodes().length; i++) {
		childnodes[i] = dndSource.getAllNodes()[i].childNodes[1].innerHTML;
	}
	document.reordercustomlist.customlistorder.value = childnodes.toString();
}
</script>
<?php
		}
	} ?>
<table width="65%">
<form name="newcustomlistmember" action="/1team/new-custom-list-member.php" method="post"/>
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid?>"/>
<input type="hidden" name="customlistid" value="<?php echo $customlistid?>"/>
<input type="hidden" name="listitemorder" value="1000"/>
<tr class="odd"><td colspan="2"><span class="bigstrong">Add new custom list member</span></td></tr>
<tr class="even">
<td width="90%"><input type="text" name="listitemname" size="80" maxlength="80" value=""></td>
<td width="10%"><a href="#" title="Add custom list member" onClick="newcustomlistmember.submit()"><img src="img/add.gif" alt="Add customlist" border="0"></a></td>
</tr>
</table>
</form>

<?php
// end !bError
} else {
	echo "error.";
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
