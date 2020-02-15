<?php
ob_start(); 	// This caches non-header output, allowing us to redirect after header.php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title= " Order or Invoice" ;
include('header.php'); ?>
<script type="text/javascript">
dojo.require("dijit.form.DateTextBox");
dojo.require("dijit.form.CurrencyTextBox");
</script>

<?php

$bError = false;
if (isset($_GET["id"])) {
	$orderid = $_GET["id"];
} else {
	$bError = true;
	$err = "i";
	$orderid = 0;
}

if (isset($_GET["uid"])) {
	$userid = $_GET["uid"];
} else {
	$userid = User::UserID_Undefined;
}

// teamid depends on who is calling
if (isUser($session, Role_TeamAdmin)){
	if (isset($session["teamid"])){
		$teamid = $session["teamid"];
	} else {
		$bError = true;
		$err = "t";
	}
} else {
	if (isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	} else {
		$bError = true;
		$err = "t";
	}
}

if (!$bError){
	$dbconn = getConnectionFromSession($session);
	$strSQL = "SELECT users.firstname, users.lastname,  paymentmethods.name as paymentmethodname, programs.name as programname, skus.*, skus.name as skuname, orderitems.id as orderitemid, orderitems.*, orders.paymentmethod as orderpaymentmethod, orders.ispaid as orderispaid, orders.* FROM (orders INNER JOIN (paymentmethods RIGHT OUTER JOIN (programs INNER JOIN (users RIGHT OUTER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid and users.id = ? ) on orderitems.programid = programs.id) on orderitems.paymentmethod = paymentmethods.id) ON orderitems.orderid = orders.id) WHERE orderid = ? AND orderitems.teamid = ? ORDER BY paymentdate DESC;";
	$orderResults = executeQuery($dbconn, $strSQL, $bError, array($userid, $orderid, $teamid));

	$countOrders = 0;
	$numOrders = count($orderResults);

	if ($numOrders > 0){
		$ispaid = $orderResults[0]["ispaid"];
		// Conditionally include user name in title
		if ( $userid >= UserID_Base ) {
			$bDisplayUserSelector = false;
			$username = getUserName( $userid, $dbconn); ?>
<h3><?php echo invoiceOrOrder($ispaid) ?>&nbsp;for&nbsp;<a href="user-props-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $userid?>"><?php echo $username?></a></h3>
<?php
		} else {
			$bDisplayUserSelector = true;
			$username = "";?>
<h3><?php echo invoiceOrOrder($ispaid) ?>&nbsp;for &nbsp;<?php echo getTeamName($teamid, $dbconn)?>&nbsp;<?php echo $teamterms["termmember"]?></h3>
<?php
		}

		$bOkForm = true;

		// If invalid userID, Build a select list of all billable and active students to allow selection
		if ($bDisplayUserSelector) {
			// Team admin must get team id from session
			if (isUser( $session, Role_TeamAdmin)) {
				$teamid = $session["teamid"];
				$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid FROM users, useraccountinfo WHERE isbillable = true and status = " . UserAccountStatus_Active . " and users.teamid = ? and users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
				$userResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));
				if ($bError) $err = "s";
			// App Admin query isn't team specific
			} else {
				$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid FROM users, useraccountinfo WHERE isbillable = true and status = " . UserAccountStatus_Active . " and users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
				$userResults = executeQuery($dbconn, $strSQL, $bError));
				if ($bError) $err = "s";
			}
			$countRows = 0;
			$numRows = count($userResults);

			// If no members, tell them so and don't display the form
			if ($numRows == 0) {
				echo "<p>No " . $teamterms["termmember"]. "s exist in the team " .getTeamName($teamid, $dbconn) . "<br>\n";
				echo '<a href="/1team/new-user-form.php?' . returnRequiredParams($session) . '">Create a team member</a></p>';
				echo "\n";
				$bOkForm = false;
			}
		}
		if ($bOkForm ){ ?>
<div class="indented-group-noborder">
<form name="editorderform" action="/1team/edit-order.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="order" value="" />
<input type="hidden" name="id" value="<?php echo $orderid?>" />
<input type="hidden" name="numorderitems" value="<?php echo $numOrders?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<?php
			// Real userid passed in, add as hidden field
			if ( !$bDisplayUserSelector) {  ?>
<input type="hidden" name="uid" value="<?php echo $userid ?>"/>
<?php
			} ?>
<table class="noborders">
<tr><td class="bold"><?php echo $teamterms["termmember"]?></td>
<td>
<?php
			if (!$bDisplayUserSelector) {
				echo $username; ?>
<input type="hidden" name="uid" value="<?php echo $userid?>" />
<?php
			} else {?>
<select name="uid"><option value="<?php echo User::UserID_Undefined?>" <?php if ($userid == User::UserID_Undefined) echo " selected"?>>Select <?php echo $teamterms["termmember"]?>&hellip;</option>
<option value="<?php echo User::UserID_Guest?>" <?php if ($userid == User::UserID_Guest) echo " selected"?>>Non-<?php echo $teamterms["termmember"] . " (" . User::Username_Guest?>)</option>
<?php
				while ($countRows < $numRows) {
					echo "<option value=\"";
					echo $userResults[$countRows]["id"];
					echo "\"";
					if ( $userid == $userResults[$countRows]["id"] ) {
						echo("selected");
					}
					echo ">";
					echo $userResults[$countRows]["firstname"];
					echo " ";
					echo $userResults[$countRows]["lastname"];
					echo " " . roleToStr($userResults[$countRows]["roleid"], $teamterms);
					echo "</option>\n";
					$countRows ++;
				} ?>
</select>
<?php
			}?>
