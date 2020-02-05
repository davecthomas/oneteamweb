<?php  
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Edit Payment Type"; 
include('header.php');
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
}

if (isset($_GET["id"])) {
	$paymenttypeid = $_GET["id"];
} else {
	$paymenttypeid = NotFound;
}

$strSQL = "SELECT * FROM paymentmethods WHERE teamid = ? AND id = ?;";
$pdostatement = $dbh->prepare($strSQL);
$pdostatement->execute(array($teamid, $paymenttypeid));

$paymenttypeResults = $pdostatement->fetchAll();

if (count($paymenttypeResults) > 0) { ?>
<h3>Modify a payment method for <?php echo getTeamName2($teamid, $dbh)?></h3>	
<div class="indented-group-noborder">
<form action="/1team/edit-payment-type.php" method="post">
<input type="hidden" name="id" value="<?php echo $paymenttypeid ?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<?php buildRequiredPostFields($session) ?>
<table class="noborders">
<td class="strong">Payment type name</td><td><input type="text" name="name" size="60" maxlength="80" value="<?php echo $paymenttypeResults[0]["name"]?>"></td></tr>
<td class="strong">Payment type list order</td><td><?php if (is_null($paymenttypeResults[0]["listorder"]) ) echo "No order set"; else echo $paymenttypeResults[0]["listorder"]?></td></tr>
</table>
</div>
<input type="submit" value="Modify paymenttype" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/></form>
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'manage-payment-types-form.php?<?php echo returnRequiredParams($session) . "&teamid=" . $teamid?>'"/>
<?php 
} 
// Start footer section
include('footer.php'); ?>
</body>
</html>
