<?php  
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Edit Order Item"; 
include('header.php');
?>
<script type="text/javascript"> 
dojo.require("dijit.form.DateTextBox");
dojo.require("dijit.form.CurrencyTextBox");
</script>

<?php
$dbh = getDBH($session);
$bError = false;

// teamid depends on who is calling 
if (isUser($session, Role_TeamAdmin)){
	if (isset($session["teamid"])){
		$teamid = $session["teamid"];
	} else {
		$bError = true;
	}
} else {
	if (isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	} else {
		$bError = true;
	}
}

if (isset($_GET["paymentid"])) {
	$paymentid = $_GET["paymentid"];
} else {
	$bError = true;
}

if (isset($_GET["uid"])) {
	$userid = $_GET["uid"];
} else {
	$bError = true;
}

if (!$bError) {
	$strSQL = "SELECT * FROM orderitems WHERE id = ? AND teamid = ?;";
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array($paymentid, $teamid));
	
	$paymentResults = $pdostatement->fetch();
	
	if (count($paymentResults) > 0) { ?>
<h3><?php echo $title . " for " . getTeamName2($teamid, $dbh) . " " . $teamterms["termmember"] . " " . getUserName2($userid, $dbh);?></h3>	
<form name="orderitemform" action="/1team/edit-orderitem.php" method="post">
<input type="hidden" name="paymentid" value="<?php echo $paymentid ?>"/>
<input type="hidden" name="uid" value="<?php echo $userid ?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<?php buildRequiredPostFields($session) ?>
<table class="noborders">
<tr><td class="bold">Date</td><td><input type="text" name="paymentdate" id="paymentdate" value="<?php echo $paymentResults["paymentdate"]?>" dojoType="dijit.form.DateTextBox" required="true" /></td></tr>
<tr><td class="bold">Amount ($US)</td><td><input type="text" id="amount" name="amount" value="<?php echo $paymentResults["amount"]?>" dojoType="dijit.form.CurrencyTextBox" required="true" constraints="{fractional:true}" currency="USD" invalidMessage="Invalid amount.  Include dollars and cents." /></td></tr>
<tr><td class="bold">Fee ($US)</td><td><input type="text" id="fee" name="fee" value="<?php echo $paymentResults["fee"]?>" dojoType="dijit.form.CurrencyTextBox" required="false" constraints="{max:0}" currency="USD" invalidMessage="Invalid amount.  Must be a negative number. Include dollars and cents." /></td></tr>
<tr><td class="bold">SKU</td><td>
<?php 
	// GEt payment methods for this team 
	$strSQL = "SELECT * FROM skus WHERE teamid = ? ORDER BY listorder";
	$pdostatementS = $dbh->prepare($strSQL);
	$bError = ! $pdostatementS->execute(array($teamid));
	$skuResults = $pdostatementS->fetchAll();
	$rowCountS = count( $skuResults);
	
	// Display programs for this team
	if ($rowCountS > 0) { 
		$countRowsS = 0; ?>		
<select name="skuid" onchange="onSkuChanged(this.selectedIndex);">
<option value="<?php echo Sku::SkuID_Undefined?>" <?php if ((empty($paymentResults["skuid"])) || ($paymentResults["skuid"] == Sku::SkuID_Undefined) ) echo ' selected'?>>Select SKU purchased with this payment...</option>
<?php 
		while ($countRowsS < $rowCountS) {
			echo "<option value=\"";
			echo $skuResults[$countRowsS]["id"];
			echo "\"";
			if ( $paymentResults["skuid"] == $skuResults[$countRowsS]["id"] ) {
				echo(" selected");
			}
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
</td></tr>
<tr><td class="bold">Number of events remaining</td><td><input type="text" id="numeventsremaining" name="numeventsremaining" value="<?php echo $paymentResults["numeventsremaining"]?>" /></td></tr>
<tr><td class="bold">Payment for program</td><td>
<?php
	// GEt programs for this team 
	$strSQL = "SELECT * FROM programs WHERE teamid = ? ORDER BY listorder";
	$pdostatementP = $dbh->prepare($strSQL);
	$bError = ! $pdostatementP->execute(array($teamid));
	$programResults = $pdostatementP->fetchAll();
	$rowCountP	  = count( $programResults);
	
	// Display programs for this team
	if ($rowCountP > 0) { 
		$countRowsP = 0; ?>
<select name="programid">
<?php 
		while ($countRowsP < $rowCountP) {
			echo "<option value=\"";
			echo $programResults[$countRowsP]["id"];
			echo "\"";
			if ( $paymentResults["programid"] == $programResults[$countRowsP]["id"] ) {
				echo(" selected");
			}
			echo ">";
			echo $programResults[$countRowsP]["name"];
			echo "</option>\n";
			$countRowsP ++;
		} ?>
</select>				
<?php 
	} else {
		echo 'No programs are defined for ' . $teamname . '. <a href="/1team/manage-programs-form.php?teamid=' . $teamid . buildRequiredParamsConcat($session) . '">Define programs</a>.';
	} ?>
</td></tr>
<tr><td class="bold">Method</td><td>
<?php	 
	// GEt payment methods for this team 
	$strSQL = "SELECT * FROM paymentmethods WHERE teamid = ? ORDER BY listorder;";
	$pdostatementPM = $dbh->prepare($strSQL);
	$bError = ! $pdostatementPM->execute(array($teamid));
	$paymentmethodResults = $pdostatementPM->fetchAll();
	$rowCountPM	  = count( $paymentmethodResults);
	
	// Display paymentmethods for this team
	if ($rowCountPM > 0) { 
		$countRowsPM = 0; ?>
<select name="paymentmethod">
<?php 
		while ($countRowsPM < $rowCountPM) {
			echo "<option value=\"";
			echo $paymentmethodResults[$countRowsPM]["id"];
			echo "\"";
			if ( $paymentResults["paymentmethod"] == $paymentmethodResults[$countRowsPM]["id"] ) {
				echo(" selected");
			}
			echo ">";
			echo $paymentmethodResults[$countRowsPM]["name"];
			echo "</option>\n";
			$countRowsPM ++;
		} ?>
</select>				
<?php 
	} else {
		echo 'No payment methods are defined for ' . $teamname . '. <a href="/1team/manage-payment-types-form.php?teamid=' . $teamid . buildRequiredParamsConcat($session) . '">Define payment methods</a>.';
	} ?>
</td></tr>
<tr><td class="bold">Paid?</td><td><input type="checkbox" name="ispaid" <?php if ($paymentResults["ispaid"]) echo "checked='checked'";?> /></td></tr>
<tr><td class="bold">Refunded?</td><td><input type="checkbox" name="isrefunded" <?php if ($paymentResults["isrefunded"]) echo "checked='checked'";?> /></td></tr>
<tr><td></td><td><input type="submit" value="Modify payment" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>&nbsp;<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = '<?php echo $_SERVER["HTTP_REFERER"]?>'"/>
</form></td></tr>
</table>
<script type="text/javascript">
var skuID=new Array();
var skuAmount=new Array();
var skuNumEvents=new Array();
skuID[0] = <?php echo Sku::SkuID_Undefined.";\n";?>
skuAmount[0] = 0.00;
skuNumEvents[0] = 0;
<?php
		// build array for javascript
		if ($rowCountS > 0) {
			$countRowsS = 0;
			while ($countRowsS < $rowCountS) {
				echo "skuID[". ($countRowsS+1) . "] = " . $skuResults[$countRowsS]["id"] . ";\n";
				echo "skuAmount[". ($countRowsS+1) . "] = " . $skuResults[$countRowsS]["price"] . ";\n";
				if ($skuResults[$countRowsS]["numevents"] > 0 )
					$numEvents = $skuResults[$countRowsS]["numevents"];
				else if ($skuResults[$countRowsS]["numevents"] == Sku::NumEventsUnlimited ) $numEvents = "'Unlimited'";
				else $numEvents = 0;
				echo "skuNumEvents[". ($countRowsS+1) . "] = " . $numEvents . ";\n";
				$countRowsS ++;
			}
		}?>
function onSkuChanged( idx){
	document.orderitemform.numeventsremaining.value = skuNumEvents[idx];
	var price;
	price = skuAmount[idx];
	dojo.byId('amount').value = price.toFixed(2);
	// This is a hack to get the dojo widget to pretty up the auto-entered value
	dojo.byId('amount').focus();
	// Now put focus back to the select list
	document.orderitemform.skuid.focus();

}
</script>
</form>
<?php 
	$pageMode = "expand"; ?>
<h4><a class="linkopacity" href="javascript:togglerender('paymenthist', 'paymenthistory','include-payment-history.php?<?php echo returnRequiredParams($session)?>&id=<?php echo $userid?>&teamid=<?php echo $teamid?>&pagemode=embedded' )">Payment History<img src="img/a_expand.gif" alt="expand section" id="paymenthist_img" border="0"></a></h4>
<div class="hideit" id="paymenthist">
<iframe src=""
	id="paymenthistory" name="paymenthistory"
	style="width: 800px;
	height: 540px;
	border:none"
></iframe>
</div>
	<?php
	} 
} 
if ($bError) {
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
