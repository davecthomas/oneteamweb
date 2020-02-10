<?php  
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Order Management"; 
include('header.php');
?>
<script type="text/javascript"> 
dojo.require("dijit.form.DateTextBox");
</script>

<?php 
$bError = false;

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
$objname = getTeamName($teamid, $dbconn);

// set up sort order
$sortRequest = "firstname";
if (isset($_REQUEST["sort"])) {
	$sortRequest = trim($_REQUEST["sort"]) . "";
	$sortRequest = cleanSQL($sortRequest);
} 
if (isset($_REQUEST["programid"])) {
	$programid = (int) $_REQUEST["programid"];
	if (!is_int($programid )){
		$programid = Program_Undefined;
	}
} else {
	// Default triggers all programs
	$programid = Program_Undefined;
}

if ((isset($_REQUEST["year"])) && (is_numeric($_REQUEST["year"]))) {
	$paymentyear = $_REQUEST["year"];
	$expand = "showit";
	$expandimg = "collapse";
} else {
	$paymentyear = date("Y");
	$expand = "hideit";
	$expandimg = "expand";
}
$disablenextyearlink = ($paymentyear+1 > (int)date("Y"));

if (!$bError){
	$teamname = getTeamName($teamid, $dbconn);
	
	// All new orderitems
	if ($programid != Program_Undefined){
		$strSQL = "select SUM(amount) as total, SUM(fee) as totalfee, COUNT(amount) as numorderitems, orderteamid, orderid, uid, orderdate, duedate, orderpaid, firstname, lastname from (select orders.userid as uid, orders.ispaid as orderpaid, orders.teamid as orderteamid, * from (users RIGHT OUTER JOIN (orders RIGHT OUTER JOIN orderitems ON (orders.id = orderitems.orderid AND orderitems.programid = ?)) on users.id = orders.userid)) AS orderlist where orderteamid = ? AND orderdate >= '1/1/" . $paymentyear . "' AND orderdate <= '12/31/" . $paymentyear . "' group by orderid, uid, orderteamid, orderdate, orderpaid, duedate, firstname, lastname order by orderdate desc;";
		$pdostatement = $dbh->prepare($strSQL);
		$pdostatement->execute(array($programid, $teamid));
	} else {
		$strSQL = "select SUM(amount) as total, SUM(fee) as totalfee, COUNT(amount) as numorderitems, orderteamid, orderid, uid, orderdate, duedate, orderpaid, firstname, lastname from (select orders.userid as uid, orders.ispaid as orderpaid, orders.teamid as orderteamid, * from (users RIGHT OUTER JOIN (orders LEFT OUTER JOIN orderitems ON (orders.id = orderitems.orderid)) on users.id = orders.userid)) AS orderlist where orderteamid = ? AND orderdate >= '1/1/" . $paymentyear . "' AND orderdate <= '12/31/" . $paymentyear . "' group by orderid, uid, orderteamid, orderdate, orderpaid, duedate, firstname, lastname order by orderdate desc;";
		$pdostatement = $dbh->prepare($strSQL);
		$pdostatement->execute(array( $teamid));
	}
	$results = $pdostatement->fetchAll();
	$numOrders = count($results); 
	
	// ?>



<h4>Filter Orders</h4>
<div class="indented-group-noborder">
<script type="text/javascript">
function updateProgramID() {
	document.forms['selectprogramform'].submit();
}
</script>
<form name="selectprogramform" action="/1team/manage-orders-form.php" method="post">
<input type="hidden" name="sort" value="<?php echo $sortRequest ?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<input type="hidden" name="year" value="<?php echo $paymentyear?>"/>
<?php buildRequiredPostFields($session) ?>
<?php
			
			// GEt payment methods for this team
			$strSQL = "SELECT * FROM programs WHERE teamid = ?";
			$pdostatementP = $dbh->prepare($strSQL);
			$bError = ! $pdostatementP->execute(array($teamid));
			$programResults = $pdostatementP->fetchAll();
			$rowCountP = count( $programResults);

			// Display programs for this team
			if ($rowCountP > 0) {
				$countRowsP = 0; ?>
<table class="noborders">
<tr><td class="bold">Display orders for program</td><td>
<select name="programid" onchange="updateProgramID();">
<option value="<?php echo Program_Undefined?>" <?php if ($programid == Program_Undefined) echo " selected"?>>All programs</option>
<?php
				while ($countRowsP < $rowCountP) {
					echo '<option value="';
					echo $programResults[$countRowsP]["id"];
					echo '"';
					if ($programid == $programResults[$countRowsP]["id"]) echo " selected";
					echo ">";
					echo $programResults[$countRowsP]["name"];
					echo "</option>\n";
					$countRowsP ++;
				} ?>
</select>
</td></tr>
</table>
</form>
<?php
			} else {
				echo '<p>There are no programs defined for the team ' . $teamname . '. <a href="/1team/manage-programs-form.php?' . returnRequiredParams($session) . '&teamid=' . $teamid .'">Define programs</a>.';
			}?>

<h4>Orders for <?php echo $teamname?></h4>
<form>
<table class="memberlist"> 
<thead class="head"> 
<tr>
<th align="left"><a target="_top" href="manage-orders-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&year=<?php echo $paymentyear-1?>&sort=<?php echo $sortRequest?>&programid=<?php echo $programid?>"><img src="img/a_previous.gif" border="0" alt="previous">Previous year</a></th>
<th align="center" colspan="7"><span class="bigstrong"><?php echo $paymentyear ?></span></th>
<th align="right"><a target="_top" class="linkopacity" href="<?php
			if (!$disablenextyearlink){
				echo "manage-orders-form.php?". returnRequiredParams($session);
				echo "&teamid=". $teamid."&year=".($paymentyear+1);
				echo "&sort=". $sortRequest."&programid=".$programid;
			} else echo "#";?>">Next year<img src="img/a_next.gif" border="0" alt="next"></a></th>
</tr>
<tr><th valign="top">Date of Order</th>
<th valign="top">Due Date</th>
<th valign="top"><?php echo $teamterms["termmember"]?></th>
<th valign="top">Paid ($US)</th>
<th valign="top">Receivable ($US)</th>
<th valign="top">Fees ($US)</th>
<th valign="top">Items</th>
<th valign="top">Paid?</th>
<th valign="top">Actions</th>
</tr>
</thead>	
<tbody>
<?php 
	$rowCountOrders = 0;
	$sumFees = 0;
	$sumItems = 0;
	$sumGross = 0;
	$sumOwed = 0;
		
	while ($rowCountOrders < $numOrders) {
		$orderdate = $results[$rowCountOrders]["orderdate"];
		$duedate = $results[$rowCountOrders]["duedate"];
		$uid = $results[$rowCountOrders]["uid"];
		$ispaid = $results[$rowCountOrders]["orderpaid"];
		$total = $results[$rowCountOrders]["total"];
		$fee = $results[$rowCountOrders]["totalfee"];
		$sumFees += $fee;
		$numorderitems = $results[$rowCountOrders]["numorderitems"];
		$sumItems += $numorderitems;
		if ($ispaid)
			$sumGross += $total;
		else $sumOwed += $total;
?>
<tr class="<?php  
		if ( ($rowCountOrders+1) % 2 ) echo("even"); 
		else echo("odd");?>">
<td><?php
	 	echo $orderdate?></td>
<td><?php
	 	echo $duedate?></td>
<td><?php
		if ($results[$rowCountOrders]["uid"] == User::UserID_Guest) {
			$username = User::Username_Guest;
			echo $username;
		} else { 
			$username = $results[$rowCountOrders]["firstname"] . ' ' . $results[$rowCountOrders]["lastname"];?>
<a target="_top" href="user-props-form.php?<?php echo returnRequiredParams($session)?>&id=<?php echo $results[$rowCountOrders]["uid"]?>"><?php echo $username?></a>
	<?php
		} ?>
</td>
<td><?php if ($ispaid) echo formatMoney($total) ?></td>
<td class="debit"><?php if (!$ispaid) echo formatMoney($total) ?></td>
<td class="debit"><?php if ($fee > 0) echo formatMoney($fee) ?></td>
<td><a href="edit-order-form.php<?php buildRequiredParams($session) ?>&uid=<?php echo $uid?>&id=<?php echo $results[$rowCountOrders]["orderid"]?>&teamid=<?php echo $teamid?>"><?php echo $numorderitems?></a></td>
<td <?php if (!$ispaid) echo 'class="debit"'?>><?php echo BoolToStr((bool)$ispaid)?></td>
<td>
<a href="edit-order-form.php<?php buildRequiredParams($session) ?>&uid=<?php echo $uid?>&id=<?php echo $results[$rowCountOrders]["orderid"]?>&teamid=<?php echo $teamid?>" title="Edit order"><img src="img/cart.png" alt="Edit order" border="0"></a>&nbsp;
<a href="copy-order-form.php<?php buildRequiredParams($session) ?>&uid=<?php echo $uid?>&id=<?php echo $results[$rowCountOrders]["orderid"]?>&teamid=<?php echo $teamid?>" title="Duplicate order"><img src="img/copy_order.png" alt="Edit order" border="0"></a>&nbsp;
<a href="print-order.php<?php buildRequiredParams($session) ?>&uid=<?php echo $uid?>&orderid=<?php echo $results[$rowCountOrders]["orderid"]?>&teamid=<?php echo $teamid?>" title="Print <?php if (!$ispaid) echo "invoice"; else echo "receipt"?>"><img src="img/printer.png" alt="Print" border="0"></a>&nbsp;
<a href="print-order.php<?php buildRequiredParams($session) ?>&uid=<?php echo $uid?>&orderid=<?php echo $results[$rowCountOrders]["orderid"]?>&teamid=<?php echo $teamid?>&email=1" title="Email <?php if (!$ispaid) echo "invoice"; else echo "receipt"?>"><img src="img/email.png" alt="Email" border="0"></a>&nbsp;
<a href="#" onClick="confDelete('<?php echo $username?>', '<?php echo $orderdate?>', '<?php echo $total?>',<?php echo $results[$rowCountOrders]["orderid"]?>)" title="Delete order"><img src="img/delete.png" alt="Delete order..." border="0"></a></td></tr>
<?php             
		$rowCountOrders ++;
	}
	//  
	if ($numOrders == 0) { ?>
<tr><td colspan="8">No orders in this time period.</td></tr>
<?php 
	} else {?>
<tr>
<td colspan="3" class="moneytotal"><?php echo ($numOrders)?> orders</td>
<td class="<?php echo getMoneyTotalClass($sumGross)?>"><?php echo formatMoney($sumGross) ?></td>
<td class="<?php echo getMoneyTotalClass(-$sumOwed)?>"><?php if ($sumOwed > 0) echo formatMoney($sumOwed)?></td>
<td class="<?php echo getMoneyTotalClass($sumFees)?>"><?php echo formatMoney($sumFees) ?></td>
<td class="moneytotal" colspan="4"><?php echo $sumItems?>&nbsp;items purchased</td>
</tr>
<?php } ?>
</tbody>
</table>
</form>
<p><a href="new-order-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>" title="New order..."><img src="img/add.gif" alt="Add item" border="0">Create a new order...</a></p>
<?php
}  
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "There was an error processing this order.", "");
} else if (isset($_GET["done"])){
	showMessage("Success", "The order was processed successfully.");
} 
// Start footer section
include('footer.php'); 
?>
<script type="text/javascript">
function confDelete(name, orderdate, total, id) {
	if (confirm('Are you sure you want to delete the order placed by ' + name + ' on ' + orderdate + ' for $' + parseFloat(total).toFixed(2) + '?')) {
		document.location.href = 'delete-order.php?<?php echo returnRequiredParams($session) . "&teamid=" . $teamid . "&id="?>' + id;
	}
}

function doEdit( epayid, uid, skuid ){
	document.location.href = 'edit-order.php?<?php echo returnRequiredParams($session) . "&teamid=" . $teamid . "&id=" ?>' + epayid + '&uid=' + uid + '&skuid=' + skuid;
}
</script>
</body>
</html>