<?php  
// Only app admins can execute this script. Header.php enforces this.
$isappadminrequired = true;
$title= " New Payment for Team" ;
include('header.php'); ?>
<script type="text/javascript"> 
dojo.require("dijit.form.DateTextBox");
dojo.require("dijit.form.CurrencyTextBox");
</script>

<?php
$dbh = getDBH($session);
$strSQL = "SELECT * FROM teams, teamaccountinfo WHERE isbillable = true and status <> " . TeamAccountStatus_Inactive . " and teams.teamaccountinfo = teamaccountinfo.id ORDER BY names;";
$pdostatement = $dbh->prepare($strSQL);
$bError = ! $pdostatement->execute();
$teamResults = $pdostatement->fetchAll();
$countRows = 0;	
$numRows = count($teamResults);
?>
<div class="indented-group-noborder">
<form name="newteampaymentform" action="/1team/new-team-payment.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<?php
	} ?>
<table class="noborders">
<tr>
<td class="bold"><?php echo $teamterms["termmember"]?></td>
<?php
	echo("<td><select name=\"id\"><option value=\"" . User::UserID_Undefined . "\" selected>Select "  . $teamterms["termuser"] . "&hellip;</option>\n");
	echo("<option value=\"" . User::UserID_Guest . "\">Non-"  . $teamterms["termmember"] . " (" . User::Username_Guest . ")</option>\n");
	while ($countRows < $numRows) {
		echo "<option value=\"";
		echo $teamResults[$countRows]["id"];
		echo "\"";
		if ( $teamid == $teamResults[$countRows]["id"] ) {
			echo("selected");
		}
		echo ">";
		echo $teamResults[$countRows]["name"];
		echo "</option>\n";
		$countRows ++;
	} ?>
</select></td></tr>
<tr><td class="bold">Date</td><td><input type="text" name="paymentdate" id="paymentdate" value="<?php echo date("Y-m-d")?>" dojoType="dijit.form.DateTextBox" required="true" /></td></tr>
<tr><td class="bold">Plan</td>
<td>
</td>
</tr>
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
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'payment-history.php?<?php echo returnRequiredParams($session) ."&teamid=" . $teamid . "&id=" . $objid . "&whomode=" . $whomode?>'"/>
</form>
<?php
	// Conditionally show payment history on this page
	if ( $teamid >= UserID_Base ) {
		$pageMode = "expand" ;
		include('include-team-payment-history.php');
	}
}
// Start footer section
include('footer.php'); ?>
</body>
</html>