</td></tr>
<tr><td class="bold">Date</td>
<td><input type="text" name="orderdate" id="orderdate" value="<?php echo $orderResults[0]["orderdate"]?>" dojoType="dijit.form.DateTextBox" required="true" /></td></tr>
<?php

			// GEt payment methods for this team
			$strSQL = "SELECT * FROM paymentmethods WHERE teamid = ?";
			$paymenttypeResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));
			$rowCountPM	  = count( $paymenttypeResults);

			// Display paymentmethods for this team
			if ($rowCountPM > 0) {
				$countRowsPM = 0; ?>
<tr><td class="bold">Method</td><td>
<select name="paymentmethod">
<option value="<?php echo PaymentMethod_Undefined?>">Select a payment method...</option><?php
				while ($countRowsPM < $rowCountPM) {
					echo "<option value=\"";
					echo $paymenttypeResults[$countRowsPM]["id"];
					echo '"';
					if ($paymenttypeResults[$countRowsPM]["id"] == $orderResults[0]["orderpaymentmethod"])
						echo " selected";
					echo '>';
					echo $paymenttypeResults[$countRowsPM]["name"];
					echo "</option>\n";
					$countRowsPM ++;
				} ?>
</select>
</td></tr>
<?php
			} ?>
<tr><td class="bold">Paid?</td><td><input type="checkbox" name="ispaid" id="ispaid" onchange="isPaidChanged()" <?php if ($orderResults[0]["ispaid"]) echo "checked";?>/></td><tr>
</table>
<div id="duedatediv" <?php if ($orderResults[0]["ispaid"]) echo 'class="hideit"';?>>
<table class="noborders">
<tr><td class="bold">Due Date</td><td ><input type="text" name="duedate" id="duedate" value="<?php echo $orderResults[0]["duedate"]?>" dojoType="dijit.form.DateTextBox" required="true" /></td></tr>
</table>
</div>
<table class="noborders" width="65%" id="ordertable">
<thead class="head">
<th width="5%" >#</th>
<th width="45%" style="text-align:left">SKU</th>
<th width="20%" style="text-align:left">Amount ($US)</th>
<th width="20%" style="text-align:left">Fee ($US)</th>
</thead>
<?php
			// GEt skus once for use in the selector used in the loop
			$strSQL = "SELECT * FROM skus WHERE teamid = ? ORDER BY listorder";
			$skuResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));
			$rowCountS = count( $skuResults);
			$amountTotal = 0.00;
			$feeTotal = 0.00;

			// One set of edit controls per orderitem
			while ($countOrders < $numOrders){ ?>
<tr id="orderitem<?php echo $orderResults[$countOrders]["orderitemid"]?>" class="<?php if ((bool)( ($countOrders) % 2 )) echo("even"); else echo("odd") ?>">
<td width="5%"><?php echo $countOrders+1?></td>
<td width="45%"><?php

				// Display skus for this team
				if ($rowCountS > 0) {
					$countRowsS = 0; ?>
<?php
					while ($countRowsS < $rowCountS) {
//						echo "<option value=\"";
//						echo $skuResults[$countRowsS]["id"];
//						echo "\"";
						if ($skuResults[$countRowsS]["id"] == $orderResults[$countOrders]["skuid"])
//							echo " selected";
//						echo ">";
						echo $skuResults[$countRowsS]["name"];
//						echo "</option>\n";
						$countRowsS ++;
					}
				}  else {
					echo 'No SKUs are defined for ' . $teamname . '. <a href="/1team/manage-skus-form.php?teamid=' . $teamid . buildRequiredParamsConcat($session) . '">Define SKUs</a>.';
				} ?>
</td>
<td width="20%"><?php echo $orderResults[$countOrders]["amount"]?><!--<input type="text" id="amount<?php echo $countOrders?>" name="amount<?php echo $countOrders?>" value="<?php echo $orderResults[$countOrders]["amount"]?>" style="width: 8em" dojoType="dijit.form.CurrencyTextBox" required="true" constraints="{fractional:true}" currency="USD" invalidMessage="Invalid amount.  Include dollars and cents." />--></td>
<td width="20%"<?php if ($orderResults[$countOrders]["fee"] < 0) echo 'class="debit"';?>><?php echo $orderResults[$countOrders]["fee"]?><!--<input type="text" id="fee<?php echo $countOrders?>" name="fee<?php echo $countOrders?>" value="<?php echo $orderResults[$countOrders]["fee"]?>" style="width: 6em" dojoType="dijit.form.CurrencyTextBox" required="false" constraints="{max:0,fractional:true}" currency="USD" />--></td>
<?php
				$amountTotal += $orderResults[$countOrders]["amount"];
				$feeTotal += $orderResults[$countOrders]["fee"];
				$countOrders++;
			} ?>
