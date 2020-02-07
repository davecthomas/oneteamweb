<?php 
error_reporting(E_ALL ^ E_NOTICE); 

if ((isset($_REQUEST["teamid"])) && (is_numeric($_REQUEST["teamid"])))  {
	$txid = $_REQUEST["txn_id"];
	$teamid = $_REQUEST["teamid"];
	
	// Support test mode. This also allows us to add a transaction manually by calling this script
	if (isset($_GET["txn_id"])){
		$testmode = true;
		testecho($testmode, "Test Mode");
	} else {
		$testmode = false;
	}
	
	require_once 'CallerService.php';
	
	$transactionID=urlencode($txid);
	
	/* Construct the request string that will be sent to PayPal.
	   The variable $nvpstr contains all the variables and is a
	   name value pair string with & as a delimiter */
	$nvpStr="&TRANSACTIONID=" . $transactionID;
	
	/* Make the API call to PayPal, using API signature.
	   The API response is stored in an associative array called $resArray */
	$resArray=hash_call("gettransactionDetails",$nvpStr);
	
	$ack = strtoupper($resArray["ACK"]);
	
	if ($ack=="SUCCESS"){
		if ($testmode) print_r($resArray);
		include ('..\utils.php');
		$dbh = new PDO('odbc:DRIVER={'.dbdriver.'};UID=' . dbusername. ';SERVER=' . getDBServer() . ';Port='.dbport.';Database=' . dbname . ';PWD=' . getPass1() . ';');
		// First, make sure this transaction doesn't already exist
		$strSQL = "SELECT txid from epayments WHERE txid = ?";
		$pdostatement = $dbh->prepare($strSQL);
		$pdostatement->execute(array($txid));
		$epaymentResults = $pdostatement->fetchAll();

		if (count($epaymentResults) == 0) {	
			$paymentdate = substr($resArray['ORDERTIME'], 0, 10);
			if (isset($resArray['AMT'])){
				$amount = $resArray['AMT'];   	   
				if ((isset($resArray['FEEAMT'])) && ($resArray['FEEAMT'] > 0)) {
					$fee = $resArray['FEEAMT'];
				} else {
					$fee = 0;
				}
			}
			$payeremail = $resArray['EMAIL'];
			$item = $resArray['L_NAME0'];
			if (isset($resArray['L_NUMBER0'])) {
				$skuname = $resArray['L_NUMBER0'];
			} else {
				$skuname = "";
			}
			$strSQL = "INSERT INTO epayments VALUES (DEFAULT, ?, ?, FALSE, ?, ?, ?, ?, ?, ? , ? );";
			$pdostatement = $dbh->prepare($strSQL);
			if (! $pdostatement->execute(array(ePaymentSourcePayPal, $txid, $teamid, $amount, $paymentdate, $item, $payeremail, $skuname, $fee ))) 
				testecho($testmode, "INSERT fail");
		} else {
			testecho($testmode, "Duplicate txn");
		}
		
		// The result of this in an un-reconciled epayment stored in the table. 
		// The idea is that the team admin will either accept these or not in the epayments
	}
}

function testecho($testmode, $str){
	if ($testmode) echo $str . "<br>\n";
}
?>