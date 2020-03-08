<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Manage Custom Lists";
include('header.php');

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
<h4><?php echo $title?> for <?php echo getTeamName($teamid, $dbconn);?></h4>
<div class="helpbox">
<div class="helpboxtitle"><h5><a class="linkopacity" href="javascript:togglevis('overview')">What are Custom Lists?<img src="/1team/img/a_expand.gif" id="overview_img" border="0" alt="expand region"></a></h5></div>
<div class="hideit" id="overview">
<div class="helpboxtext">
<p>A custom list is a special type of information you create for your custom fields. For example, if you track shirt sizes for your members, you can create a custom list called "Shirt Size" and add list members to represent each shirt size. You can order these lists to change what order they are displayed in. It is helpful to understand custom field concepts to completely use custom lists.</p>
</div></div></div>
<?php
	$strSQL = "SELECT * FROM customlists WHERE teamid = ?;";
	$customlistResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));

	$rowCount = 0;
	$loopMax = count($customlistResults);?>
<table width="65%">
<thead>
<tr><th width="90%">List Name</th>
<th width="10%">Actions</th></tr>
</thead>
<?php
	while ($rowCount < $loopMax) { ?>
<tr class="<?php if ((bool)( $rowCount % 2 )) echo("even"); else echo("odd") ?>">
<td width="90%"><?php echo $customlistResults[$rowCount]["name"]?></td>
<td width="10%">
<a href="delete-custom-list.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $customlistResults[$rowCount]["id"]?>" title="Delete"><img src="img/delete.gif" alt="Delete" border="0"></a>&nbsp;
<a href="edit-custom-list-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $customlistResults[$rowCount]["id"]?>" title="Edit"><img src="img/edit.gif" alt="Edit" border="0"></a>
</td>
</tr>
<?php
			$rowCount ++;
	}	?>
</table>
</form>
<table width="65%">
<form name="newcustomlist" action="/1team/new-custom-list-form.php" method="post"/>
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid?>"/>
<tr class="odd"><td colspan="2"><span class="bigstrong">Add new custom list</span></td></tr>
<tr class="even">
<td width="90%"><input type="text" name="name" size="80" maxlength="80" placeholder="New customlist Name"></td>
<td width="10%"><a href="#" title="Add customlist" onClick="newcustomlist.submit()"><img src="img/add.gif" alt="Add customlist" border="0"></a></td>
</tr>
</table>
</form>
<?php
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["errlistitem"])){
	showError("Error", 'The custom list item cannot be deleted since it is used in the custom field <a href="manage-custom-fields.php?' . returnRequiredParams($session) . '&teamid=' . $teamid . '">' . $_GET["errname"] . '</a>.', "");
} else if (isset($_GET["errname"])){
	showError("Error", 'The custom list cannot be deleted since it is used in the custom field <a href="manage-custom-fields.php?' . returnRequiredParams($session) . '&teamid=' . $teamid . '">' . $_GET["errname"] . '</a>.', "");
} else if (isset($_GET["err"])){
	showError("Error", "The custom list was not saved successfully.", "");
} else if (isset($_GET["done"])){
	showMessage("Success", "The custom list was saved successfully.");
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
