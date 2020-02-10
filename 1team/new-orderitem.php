<?php  
include ('utils.php');

// Assure we have the input we need, else send them to default.php
if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid = getUserID()) == RC_RequiredInputMissing)){
	redirect("default.php?rc=" . RC_RequiredInputMissing);
}
// Get $session array initialized
$session = startSession($sessionkey, $userid);
if (! isValidSession($session )){
	redirect("default.php?rc=" . $session);
}
redirectToLoginIfNotAdmin( $session);

//This accepts post or get input since it is called both ways
$bError = false;
$err = "non";
// teamid depends on who is calling 
if (isUser($session, Role_TeamAdmin)){
	if (isset($session["teamid"])){
		$teamid = $session["teamid"];
	} 
} else {
	if (isset($_REQUEST["teamid"])){
		$teamid = $_REQUEST["teamid"];
	} else {
		$bError = true;
		$err = "t";
	}
}

if ( isset($_REQUEST["id"])) {
	$userid = (int)(getCleanInput($_REQUEST["id"]));
} else {
	$bError = true;
	$err = "i";
} 	
if (isset($_REQUEST["paymentdate"])) {
	$paymentdate = $_REQUEST["paymentdate"]; 
} else {
	$bError = true;
	$err = "pd";
}


if (isset($_REQUEST["paymentmethod"])) {
	$paymentmethod = $_REQUEST["paymentmethod"]; 
} else {
	$bError = true;
	$err = "pm";
}

if (isset($_REQUEST["amount"])) {
	$amount = $_REQUEST["amount"]; 
} else {
	$bError = true;
	$err = "p";
}

// SKU is required, since it drives numeventsremaining
if (isset($_REQUEST["skuid"])) {
	$skuid = $_REQUEST["skuid"]; 
} else {
	$bError = true;
	$err = "sid";
}

if (isset($_POST["ispaid"])) {
	$ispaidsql = 'TRUE';
	$ispaid = true;
} else {
	$ispaidsql = 'FALSE';
	$ispaid = false;
}

if (isset($_POST["isrefunded"])) {
	$isrefundedsql = 'TRUE';
	$isrefunded = true;
} else {
	$isrefundedsql = 'FALSE';
	$isrefunded = false;
}

  

// get the programid and numclassesfrom the sku
$strSQL = "SELECT programid, numevents from skus where id = ? and teamid = ?";
$pdostatement = $dbh->prepare($strSQL);
$pdostatement->execute(array($skuid, $teamid));
$resultsSku = $pdostatement->fetchAll();
$numSkus = count($resultsSku );
if ($numSkus == 1) {
	$programid = $resultsSku[0]["programid"];
	// Initialize the number of events remaining for this payment is the number of events defined for the sku
	// This gets deducted by one each time they log attendance
	$numeventsremaining = $resultsSku[0]["numevents"];
} else {
	$bError = true;
	$err = "sk";
}

if ( !$bError) {
	// Fee is not required 
	if (isset($_REQUEST["fee"])){
		$fee = $_REQUEST["fee"];
	} else $fee = 0;
	
	$strSQL = "INSERT INTO orderitems VALUES (DEFAULT, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
	$pdostatement = $dbh->prepare($strSQL);
	$bError = (!$pdostatement->execute(array($programid, $paymentdate, $userid, $teamid, $paymentmethod, $amount, $skuid, $numeventsremaining, $fee, $ispaidsql, $isrefundedsql )));
	
	if (!$bError){
		
		// Epayments are only passed via GET. This triggers a reconcile and different redirect
		if (isset($_GET["epayid"])){
			$epayid = $_GET["epayid"];
			$strSQL = "UPDATE epayments set reconciled = TRUE WHERE id = ? and teamid = ?;";
			$pdostatement = $dbh->prepare($strSQL);
			$bError = (!$pdostatement->execute(array($epayid, $teamid)));
			// Success
			if (!$bError){
				redirect( "payment-history.php?".returnRequiredParams($session). "&teamid=" . $teamid . "&id=" . $userid . "&done=1");
			// On error, go back to reconcile form
			} else {
				redirect("epayment-reconcile-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=eps");
			}
		// Success 			
		} else {
			redirect( "payment-history.php?".returnRequiredParams($session)."&teamid=" . $teamid . "&id=" . $userid . "&done=1");
		}
	} else $err = "ps";
}
if ($bError) {
	// Go back to reconciler
	if (isset($_GET["epayid"]))
		redirect("epayment-reconcile-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=eps");
	// Go to payment history
	else
		redirect( "payment-history.php?".returnRequiredParams($session)."&teamid=" . $teamid . "&id=" . $userid . "&err=". $err);
	
}
?>