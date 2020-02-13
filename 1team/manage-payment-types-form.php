<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Manage Payment Methods";
include('header.php');
?>
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
	if (isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	} else{
		$bError = true;
	}
}
?>
<h4>Payment Methods for <?php echo getTeamName($teamid, $dbconn);?></h4>
<div class="helpbox">
<div class="helpboxtitle"><h5><a class="linkopacity" href="javascript:togglevis('overview')">What are Payment Methods?<img src="/1team/img/a_expand.gif" id="overview_img" border="0" alt="expand region"></a></h5></div>
<div class="hideit" id="overview">
<div class="helpboxtext">
<p>Payment methods are the various ways your customers pay you. This will allow you to track all of your payments from different sources independently. You can reorder them to change their display order, edit, delete, or add new payment methods.</p>
</div></div></div>
<?php
	$strSQL = "SELECT * FROM paymentmethods WHERE teamid = ? ORDER BY listorder;";
  $dbconn = getConnection();
	$paymentmethodResults = executeQueryFetchColumn($dbconn, $strSQL, $bError, array($teamid));

	$rowCount = 0;
	$loopMax = count($paymentmethodResults);?>
<table width="65%">
<thead>
<tr>
<th width="90%">Payment Method Name</th>
<th width="10%">Actions</th>
</tr>
</thead>
</table>
<div dojoType="dojo.dnd.Source" id="paymentmethodlist" jsId="paymentmethodlist" class="container">
<script type="dojo/method" event="creator" args="item, hint">

	// this is custom creator, which changes the avatar representation
	node = dojo.doc.createElement("div"), s = String(item);

	node.id = dojo.dnd.getUniqueId();
	node.className = "dojoDndItem";
	node.innerHTML = s; // "Reordering paymentmethod. Drop in desired order location.";
	return {node: node, data: item, type: ["text"]};
</script>
<?php
	while ($rowCount < $loopMax) { ?>

<div class="dojoDndItem">
<span id="paymentmethod<?php echo $rowCount?>" style="display:none" class="paymentmethodorderitem"><?php echo $paymentmethodResults[$rowCount]["id"]?></span><table width="65%"><tr class="even">
<td width="90%"><?php echo $paymentmethodResults[$rowCount]["name"]?></td>
<td width="10%">
<a href="delete-payment-type.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $paymentmethodResults[$rowCount]["id"]?>" title="Delete"><img src="img/delete.gif" alt="Delete" border="0"></a>&nbsp;
<a href="edit-payment-type-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $paymentmethodResults[$rowCount]["id"]?>" title="Edit"><img src="img/edit.gif" alt="Edit" border="0"></a>
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
<tr class="odd"><td colspan="4"><span class="bigstrong">Reorder payment methods</span></td></tr>
<tr class="even"><td colspan="3" width="70%"><form name="reorderpaymenttype" action="/1team/reorder-payment-types.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid?>"/>
<input type="hidden" name="paymenttypeorder" value="" />
To reorder the payment methods list, click and drag them, then press the "Reorder paymenttypes" button.</td>
<td width="10%" align="center"><input type="button" value="Reorder payment methods" onclick="getpaymentmethodOrder(this.form)" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/></td>
</tr>
</table>
</form>
<?php
	}?>
<table width="65%">
<tr class="odd"><td colspan="2"><span class="bigstrong">Add new payment method</span></td></tr>
<tr class="even">
<td width="90%"><form name="newpaymentmethod" action="/1team/new-payment-type.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="text" name="name" size="80" maxlength="80" value="New payment method name"></td>
<td width="10%"><a href="#" title="Add payment method" onClick="newpaymentmethod.submit()"><img src="img/add.gif" alt="Add paymenttype" border="0"></a></td>
</tr>
</table>
</form>
<script type="text/javascript">
function getpaymentmethodOrder(form){
	dndSource = new dojo.dnd.Source('paymentmethodlist');
	// The idea is to get all nodes from the DnD Source
	// create an array of node paymenttype ids where the index of the array+1 is the order
	// and the content of the array is the paymenttype id
	var childnodes = new Array();
	for (var i = 0; i < dndSource.getAllNodes().length; i++) {
		childnodes[i] = dndSource.getAllNodes()[i].childNodes[1].innerHTML;
	}
	document.reorderpaymenttype.paymenttypeorder.value = childnodes.toString();
	form.submit();
}
</script>
<?php
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "The payment method was not saved successfully.", "");
} else if (isset($_GET["done"])){
	showMessage("Success", "The payment method was saved successfully.");
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
