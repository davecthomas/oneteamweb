<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Manage SKUs";
include('header.php');
?>
<script type="text/javascript">
dojo.require("dijit.form.DateTextBox");
dojo.require("dojo.parser");
dojo.require("dojo.dnd.Source");
</script>
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
$dbconn = getConnectionFromSession($session);

?>
<h4>SKUs for <?php echo getTeamName($teamid, $dbconn);?></h4>
<div class="helpbox">
<div class="helpboxtitle"><h5><a class="linkopacity" href="javascript:togglevis('overview')">What are SKUs?<img src="/1team/img/a_expand.gif" id="overview_img" border="0" alt="expand region"></a></h5></div>
<div class="hideit" id="overview">
<div class="helpboxtext">
<p>SKUs (Stock Keeping Units) represent the individual products and services you price for sale, you can define SKUs independently per program. For example, you may sell various different services, each with unique identifying SKUs, to customers of your children's program distinct from your adult's program.</p>
</div></div></div>
<?php
	$strSQL = "SELECT programs.name AS programs_name, skus.* FROM programs INNER JOIN skus on programs.id = skus.programid WHERE programs.teamid = ? ORDER BY skus.programid, listorder;";
  $dbconn = getConnectionFromSession($session);
  $skuResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));

	$rowCount = 0;
	$loopMax = count($skuResults);?>
<table width="65%">
<thead>
<th width="50%">SKU Name</th>
<th width="10%">Price</th>
<th width="30%">Program</th>
<th width="10%">Actions</th>
</thead>
</table>
<div dojoType="dojo.dnd.Source" id="skulist" jsId="skulist" class="container">
<script type="dojo/method" event="creator" args="item, hint">
	// this is custom creator, which changes the avatar representation
	node = dojo.doc.createElement("div");
	s = String(item);

	node.id = dojo.dnd.getUniqueId();
	node.className = "dojoDndItem";
	node.innerHTML = s; // "Reordering SKU. Drop in desired order location.";
	return {node: node, data: item, type: ["text"]};
</script>
<?php
	while ($rowCount < $loopMax) { ?>

<div class="dojoDndItem">
<span id="sku<?php echo $rowCount?>" style="display:none" class="skuorderitem"><?php echo $skuResults[$rowCount]["id"]?></span><table width="65%"><tr class="even">
<td width="50%"><?php echo $skuResults[$rowCount]["name"]?></td>
<td width="10%">$<?php echo $skuResults[$rowCount]["price"]?></td>
<td width="30%"><?php echo $skuResults[$rowCount]["programs_name"]?></td>
<td width="10%">
<a href="delete-sku.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $skuResults[$rowCount]["id"]?>" title="Delete"><img src="img/delete.gif" alt="Delete" border="0"></a>&nbsp;
<a href="edit-sku-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $skuResults[$rowCount]["id"]?>" title="Edit"><img src="img/edit.gif" alt="Edit" border="0"></a>
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
<tr class="odd"><td colspan="4"><span class="bigstrong">Reorder SKUs</span></td></tr>
<tr class="even"><td colspan="3" width="70%">
<form name="reordersku" action="/1team/reorder-sku.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid?>"/>
<input type="hidden" name="skuorder" value="" />
To reorder the SKU list, click and drag them, then press the "Reorder SKUs" button.</td>
<td width="10%" align="center"><input type="submit" value="Reorder SKUs" onclick="getLevelOrder()" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/></td>
</tr>
</table>
</form>
<?php
	} ?>
<table width="65%">
<tr class="odd"><td colspan="4"><span class="bigstrong">Add new SKU for <?php echo getTeamName($teamid, $dbconn)?>&nbsp;<?php echo $teamterms["termmember"]?>s</span></td></tr>
<tr class="even">
<td width="50%">
<form name="newsku" action="/1team/new-sku.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="text" name="name" size="40" maxlength="80" value="New SKU Name"></td>
<td width="10%">$<input type="text" name="price" size="6" maxlength="10" value="0.00"></td>
<td width="30">
<?php
	$strSQL = "SELECT * FROM programs WHERE teamid = ?;";
	$programResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));

	$rowCount = 0;
	$loopMax = count($programResults);?>
<select name="programid">
<option value="0" selected>Program associated with SKU...</option>
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
<td width="10%"><a href="#" title="Add SKU" onClick="newsku.submit()"><img src="img/add.gif" alt="Add SKU" border="0"></a></td>
</tr>
</table>
</form>
<script type="text/javascript">
function getLevelOrder(){
	dndSource = new dojo.dnd.Source('skulist');
	// The idea is to get all nodes from the DnD Source
	// create an array of node SKU ids where the index of the array+1 is the order
	// and the content of the array is the SKU id
	var childnodes = new Array();
	for (var i = 0; i < dndSource.getAllNodes().length; i++) {
		childnodes[i] = dndSource.getAllNodes()[i].childNodes[1].innerHTML;
	}
	document.reordersku.skuorder.value = childnodes.toString();
}
</script>
<?php
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "The SKU was not saved successfully.", "");
} else if (isset($_GET["done"])){
	showMessage("Success", "The SKU was saved successfully.");
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