</tr>
<thead>
<th colspan="5"></th>
</thead>
</table>
<table class="noborders" id="ordertablenewitems" width="65%">
<tr>
<td width="5%"></td>
<td width="45%"></td>
<td width="20%"></td>
<td width="20%"></td>
</tr>
</table>
<table class="noborders" width="65%">
<thead>
<th colspan="5"></th>
</thead>
<tr class="totalrow">
<td width="5%" class="bold"></td>
<td width="45%" style="font-size: 1.5em;font-weight: bolder;"><div id="totallabel"><div id="totalnet"></div></div></td>
<td width="20%" class="bold"><div id="totalamt"></div></td>
<td width="20%" class="debit"><div id="totalfee"></div></td>
</tr>
</table>
<table class="noborders" width="65%">
<thead>
<th colspan="5"></th>
</thead>
</table>
<?php //<h5 class="expandable"><a class="linkopacity" href="javascript:void(0)" onclick="javascript:togglevis('neworderitemh');return false">Add a new item to the existing order<img src="img/a_expand.gif" alt="expand section" id="neworderitemh_img" border="0"></a></h5> ?>
<div class="hideit" id="neworderitemh" name="neworderitemh">
<table class="noborders" width="65%">
<tr><td width="5%"></td>
<td width="45%">
<?php
			// Add new item to order
			// Display skus for this team in selector
			if ($rowCountS > 0) {
				$countRowsS = 0; ?>
<select id="skuid" name="skuid" onchange="onNewItemChanged(this.selectedIndex);">
<option value="<?php echo Sku::SkuID_Undefined?>" <?php if ((empty($paymentResults["skuid"])) || ($paymentResults["skuid"] == Sku::SkuID_Undefined) ) echo ' selected'?>>Select SKU purchased with this payment...</option>
<?php
				while ($countRowsS < $rowCountS) {
					echo "<option value=\"";
					echo $skuResults[$countRowsS]["id"];
					echo "\"";
					echo ">";
					echo $skuResults[$countRowsS]["name"];
					echo "</option>\n";
					$countRowsS ++;
				} ?>
</select>
<?php
			}  else {
				echo 'No SKUs are defined for ' . $teamname . '. <a href="/1team/manage-skus-form.php?teamid=' . $teamid . buildRequiredParamsConcat($session) . '">Define SKUs</a>.';
			} ?>
