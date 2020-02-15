<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Modify Custom List Item";
include('header.php');
$bError = false;
?>
<script type="text/javascript">
dojo.require("dijit.form.DateTextBox");
</script>
<?php

echo "<h3>" . getTitle($session, $title) . "</h3>";
$teamid = NotFound;

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
	$customlistitemid = $_GET["id"];
} else {
	$customlistitemid = NotFound;
}

if (isset($_GET["customlistid"])) {
	$customlistid = $_GET["customlistid"];
} else {
	$customlistid = CustomList_Undefined;
}

$strSQL = "SELECT * FROM customlistdata, customlists WHERE customlistdata.customlistid = customlists.id AND customlistdata.id = ? AND customlistdata.teamid = ?;";
$dbconn = getConnectionFromSession($session);
$customlistitemResults = executeQuery($dbconn, $strSQL, $bError, array($customlistitemid, $teamid));

if (count($customlistitemResults) > 0) { ?>
<h4><?php echo $customlistitemResults [0]["name"]?> : <?php echo $customlistitemResults [0]["listitemname"]?></h4>
<div class="indented-group-noborder">
<form action="/1team/edit-custom-list-item.php" method="post">
<input type="hidden" name="id" value="<?php echo $customlistitemid ?>"/>
<input type="hidden" name="customlistid" value="<?php echo $customlistid ?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<?php buildRequiredPostFields($session) ?>
<table class="noborders">
<td class="strong">List item name</td><td><input type="text" name="name" size="60" maxlength="80" value="<?php echo $customlistitemResults[0]["listitemname"]?>"></td></tr>
<td class="strong">List item order</td><td><?php echo $customlistitemResults[0]["listorder"]?></td></tr>
</table>
</div>
<input type="submit" value="Modify list item" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'edit-custom-list-form.php?<?php echo returnRequiredParams($session) . "&teamid=" . $teamid . "&id=" . $customlistid?>'"/>
</form>
<?php
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
