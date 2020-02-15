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
	} else {
		$bError = true;
		$err = "t";
	}
} else {
	if (isset($_REQUEST["teamid"])){
		$teamid = $_REQUEST["teamid"];
	} else {
		$bError = true;
		$err = "t";
	}
}

if ( isset($_REQUEST["uid"])) {
	$userid = (int)(getCleanInput($_REQUEST["uid"]));
} else {
	$bError = true;
	$err = "i";
}
if (isset($_REQUEST["orderdate"])) {
	$orderdate = $_REQUEST["orderdate"];
} else {
	$bError = true;
	$err = "od";
}
if (isset($_REQUEST["order"])) {
	$order = $_REQUEST["order"];
	$orderarray = explode( ",",$order);
} else {
	$bError = true;
	$err = "or";
}

if (isset($_REQUEST["numorderitems"])) {
	$numorderitems = $_REQUEST["numorderitems"];
	// Make sure we got enough items in the order array
	if ((is_array($orderarray)) && (is_numeric($numorderitems))){
		if ((count($orderarray)/OrderItemArraySize) != $numorderitems){
			$bError = true;
			$err = "la";
		}
	} else {
		$bError = true;
		$err = "na";
	}
// If no numorderitems, there's only one
} else {
	$numorderitems = 1;
}


// due date is optional
if (isset($_REQUEST["duedate"])) {
	$duedate = $_REQUEST["duedate"];
} else {
	$duedate = NULL;
}

if (isset($_REQUEST["paymentmethod"])) {
	$paymentmethod = $_REQUEST["paymentmethod"];
} else {
	$bError = true;
	$err = "pm";
}

if (isset($_REQUEST["ispaid"])) {
	$ispaidsql = 'TRUE';
	$ispaid = true;
} else {
	// ePayments are by default considered paid, even though there is no "ispaid" in the request
	if (isset($_GET["epayid"])) {
		$ispaidsql = 'TRUE';
		$ispaid = true;
	} else {
		$ispaidsql = 'FALSE';
		$ispaid = false;
	}
}

if (isset($_POST["isrefunded"])) {
	$isrefundedsql = 'TRUE';
	$isrefunded = true;
} else {
	$isrefundedsql = 'FALSE';
	$isrefunded = false;
}
$discount = 0.00;



if ( !$bError) {
	$dbconn = getConnectionFromSession($session);

	if (is_null($duedate)) {
		$strSQL = "INSERT INTO orders VALUES (DEFAULT, ?, ?, ?, NULL, ?, ?, ?) RETURNING id;";
		$orderid = executeQueryFetchColumn($dbconn, $strSQL, $bError, array($userid, $teamid, $orderdate, $discount, $ispaidsql, $paymentmethod));
	} else {
		$strSQL = "INSERT INTO orders VALUES (DEFAULT, ?, ?, ?, ?, ?, ?, ?) RETURNING id;";
		$orderid = executeQueryFetchColumn($dbconn, $strSQL, $bError, array($userid, $teamid, $orderdate, $duedate, $discount, $ispaidsql, $paymentmethod));
	}
	//print_r(array($userid, $teamid, $orderdate, $duedate, $discount, $ispaidsql, $paymentmethod));

	if (!$bError){
		// Now create the individual orderitems from the orderarray
		for ($loopOrderItems = 0; $loopOrderItems < $numorderitems; $loopOrderItems++){
			$skuid = $orderarray[$loopOrderItems*OrderItemArraySize+OrderItemArrayIndex_SKU];
			$amount = $orderarray[$loopOrderItems*OrderItemArraySize+OrderItemArrayIndex_Amount];
			$fee = $orderarray[$loopOrderItems*OrderItemArraySize+OrderItemArrayIndex_Fee];

			// get the programid and numclassesfrom the sku, for each orderitem in the order
			$strSQL = "SELECT programid, numevents from skus where id = ? and teamid = ?";
			$resultsSku = executeQuery($dbconn, $strSQL, $bError, array($skuid, $teamid));
			if ($bError) $err = "pis";

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

			if (!$bError){
				// Store the orderitem (FALSE is for isrefunded field)
				$strSQL = "insert into orderitems VALUES (DEFAULT, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, FALSE, ?);";
				executeQuery($dbconn, $strSQL, $bError, array($programid, $orderdate, $userid, $teamid, $paymentmethod, $amount, $skuid, $numeventsremaining, $fee, $ispaidsql, $orderid ));
				if ($bError) $err = $orderid;
			}

		}

		// Epayments are only passed via GET. This triggers a reconcile and different redirect
		if ((isset($_GET["epayid"])) && (!$bError)){
			$epayid = $_GET["epayid"];
			$strSQL = "UPDATE epayments set reconciled = TRUE WHERE id = ? and teamid = ?;";
			executeQuery($dbconn, $strSQL, $bError, array($epayid, $teamid));
			if ($bError) $err = "epu";
			// Success
			if (!$bError){
				redirect( "payment-history.php?". returnRequiredParams($session) . "&teamid=" . $teamid . "&id=" . $userid . "&done=1");
			// On error, go back to reconcile form
			} else {
				redirect("epayment-reconcile-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=". $err);
			}
		// Success
		} else {
			if (!$bError) redirect( "manage-orders-form.php?".returnRequiredParams($session). "&teamid=" . $teamid . "&done=1");
			else	redirect( "manage-orders-form.php?".returnRequiredParams($session)."&teamid=" . $teamid . "&err=". $err);
		}
	}
}
if ($bError) {
	// Go back to reconciler
	if (isset($_GET["epayid"]))
		redirect("epayment-reconcile-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=" . $err);
	// Go to payment history
	else
		redirect( "manage-orders-form.php?".returnRequiredParams($session)."&teamid=" . $teamid . "&err=". $err);
}
?>
