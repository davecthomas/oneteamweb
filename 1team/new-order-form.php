<?php  
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title= " Make an Order" ;
include('header.php');
$bError = false;
$err = "";
?>
<script type="text/javascript">
	dojo.require("dijit.form.DateTextBox");
	dojo.require("dijit.form.CurrencyTextBox");
</script>
<script type="text/javascript">
	var skuID=new Array();
	var skuAmount=new Array();
</script>
<?php

$bError = false;
if (isset($_GET["id"])) {
	$userid = $_GET["id"];
} else {
	$userid = User::UserID_Undefined;
} 	

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


if (!$bError) {
	
	$teamname = getTeamName($teamid, $dbconn);

	// GEt skus
	$strSQL = "SELECT * FROM skus WHERE teamid = ? ORDER BY listorder";
	$pdostatementS = $dbh->prepare($strSQL);
	$bError = ! $pdostatementS->execute(array($teamid));
	$skuResults = $pdostatementS->fetchAll();
	$rowCountS = count( $skuResults);
	// build array for javascript
	if ($rowCountS > 0) { ?>
<script type="text/javascript">
<?php
		$countRowsS = 0;
		while ($countRowsS < $rowCountS) {
			echo "	skuID[". $countRowsS . "] = " . $skuResults[$countRowsS]["id"] . ";\n";
			echo "	skuAmount[". $countRowsS . "] = " . $skuResults[$countRowsS]["price"] . ";\n";
			$countRowsS ++;
		} ?>
</script>
<script type="text/javascript">
function onSkuChanged( idx){
	price = skuAmount[idx-1];
	dojo.byId('payment').value = price;
}

function isPaidChanged(){
	if (document.neworderform.ispaid.value == "on"){
		hideit('duedatediv');
	} else {
		showit('duedatediv');
	}
}
function checkEnableSubmit(submitButton){
	var submitButtonElm = document.getElementById(submitButton);
	var uidListElm = document.getElementById('uid');
	var paymethodListElm = document.getElementById('paymentmethod');
	var isPaidElm = document.getElementById('ispaid');
	// 2 ways to enable submit:
	//	1. Have a user selected and product added and payment method and is paid
	if ((uidListElm.selectedIndex != 0) && (paymethodListElm.selectedIndex != 0) && (isPaidElm.checked == true) && (rowid > rowidbase)){
		submitButtonElm.disabled=false;
	//	2. A user selected, product added, isn't paid (this is an invoice)
	} else if ((uidListElm.selectedIndex != 0) && ((isPaidElm.checked != true) && (rowid > rowidbase))){
		submitButtonElm.disabled=false;
	} else {
		submitButtonElm.disabled=true;
	}
}
</script>
<?php

	}
	// Conditionally include user name in title
	if ( $userid >= UserID_Base ) {
		$bDisplayUserSelector = false;
		$username = getUserName( $userid, $dbconn); ?>
<h3><?php echo $title?>&nbsp;for&nbsp;<a href="user-props-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $userid?>"><?php echo $username?></a></h3>
		<?php
	} else {
		$bDisplayUserSelector = true;
		$username = "";?>
<h3><?php echo $title?>&nbsp;for a&nbsp;<?php echo getTeamName($teamid, $dbconn)?>&nbsp;<?php echo $teamterms["termmember"]?></h3>
		<?php
	}

	$bOkForm = true;

	// If invalid userID, Build a select list of all billable and active students to allow selection
	if ($bDisplayUserSelector) {
		// Note: I removed "isbillable = true and " from the following queries since sometimes non-billable students pay for stuff!
		// Team admin must get team id from session
		if (isUser( $session, Role_TeamAdmin)) {
			$teamid = $session["teamid"];
			$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid FROM users, useraccountinfo WHERE status = " . UserAccountStatus_Active . " and users.teamid = ? and users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
			$pdostatement = $dbh->prepare($strSQL);
			$bError = ! $pdostatement->execute(array($teamid));
			// App Admin query isn't team specific
		} else {
			$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid FROM users, useraccountinfo WHERE status = " . UserAccountStatus_Active . " and users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
			$pdostatement = $dbh->prepare($strSQL);
			$bError = ! $pdostatement->execute();
		}
		$userResults = $pdostatement->fetchAll();
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
	if ($bOkForm ) { ?>
<div class="indented-group-noborder">
	<form name="neworderform" action="/1team/new-order.php" method="post">
<?php
		buildRequiredPostFields($session) ?>
<input type="hidden" name="order" value="" />
<input type="hidden" name="numorderitems" value="0"/>
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
<select id="uid" name="uid" onchange="checkEnableSubmit('submit')"><option value="<?php echo User::UserID_Undefined?>" selected>Select <?php echo $teamterms["termmember"]?>&hellip;</option>
<option value="<?php echo User::UserID_Guest?>">Non-<?php echo $teamterms["termmember"] . " (" . User::Username_Guest?>)</option>
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
				<td><input type="text" name="orderdate" id="orderdate" value="<?php echo date("Y-m-d")?>" dojoType="dijit.form.DateTextBox" required="true" /></td></tr>
<tr><td class="bold">Method</td><td>
<?php 

		// GEt payment methods for this team
		$strSQL = "SELECT * FROM paymentmethods WHERE teamid = ?";
		$pdostatementPM = $dbh->prepare($strSQL);
		$bError = ! $pdostatementPM->execute(array($teamid));
		$paymenttypeResults = $pdostatementPM->fetchAll();
		$rowCountPM	  = count( $paymenttypeResults);

		// Display paymentmethods for this team
		if ($rowCountPM > 0) {
			$countRowsPM = 0; ?>
<select id="paymentmethod" name="paymentmethod" onchange="checkEnableSubmit('submit')">
<option value="<?php echo PaymentMethod_Undefined?>">Select a payment method...</option><?php
			while ($countRowsPM < $rowCountPM) {
				echo "<option value=\"";
				echo $paymenttypeResults[$countRowsPM]["id"];
				echo '">';
				echo $paymenttypeResults[$countRowsPM]["name"];
				echo "</option>\n";
				$countRowsPM ++;
			} ?>
</select>
<?php
		} else {
			echo 'No payment methods defined. <a href="manage-payment-types-form.php?'. returnRequiredParams($session).'&teamid='. $session["teamid"].'">Customize payment methods</a> before you create a new order.';
		}?>
</td></tr>
			<tr><td class="bold">Paid?</td><td><input type="checkbox" name="ispaid" id="ispaid" onchange="isPaidChanged()"/></td><tr>
		</table>
		<div id="duedatediv">
			<table class="noborders">
				<tr><td class="bold">Due Date</td><td ><input type="text" name="duedate" id="duedate" value="<?php echo date("Y-m-d")?>" dojoType="dijit.form.DateTextBox" required="true" /></td></tr>
			</table>
		</div>
		<table class="noborders" width="65%">
			<thead class="head">
			<tr>
			<th width="50%" colspan="2">SKU</th>
			<th width="20%">Amount ($US)</th>
			<th width="15%">Discount ($US)</th>
			<th width="15%">Actions</th>
			<tr>
			</thead>
			<tr><td width="5%"></td><td width="45%">
<?php 

		// Display skus for this team
		if ($rowCountS > 0) {
			$countRowsS = 0; ?>
					<select id="skuid" name="skuid" onchange="onSkuChanged(this.selectedIndex);">
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
				<td><input type="text" id="amount" name="amount" value="0.00" style="width: 8em" dojoType="dijit.form.CurrencyTextBox" required="true" constraints="{fractional:true}" currency="USD" invalidMessage="Invalid amount.  Include dollars and cents." /></td>
				<td class="debit"><input type="text" id="discount" name="discount" value="0.00" style="width: 6em" dojoType="dijit.form.CurrencyTextBox" required="false" constraints="{fractional:true}" currency="USD" invalidMessage="Invalid amount. Include dollars and cents." /></td>
				<td>
					<div id="additembuttondiv" class="hideit"><a href="#" title="Add item to order" onClick="addrow('ordertable', rowid++, 'skuid', 'amount','discount');"><img src="img/add.gif" alt="Add item" border="0"></a></div>
				</td>
			</tr>
			<thead>
			<tr>
			<th colspan="5"></th>
			</tr>
			</thead>
		</table>
		<table class="noborders" id="ordertable" width="65%">
			<tr>
				<td width="5%"></td>
				<td width="45%"></td>
				<td width="20%"></td>
				<td width="15"></td>
				<td width="15%"></td>
			</tr>
		</table>
		<table class="noborders" width="65%">
			<tr class="totalrow">
				<td width="5%" class="bold"></td>
				<td width="45%" class="bold"><div id="totallabel">Totals</div></td>
				<td width="20%" class="bold"><div id="totalamt"></div></td>
				<td width="15%" class="debit"><div id="totaldiscount"></div></td>
				<td width="15%"></td>
			</tr>
		</table>
		<table class="noborders" width="65%">
			<thead>
			<tr>
			<th colspan="5"></th>
			</tr>
			</thead>
		</table>
		<h3><div id="totalnet"></div></h3>
</div>
<?php 
			if ($userid == User::UserID_Undefined) {
				$objid = $teamid;
				$whomode = "team";
			} else {
				$objid = $userid;
				$whomode = "user";
			}?>
<input type="submit" value="Submit" id="submit" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" disabled/>&nbsp;<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'manage-orders-form.php?<?php echo returnRequiredParams($session) ."&teamid=" . $teamid ?>'"/>
</form>
<script type="text/javascript">
	// This is our order
	const rowidbase = 1;
	var order = new Array();
	var rowid = rowidbase;
	var total = 0;
	var totaldiscount = 0;
	var totalnet = 0;
	var totallabeldiv = document.getElementById('totallabel');
	var totalamtdiv = document.getElementById('totalamt');
	var totaldiscountdiv = document.getElementById('totaldiscount');
	var totalnetdiv = document.getElementById('totalnet');

	// Add an item to the order, add a row to the order table using DOM methods
	function addrow(tableid, tablerow, skuformid, amtformid, discountformid){
		var table = document.getElementById(tableid);
		// Insert a row in the table
		var newrow   = table.insertRow(tablerow);
		var rowclass;
		// Alternate odd/even class for visual appeal
		if (Boolean( tablerow % 2 ))
			rowclass = "even";
		else
			rowclass = "odd";

		// New order array element
		order[tablerow-1] = new Array(<?php echo Order::OrderItemArraySize_New?>);

		newrow.setAttribute('class', rowclass);
		// Insert a cell in the row for sku
		var numcell = newrow.insertCell(0);
		numcell.setAttribute('width','5%');
		var numnode = document.createTextNode(tablerow);
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
		order[tablerow-1][0] = elmsku.options[elmsku.selectedIndex].value;

		var amtcell = newrow.insertCell(0);
		amtcell.setAttribute('width','20%');
		var elmamt = document.getElementById(amtformid);
		var txtamt = elmamt.value;
		var nodeamt = document.createTextNode(txtamt);
		amtcell.appendChild( nodeamt );
		newrow.appendChild( amtcell );
		// Keep a running total
		if (txtamt.substring(1,1) =='$') txtamt = txtamt.substring(1);
		var floatamt = parseFloat(txtamt) + 0.00;
		total += floatamt;
		// Add decimals to make it look pretty
		if (Math.round(total) == total) deci = ".00"; else deci = "";
		totalamtdiv.innerHTML = "$ " + total + deci;
		// Store the amount in the order
		order[tablerow-1][1] = floatamt;

		var discountcell = newrow.insertCell(0);
		discountcell.setAttribute('width','15%');
		var elmdiscount = document.getElementById(discountformid);
		var txtdiscount = elmdiscount.value;
		var nodediscount = document.createTextNode(txtdiscount);
		// Keep a running total
		if (txtdiscount.substring(1,1) =='$') txtdiscount = txtdiscount.substring(1);
		var floatdiscount = parseFloat(txtdiscount.substring(1)) + 0.00;
		totaldiscount += floatdiscount;
		// Add decimals to make it look pretty
		if (Math.round(totaldiscount) == totaldiscount) deci = ".00"; else deci = "";
		totaldiscountdiv.innerHTML = "$ " + totaldiscount + deci;
		if (floatdiscount != 0) discountcell.setAttribute('class','debit');
		discountcell.appendChild( nodediscount );
		newrow.appendChild( discountcell );
		// Store the discount in the order
		order[tablerow-1][2] = floatdiscount;

		// Keep a running total
		totalnet = total - totaldiscount;
		// Add decimals to make it look pretty
		if (Math.round(totalnet) == totalnet) deci = ".00"; else deci = "";
		totalnetdiv.innerHTML = "Order Total: $ " + totalnet + deci;

		var actioncell = newrow.insertCell(0);
		actioncell.setAttribute('width','15%');
		var a=document.createElement('a');
		a.setAttribute('href','#');
		a.setAttribute('onClick','deleteRow(' + tablerow + ')');
		var img = document.createElement("img");
		img.setAttribute('src', 'img/delete.png');
		img.setAttribute('alt', 'Delete item');
		img.setAttribute('border', '0');
		a.appendChild(img);
		actioncell.appendChild(a);
		newrow.appendChild( actioncell );

		// Finally, store the order into the form
		document.neworderform.order.value = order.toString();
		document.neworderform.numorderitems.value = tablerow;

		// Once we start adding order items, we should check to see if we can enable submit
		checkEnableSubmit('submit');
	}

	// Update the totals of the order
	function updateTotals(amount, discount){
		total += amount;

		// Add decimals to make it look pretty
		if (Math.round(total) == total) deci = ".00"; else deci = "";
		totalamtdiv.innerHTML = "$ " + total + deci;

		totaldiscount += discount;
		// Add decimals to make it look pretty
		if (Math.round(totaldiscount) == totaldiscount) deci = ".00"; else deci = "";
		totaldiscountdiv.innerHTML = "$ " + totaldiscount + deci;

		// Keep a running total
		totalnet = total - totaldiscount;
		// Add decimals to make it look pretty
		if (Math.round(totalnet) == totalnet) deci = ".00"; else deci = "";
		totalnetdiv.innerHTML = "Order Total: $ " + totalnet + deci;

	}

	// Delete the row from the table
	function deleteRow(rownum){
		// Get the amounts from the order array so we can update the totals
		if (order.length < 1) echo( rownum);
		amount = order[rownum-1][1];
		discount = order[rownum-1][2];
		updateTotals(-amount, -discount);

		document.getElementById('ordertable').deleteRow(rownum);
		order.splice(rownum-1, rownum-1);
		rowid--;

		// We should check to see if we can enable submit (no order items means nope)
		checkEnableSubmit('submit');
	}
	// show the add item button if there is a sku selected
	function onSkuChanged(skuindex){
		if (skuindex != 0) {
			showit('additembuttondiv');
			if (skuAmount instanceof Array) {
				price = skuAmount[skuindex-1];
				dojo.byId('amount').value = price;
			}
			// This is a hack to get the dojo widget to pretty up the auto-entered value
			dojo.byId('amount').focus();
			// Now put focus back to the select list
			document.neworderform.skuid.focus();
		} else {
			hideit('additembuttondiv');
			dojo.byId('amount').value = "0.00";
		}
	}

	function debugOrder(str){
		var outstr = "Order " + str + ": ";
		for (var i= 0; i< order.length; i++){
			outstr += " ["+i+"]="+order[i];
		}
		alert (outstr);
	}

<?php 
		// Get skus and build javascript array mapping skus to amounts
		$strSQL = "SELECT * FROM skus WHERE teamid = ? ORDER BY listorder";
		$pdostatementS = $dbh->prepare($strSQL);
		$bError = ! $pdostatementS->execute(array($teamid));
		$skuResults = $pdostatementS->fetchAll();
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
			}
		} ?>
</script>
<?php
	}
}
if ($bError) {
	redirect("manage-orders-form.php?" . returnRequiredParams($session) ."&teamid=" . $teamid . "&err=". $err);
}
// Start footer section
include('footer.php'); ?>
</body>
</html>