</td>
<script type="text/javascript">
var skuID=new Array();
var skuAmount=new Array();
<?php
			// build array for javascript
			if ($rowCountS > 0) {
				$countRowsS = 0;
				while ($countRowsS < $rowCountS) {
					echo "	skuID[". $countRowsS . "] = " . $skuResults[$countRowsS]["id"] . ";\n";
					echo "	skuAmount[". $countRowsS . "] = " . $skuResults[$countRowsS]["price"] . ";\n";
					$countRowsS ++;
				}
			}?>
</script>
<td width="20%"><input type="text" id="amount" name="amount" value="0.00" style="width: 8em" dojoType="dijit.form.CurrencyTextBox" required="true" constraints="{fractional:true}" currency="USD" invalidMessage="Invalid amount.  Include dollars and cents." /></td>
<td width="20%" class="debit"><input type="text" id="fee" name="fee" value="0.00" style="width: 6em" dojoType="dijit.form.CurrencyTextBox" required="false" constraints="{fractional:true}" currency="USD"/></td>
<td width="10%"><div id="additembuttondiv" class="hideit"><a href="#" title="Add item to order" onClick="addrow('ordertablenewitems', neworderitem_tablerowid++, 'skuid', 'amount','fee');"><img src="img/add.gif" alt="Add item" border="0"></a></div></td>
</tr>
</table>
</div>
</div>
<?php
			if ($userid == User::UserID_Undefined) {
				$objid = $teamid;
				$whomode = "team";
			} else {
				$objid = $userid;
				$whomode = "user";
			}?>
<script type="text/javascript">
var total = 0;
var totalfee = 0;
var totalnet = 0;
var totallabeldiv = document.getElementById('totallabel');
var totalamtdiv = document.getElementById('totalamt');
var totalfeediv = document.getElementById('totalfee');
var totalnetdiv = document.getElementById('totalnet');
// This is our order
var order = new Array();
// Add items to the order array based on existing items. More items may come later.
<?php
			$countOrders = 0;
			// One set of edit controls per orderitem
			while ($countOrders < $numOrders) { ?>
// New order array element
order[<?php echo $countOrders?>] = new Array(<?php echo Order::OrderItemArraySize_Edit?>);
order[<?php echo $countOrders?>][<?php echo Order::OrderItemArrayIndex_SKU?>] = <?php echo $orderResults[$countOrders]["skuid"]?>;
order[<?php echo $countOrders?>][<?php echo Order::OrderItemArrayIndex_Amount?>] = <?php echo $orderResults[$countOrders]["amount"]?>;
order[<?php echo $countOrders?>][<?php echo Order::OrderItemArrayIndex_Fee?>] = <?php echo $orderResults[$countOrders]["fee"]?>;
order[<?php echo $countOrders?>][<?php echo Order::OrderItemArrayIndex_Orderitemid?>] = <?php echo $orderResults[$countOrders]["orderitemid"]?>;

updateTotals(order[<?php echo $countOrders?>][1], order[<?php echo $countOrders?>][2]);
<?php
				$countOrders ++;
			}
?>
document.editorderform.order.value = order.toString();

var numorderitems = <?php echo $numOrders?>;
var neworderitem_tablerowid = 1;
// Index of last order item array element
var orderitemidx = <?php echo $countOrders-1?>;

// Update the totals of the order
function updateTotals(amount, fee){
	total += amount;

	// Add decimals to make it look pretty
	if (Math.round(total) == total) deci = ".00"; else deci = "";
	totalamtdiv.innerHTML = "$ " + total + deci;

	totalfee += fee;
	totalfee = Math.round(totalfee*100)/100
	// Add decimals to make it look pretty
	if (Math.round(totalfee) == totalfee) deci = ".00"; else deci = "";
	if (totalfee < 0) {
		totalfeediv.innerHTML = "$ (" + (-totalfee) + deci + ")";
	}else {
		totalfeediv.innerHTML = "$ " + totalfee + deci;
	}

	// Keep a running total
	totalnet = total + totalfee;
	// Add decimals to make it look pretty
	if (Math.round(totalnet) == totalnet) deci = ".00"; else deci = "";
	totalnetdiv.innerHTML = "<?php echo invoiceOrOrder($ispaid) ?> Total: $ " + totalnet + deci;

}

