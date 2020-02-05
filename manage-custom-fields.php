<?php  
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Manage Custom Fields"; 
include('header.php');
?>
<script type="text/javascript"> 
dojo.require("dojo.parser");
dojo.require("dojo.dnd.Source");
</script> 
<?php
echo "<h3>" . getTitle($session, $title) . "</h3>";

$dbh = getDBH($session);
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
<h4>Custom Fields for <?php echo getTeamName2($teamid, $dbh);?></h4>
<div class="helpbox">
<div class="helpboxtitle"><h5><a class="linkopacity" href="javascript:togglevis('overview')">What are Custom Fields?<img src="/1team/img/a_expand.gif" id="overview_img" border="0" alt="expand region"></a></h5></div>
<div class="hideit" id="overview">
<div class="helpboxtext">
<p>If you want to include special fields for your customers, you can define custom fields. You can choose to display these custom fields based on information specific to the customer. For example, you can store special information such as shirt size, and show different sizes depending on if the customer is in your adult or children's program. You can also set display conditions for the custom field. For example, you can choose to only display a "lifeguard evaluation complete" field for adult customers. These custom fields can be of any data type, including a custom list based on your needs. It is helpful to understand custom list concepts before you create custom fields.</p>
</div></div></div>
<?php
	$strSQL = "SELECT customfields.id as customfieldsid, customdatatypes.id as customdatatypesid, * FROM customfields, customdatatypes WHERE customfields.teamid = ? AND customfields.customdatatypeid = customdatatypes.id ORDER BY listorder;";
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array($teamid));
	
	$customfieldResults = $pdostatement->fetchAll();

	$rowCount = 0;
	$loopMax = count($customfieldResults);?>
<table width="65%">
<thead>
<tr>
<th width="50%">Custom Field Name</th>
<th width="30%">Data Type</th>
<th width="10%">Display Conditions?</th>
<th width="10%">Actions</th>
</tr>
</thead>
</table>
<div dojoType="dojo.dnd.Source" id="customfieldlist" jsId="customfieldlist" class="container"> 
<script type="dojo/method" event="creator" args="item, hint"> 

	// this is custom creator, which changes the avatar representation
	node = dojo.doc.createElement("div"), s = String(item);
	
	node.id = dojo.dnd.getUniqueId();
	node.className = "dojoDndItem";
	node.innerHTML = s; // "Reordering customfield. Drop in desired order location.";
	return {node: node, data: item, type: ["text"]};
</script> 
<?php
	while ($rowCount < $loopMax) { ?>
<div class="dojoDndItem"> 
<span id="customfield<?php echo $rowCount?>" style="display:none" class="customfieldorderitem"><?php echo $customfieldResults[$rowCount]["customfieldsid"]?></span>
<table width="65%"><tr class="even">
<td width="50%"><?php echo $customfieldResults[$rowCount]["name"]?></td>
<td width="30%"><?php echo $customfieldResults[$rowCount]["typename"];
		// If this is a list type, echo the name of the list
		if ($customfieldResults[$rowCount]["customdatatypeid"] == CustomDataType_List) {
			echo ' : <a href="/1team/edit-custom-list-form.php?' . returnRequiredParams($session) . '&id=' . $customfieldResults[$rowCount]["customlistid"]. '">' . getCustomListName($customfieldResults[$rowCount]["customlistid"], $dbh) . '</a>';
		}?></td>
<td width="10%"><?php echo BoolToStr($customfieldResults[$rowCount]["hasdisplaycondition"])?></td>
<td width="10%"><a href="delete-custom-field.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $customfieldResults[$rowCount]["customfieldsid"]?>" title="Delete"><img src="img/delete.gif" alt="Delete" border="0"></a>&nbsp;
<a href="edit-custom-field-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $customfieldResults[$rowCount]["customfieldsid"]?>" title="Edit"><img src="img/edit.gif" alt="Edit" border="0"></a></td>
</tr>
</table></div>
<?php 
			$rowCount ++;
	}	?>
</div> 
<?php
	if ($rowCount > 1){?>
<table width="65%">
<tr class="odd"><td colspan="4"><span class="bigstrong">Reorder Custom Fields</span></td></tr>
<tr class="even"><td colspan="3" width="90%">
<form name="reordercustomfield" action="/1team/reorder-custom-field.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid?>"/>
<input type="hidden" name="customfieldorder" value="" />
To reorder the customfield list, click and drag them, then press the "Reorder customfields" button.</td>
<td width="10%" align="center"><input type="submit" value="Reorder customfields" onclick="getCustomFieldOrder()" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/></td>
</tr>
</table>
</form>
<?php
	} ?>
<table width="65%">
<tr class="odd"><td colspan="4"><span class="bigstrong">Add new Custom Field</span></td></tr>
<tr class="even"><td width="50%">
<form name="newcustomfield" action="/1team/new-custom-field.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid?>">
<input type="text" name="name" size="80" maxlength="80" value="New Custom Field Name"></td>
<td width="30%"><select name="datatype">
<option value="<?php echo CustomDataType_Undefined?>" selected>Select a data type...</option>
<?php 
	$strSQL = "SELECT * FROM customdatatypes ORDER BY typename;";
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute();
	
	$customdatatypesResults = $pdostatement->fetchAll();

	$rowCount = 0;
	$loopMax = count($customdatatypesResults);
	
	while ($rowCount < $loopMax) { 
		echo  "<option "; 
		echo  'value="' . $customdatatypesResults[$rowCount]["id"] . '"';
		echo  ">";
		echo $customdatatypesResults[$rowCount]["typename"];
		echo  "</option>";
		$rowCount++;
	}
?>
</select>	
</td>
<td width="10%"><input type="checkbox" name="hasdisplaycondition" value="0"></td>
<td width="10%"><a href="#" title="Add Custom Field" onClick="newcustomfield.submit()"><img src="img/add.gif" alt="Add customfield" border="0"></a></td>
</tr>
</table>
</form>
<script type="text/javascript">
function getCustomFieldOrder(){
	dndSource = new dojo.dnd.Source('customfieldlist');
	// The idea is to get all nodes from the DnD Source 
	// create an array of node customfield ids where the index of the array+1 is the order 
	// and the content of the array is the customfield id
	var childnodes = new Array(); 
	for (var i = 0; i < dndSource.getAllNodes().length; i++) {
		childnodes[i] = dndSource.getAllNodes()[i].childNodes[1].innerHTML;
	}
	document.reordercustomfield.customfieldorder.value = childnodes.toString();
}
</script>
<?php 
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "The custom field was not saved successfully.", "");
} else if (isset($_GET["done"])){
	showMessage("Success", "The custom field was saved successfully.");
} 
// Start footer section
include('footer.php'); ?>
</body>
</html>