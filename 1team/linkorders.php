<?php  
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title= " Make an Order" ;
include('header.php'); 

  
$strSQL = "SELECT * from orderitems;";
$pdostatement = $dbh->prepare($strSQL);
$bError = ! $pdostatement->execute();
if (!$bError) {
	$paymentResults = $pdostatement->fetchAll();
	$countRows = 0;	
	$numRows = count($paymentResults); 
	while ($countRows < $numRows){
		$err = "";
		// See if the same user purchased something on this date. If so, add this to the order
		$strSQL = "SELECT * FROM order_orderitems WHERE orderitemid = ?;";
		$pdostatement = $dbh->prepare($strSQL);
		$bError = ! $pdostatement->execute(array($paymentResults[$countRows]["id"]));
		if (!$bError) {
			$order_orderitemResults = $pdostatement->fetchAll();
			$numOrder_orderitems = count($order_orderitemResults);
			
			// If we have an order, get the id and add this payment to the order_sku table 
			// Don't group guest users orderitems
			if ($numOrder_orderitems == 1) {
				$strSQL = "UPDATE orderitems SET orderid = ? WHERE id = ?;";
				$pdostatement = $dbh->prepare($strSQL);
				$bError = ! $pdostatement->execute(array($order_orderitemResults[0]["orderid"], $paymentResults[$countRows]["id"]));
			} else $err = "2";
		} else $err = "1";
		echo $countRows;
		if ($bError) echo " Error " . $err;
		echo "<BR>";
		$countRows ++;
	}	
} else {
	echo "<br>Error 0";
}
// Start footer section
include('footer.php'); ?>
</body>
</html>