// Add an item to the order, add a row to the order table using DOM methods
function addrow(tableid, tablerow, skuformid, amtformid, feeformid, orderitemid){
	var table = document.getElementById(tableid);
	// Insert a row in the table
	var newrow   = table.insertRow(tablerow);
	var rowclass;

debugOrder('before add');
	// New order array element
	order[++orderitemidx] = new Array(<?php echo Order::OrderItemArraySize_Edit?>);

	// Alternate odd/even class for visual appeal.
	if (Boolean( orderitemidx % 2 ))
		rowclass = "even";
	else
		rowclass = "odd";

	newrow.setAttribute('id', 'neworderitem'+tablerow);
	newrow.setAttribute('class', rowclass);
	// Insert a cell in the row for sku
	var numcell = newrow.insertCell(0);
	numcell.setAttribute('width','5%');
	// Show item number, which is 1-based. Array is 0-based, so add 1.
	var numnode = document.createTextNode(orderitemidx+1);
	numcell.appendChild( numnode );
	newrow.appendChild( numcell );

	// Insert a cell in the row for sku
	var skucell = newrow.insertCell(0);
	skucell.setAttribute('width','45%');
	var elmsku = document.getElementById(skuformid);
	var txtsku = elmsku.options[elmsku.selectedIndex].text;
	var nodesku = document.createTextNode(txtsku);
	skucell.appendChild( nodesku );
	newrow.appendChild( skucell );
	// Store the skuid in the order
	order[orderitemidx][<?php echo Order::OrderItemArrayIndex_SKU?>] = elmsku.options[elmsku.selectedIndex].value;

	var amtcell = newrow.insertCell(0);
	amtcell.setAttribute('width','20%');
	var elmamt = document.getElementById(amtformid);
	var txtamt = elmamt.value;
	var nodeamt = document.createTextNode(txtamt);
	amtcell.appendChild( nodeamt );
	newrow.appendChild( amtcell );
	// Keep a running total
	var floatamt = parseFloat(txtamt.substring(1)) + 0.00;
	// Store the amount in the order
	order[orderitemidx][<?php echo Order::OrderItemArrayIndex_Amount?>] = floatamt;

	var feecell = newrow.insertCell(0);
	feecell.setAttribute('width','20%');
	var elmfee = document.getElementById(feeformid);
	var txtfee = elmfee.value;
	var nodefee = document.createTextNode(txtfee);
	// Keep a running total
	var floatfee = parseFloat(txtfee.substring(txtfee.indexOf('$')+1)) + 0.00;
	floatfee = floatfee.toPrecision(2);
	if (floatfee > 0) floatfee = -floatfee;
	if (floatfee < 0) feecell.setAttribute('class','debit');
	feecell.appendChild( nodefee );
	newrow.appendChild( feecell );
	// Store the fee in the order
	order[orderitemidx][<?php echo Order::OrderItemArrayIndex_Fee?>] = floatfee;
	// Store the orderitemid in the order
	order[orderitemidx][<?php echo Order::OrderItemArrayIndex_Orderitemid?>] = orderitemid;

	var actioncell = newrow.insertCell(0);
	actioncell.setAttribute('width','10%');
	var a=document.createElement('a');
	a.setAttribute('href','#');
	a.setAttribute('onClick','deleteRowNewItemTable( "ordertablenewitems", "neworderitem'+tablerow+'", '+orderitemidx+')');
	var img = document.createElement("img");
	img.setAttribute('src', 'img/delete.png');
	img.setAttribute('alt', 'Delete item');
	img.setAttribute('border', '0');
	a.appendChild(img);
	actioncell.appendChild(a);
	newrow.appendChild( actioncell );

	// Update the totals in the 3 totals divs
	updateTotals(floatamt, floatfee);

	// Finally, store the order into the form
	document.editorderform.order.value = order.toString();
	document.editorderform.numorderitems.value = ++numorderitems;

debugOrder('after add');

}

function debugOrder(str){
	var outstr = "Order " + str + ": ";
	for (var i= 0; i< order.length; i++){
		outstr += " ["+i+"]="+order[i];
	}
	alert (outstr);
}
// Delete the row from the table
function deleteRow( table, rowid, orderidx){
	// Get the amounts from the order array so we can update the totals
	amount = order[orderidx][<?php echo OrderItemArrayIndex_Amount?>];
	fee = order[orderidx][<?php echo OrderItemArrayIndex_Fee?>];
	updateTotals(-amount, -fee);

	var el = document.getElementById(rowid);
	el.parentNode.removeChild(el);
//	document.getElementById(table).deleteRow(rownum);
	order.splice(orderidx, 1);
//	neworderitem_tablerowid--;
}

