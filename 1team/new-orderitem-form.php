<?php  
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title= " Add an Order Item to an Order" ;
include('header.php'); ?>
<script type="text/javascript"> 
dojo.require("dijit.form.DateTextBox");
dojo.require("dijit.form.CurrencyTextBox");
</script>

<?php

$bError = false;
if (isset($_GET["id"])) {
	$userid = $_GET["id"];
} else {
	$userid = User::UserID_Undefined;
} 	

$teamid = NotFound;
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

  

// Conditionally include user name in title
if ( $userid >= UserID_Base ) { 
	$bDisplayUserSelector = false;?>
<h3><?php echo $title?>&nbsp;for&nbsp;<a href="user-props-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $userid?>"><?php echo getUserName( $userid, $dbconn)?></a></h3>
<?php
} else { 
	$bDisplayUserSelector = true;?>
<h3><?php echo $title?>&nbsp;for a&nbsp;<?php echo getTeamName($teamid, $dbconn)?>&nbsp;<?php echo $teamterms["termmember"]?></h3>
<?php 
} 

$bOkForm = true;

// If invalid userID, Build a select list of all billable and active students to allow selection
if ($bDisplayUserSelector) {
	// Team admin must get team id from session
	if (isUser( $session, Role_TeamAdmin)) {
		$teamid = $session["teamid"];
		$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid FROM users, useraccountinfo WHERE isbillable = true and status = " . UserAccountStatus_Active . " and users.teamid = ? and users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
		$pdostatement = $dbh->prepare($strSQL);
		$bError = ! $pdostatement->execute(array($teamid));
	// App Admin query isn't team specific
	} else {
		$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid FROM users, useraccountinfo WHERE isbillable = true and status = " . UserAccountStatus_Active . " and users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
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
if ($bOkForm ){ ?>
<div class="indented-group-noborder">
<form name="newpaymentform" action="/1team/new-orderitem.php" method="post"/>
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<?php
	// Real userid passed in, add as hidden
	if ( isValidUserID($userid )) {  ?>
<input type="hidden" name="id" value="<?php echo $userid ?>"/>
<?php
	} 
	echo '<table class="noborders">';
	if ($bDisplayUserSelector) {?>
<tr>
<td class="bold"><?php echo $teamterms["termmember"]?></td>
<td><select name="id"><option value="<?php echo User::UserID_Undefined?>" selected>Select <?php echo $teamterms["termuser"]?>&hellip;</option>
<?php
		echo("<option value=\"" . User::UserID_Guest . "\">Non-"  . $teamterms["termmember"] . " (" . User::Username_Guest . ")</option>\n");
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
</select></td></tr>
<?php 
	}?>
<tr><td class="bold">Date</td><td><input type="text" name="paymentdate" id="paymentdate" value="<?php echo date("Y-m-d")?>" dojoType="dijit.form.DateTextBox" required="true" /></td></tr>
<tr><td class="bold">SKU</td><td>
<?php 
		// GEt skus 
		$strSQL = "SELECT * FROM skus WHERE teamid = ? ORDER BY listorder";
		$pdostatementS = $dbh->prepare($strSQL);
		$bError = ! $pdostatementS->execute(array($teamid));
		$skuResults = $pdostatementS->fetchAll();
		$rowCountS = count( $skuResults);
		
		// Display skus for this team
		if ($rowCountS > 0) { 
			$countRowsS = 0; ?>		
<select name="skuid" onchange="onSkuChanged(this.selectedIndex);">
<option value="<?php echo Sku::SkuID_Undefined?>" <?php if ((empty($paymentResults["skuid"])) || ($paymentResults["skuid"] == Sku::SkuID_Undefined) ) echo ' selected'?>>Select SKU purchased...</option>
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
</tr>
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
function onSkuChanged( idx){
	price = skuAmount[idx-1];
	dojo.byId('amount').value = price;
	// This is a hack to get the dojo widget to pretty up the auto-entered value
	dojo.byId('amount').focus();
	// Now put focus back to the select list
	document.newpaymentform.skuid.focus();
	
}
</script>
<tr><td class="bold">Amount ($US)</td><td><input type="text" id="amount" name="amount" value="0.00" dojoType="dijit.form.CurrencyTextBox" required="true" constraints="{fractional:true}" currency="USD" invalidMessage="Invalid amount.  Include dollars and cents." /></td></tr>
<tr><td class="bold">Fee ($US)</td><td><input type="text" name="fee" value="0.00" dojoType="dijit.form.CurrencyTextBox" required="false" constraints="{max:0,fractional:true}" currency="USD" invalidMessage="Invalid amount.  Must be a negative amount. Include dollars and cents." /></td></tr>
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
<tr><td class="bold">Method</td><td>
<select name="paymentmethod">
<?php 
		while ($countRowsPM < $rowCountPM) {
			echo "<option value=\"";
			echo $paymenttypeResults[$countRowsPM]["id"];
			echo '">';
			echo $paymenttypeResults[$countRowsPM]["name"];
			echo "</option>\n";
			$countRowsPM ++;
		} ?>
</select>				
</td></tr>
<?php 
	} ?>
<tr><td class="bold">Paid?</td><td><input type="checkbox" name="ispaid"/></td></tr>
<tr><td class="bold">Refunded?</td><td><input type="checkbox" name="isrefunded"/></td></tr>
</table>
</div>
<input type="submit" value="Create new order item" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<?php 
	if ($userid == User::UserID_Undefined) {
		$objid = $teamid;
		$whomode = "team";
	} else {
		$objid = $userid;
		$whomode = "user";
	}?>
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'payment-history.php?<?php echo returnRequiredParams($session) ."&teamid=" . $teamid . "&id=" . $objid . "&whomode=" . $whomode?>'"/>
</form>
<?php
	// Conditionally show user's payment history on this page
	if ( $userid >= UserID_Base ) { 
		$pageMode = "expand" ;
		include('include-payment-history.php');
	}
}
// Start footer section
include('footer.php'); ?>
</body>
</html>