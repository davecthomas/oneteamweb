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
	include ('..\utils.php');
	require_once 'CallerService.php';
	$dbconn = getConnection();
	$bError = false;
	$team_payment_provider = get_team_payment_provider($dbconn, $bError, $teamid);

	$transactionID=urlencode($txid);

	/* Construct the request string that will be sent to PayPal.
	   The variable $nvpstr contains all the variables and is a
	   name value pair string with & as a delimiter */
	$nvpStr="&TRANSACTIONID=" . $transactionID;

	/* Make the API call to PayPal, using API signature.
	   The API response is stored in an associative array called $resArray */
	$resArray=hash_call("gettransactionDetails",$nvpStr, $team_payment_provider);

	$ack = strtoupper($resArray["ACK"]);

	if ($ack=="SUCCESS"){
		if ($testmode) print_r($resArray);
		// First, make sure this transaction doesn't already exist
		$strSQL = "SELECT txid from epayments WHERE txid = ?";
		$epaymentResults = executeQuery($strSQL, $dbconn, $bError, array($session["userid"], $session["sessionkey"]));array($txid));

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
			executeQuery($strSQL, $dbconn, $bError, array(ePaymentSourcePayPal, $txid, $teamid, $amount, $paymentdate, $item, $payeremail, $skuname, $fee ));
			if ($bError) {
				testecho($testmode, "INSERT fail");
		} else {
			testecho($testmode, "Duplicate txn");
		}

		// The result of this in an un-reconciled epayment stored in the table.
		// The idea is that the team admin will either accept these or not in the epayments
	}
}

function get_team_payment_provider($dbconn, &$bError, $teamid){
	$strSQL = "SELECT payment_provider, api_username, api_password, api_signature from teams WHERE teamid = ?";
	$team_payment_provider = executeQuery($strSQL, $dbconn, $bError, array($session["userid"], $session["sessionkey"]));array($teamid));

	if ((! $bError) and (count($team_payment_provider)>0)) $team_payment_provider[0];
	else return null;

}

function testecho($testmode, $str){
	if ($testmode) echo $str . "<br>\n";
}
?>