// Delete the row from the table
function deleteRowNewItemTable( table, rowid, orderidx){
	// Get the amounts from the order array so we can update the totals
	amount = order[orderidx][<?php echo OrderItemArrayIndex_Amount?>];
	fee = order[orderidx][<?php echo OrderItemArrayIndex_Fee?>];
	updateTotals(-amount, -fee);

	var el = document.getElementById(rowid);
	el.parentNode.removeChild(el);
//	document.getElementById(table).deleteRow(rownum);
	order.splice(orderidx, 1);
debugOrder('after del');
	neworderitem_tablerowid--;
}
// Set the amount value automatically based on the entered amount.
function onSkuChanged(skuindex, orderid){
	if (skuAmount instanceof Array) {
		price = skuAmount[skuindex-1];
		dojo.byId('amount'+orderid).value = price;
	}
	// This is a hack to get the dojo widget to pretty up the auto-entered value
	dojo.byId('amount'+orderid).focus();
	// Now put focus back to the select list
	document.getElementByID('skuid'+orderid).focus;
}

// show the add item button if there is a sku selected
function onNewItemChanged(skuindex){
	if (skuindex != 0)
		showit('additembuttondiv');
	else hideit('additembuttondiv');
	if (skuAmount instanceof Array) {
		price = skuAmount[skuindex-1];
		dojo.byId('amount').value = price;
	}
	// This is a hack to get the dojo widget to pretty up the auto-entered value
	dojo.byId('amount').focus();
	// Now put focus back to the select list
	document.editorderform.skuid.focus();
}

<?php
			// Get skus and build javascript array mapping skus to amounts
			$strSQL = "SELECT * FROM skus WHERE teamid = ? ORDER BY listorder";
			$skuResults = $executeQuery($dbconn, $strSQL, $bError, array($teamid));
			$rowCountS = count( $skuResults);

			// Display skus for this team
			if ($rowCountS > 0) {
				$countRowsS = 0; ?>
var skuID=new Array();
var skuAmount=new Array();
<?php
				// build array for javascript
				if ($rowCountS > 0) {
					$countRowsS = 0;
					while ($countRowsS < $rowCountS) {
						echo "	skuID[". $countRowsS . "] = " . $skuResults[$countRowsS]["id"] . ";\n";
						echo "	skuAmount[". $countRowsS . "] = " . $skuResults[$countRowsS]["price"] . ";\n";
						$countRowsS ++;
					}
				}?>
<?php
			}
?>
function isPaidChanged(){
	if (document.editorderform.ispaid.value == "on"){
		hideit('duedatediv');
		hideit('printinvoicediv');
		showit('printreceiptdiv')
	} else {
		showit('duedatediv');
		hideit('printreceiptdiv');
		showit('printinvoicediv')
	}
}
</script>
<?php          ?>
<table class="noborders" width="65%">
<tr>
<td width="5%"></td>
<td width="45%"><input type="submit" value="Submit" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>&nbsp;<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = '<?php if (isset($_SERVER["HTTP_REFERER"])) echo $_SERVER["HTTP_REFERER"]; else echo "manage-orders-form.php?". returnRequiredParams($session) ."&teamid=" . $teamid ?>'"/>
</td>
<td width="20%">
</td>
<td width="20%">
<div id="printinvoicediv" <?php if ($ispaid) echo 'class="hideit"';?>>
<input type="button" value="Print Invoice" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'print-order.php?<?php echo returnRequiredParams($session) ."&teamid=" . $teamid . "&orderid=" . $orderid ."&uid=".$userid?>'"/>
</div>
<div id="printreceiptdiv" <?php if (!$ispaid) echo 'class="hideit"';?>>
<input type="button" value="Print Receipt" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'print-order.php?<?php echo returnRequiredParams($session) ."&teamid=" . $teamid . "&orderid=" . $orderid ."&uid=".$userid?>'"/>
</div>
</td>
</tr>
</table>
</form>

<?php
		}
	} else {
		$bError = true;
		$err = "no";
	}
}
if ($bError){
//	redirect( "manage-orders-form.php?".returnRequiredParams($session)."&teamid=" . $teamid . "&err=".$err);
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
<?php
ob_end_flush();

function invoiceOrOrder($ispaid ){
	if ($ispaid) return "Order";
	else return "Invoice";
}?>
