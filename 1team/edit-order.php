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

if ( isset($_REQUEST["uid"])) {
	$userid = (int)(getCleanInput($_REQUEST["uid"]));
} else {
	$bError = true;
	$err = "u";
}
if (isset($_REQUEST["orderdate"])) {
	$orderdate = $_REQUEST["orderdate"];
} else {
	$bError = true;
	$err = "od";
}
if (isset($_REQUEST["id"])) {
	$orderid = $_REQUEST["id"];
} else {
	$orderid = Order::OrderID_Undefined;
	$bError = true;
	$err = "o";
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
echo "numorderitems".$numorderitems."<BR>";
	// Make sure we got enough items in the order array
	if ((is_array($orderarray)) && (is_numeric($numorderitems))){
print_r($orderarray);
		if ((count($orderarray)/Order::OrderItemArraySize_Edit) != $numorderitems){
			$bError = true;
			$err = "la";
		}
	} else {
		$bError = true;
		$err = "na";
	}
} else {
	$bError = true;
	$err = "no";
}


// due date is optional
if (isset($_REQUEST["duedate"])) {
	$duedate = $_REQUEST["duedate"];
	if (empty($duedate)) $duedate = NULL;
} else {
	$duedate = NULL;
}

if (isset($_REQUEST["paymentmethod"])) {
	$paymentmethod = $_REQUEST["paymentmethod"];
} else {
	$bError = true;
	$err = "pm";
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
$discount = 0.00;



if ( !$bError) {
	$dbconn = getConnectionFromSession($session);

	if (is_null($duedate)) {
		$strSQL = "UPDATE orders set orderdate = ?, duedate = NULL, discount = ?, ispaid = ?, paymentmethod = ? where teamid = ? and userid = ? and id = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($orderdate, $discount, $ispaidsql, $paymentmethod, $teamid, $userid, $orderid )));
	} else {
		$strSQL = "UPDATE orders set orderdate = ?, duedate = ?, discount = ?, ispaid = ?, paymentmethod = ? where teamid = ? and userid = ? and id = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($orderdate, $duedate, $discount, $ispaidsql, $paymentmethod, $teamid, $userid, $orderid )));
	}
	if ($bError) $err = "oi";

	if ((!$bError) && ($orderid != Order::OrderID_Undefined)){
		// Now update the individual orderitems from the orderarray
		for ($loopOrderItems = 0; $loopOrderItems < $numorderitems; $loopOrderItems++){
			$skuid = $orderarray[$loopOrderItems*Order::OrderItemArraySize_Edit+Order::OrderItemArrayIndex_SKU];
			$amount = $orderarray[$loopOrderItems*Order::OrderItemArraySize_Edit+Order::OrderItemArrayIndex_Amount];
			$fee = $orderarray[$loopOrderItems*Order::OrderItemArraySize_Edit+Order::OrderItemArrayIndex_Fee];
			$orderitemid = $orderarray[$loopOrderItems*Order::OrderItemArraySize_Edit+Order::OrderItemArrayIndex_Orderitemid];;

			// Store the orderitem
			$strSQL = "UPDATE orderitems SET paymentdate = ?, paymentmethod = ?, ispaid = ? where userid = ? and teamid = ? and id = ?;";
			executeQuery($dbconn, $strSQL, $bError, array( $orderdate, $paymentmethod, $ispaidsql, $userid, $teamid, $orderitemid )));
			if ($bError) $err = "oii";
		}

		// Success
		if (!$bError) redirect( "manage-orders-form.php?".returnRequiredParams($session). "&teamid=" . $teamid . "&done=1");
		else	redirect( "manage-orders-form.php?".returnRequiredParams($session)."&teamid=" . $teamid . "&err=". $err);
	}
}
if ($bError) {
	// Go back to reconciler
	if (isset($_GET["epayid"]))
		redirect("epayment-reconcile-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=" . $err);
	// Go to payment history
	else
		redirect( "manage-orders-form.php?".returnRequiredParams($session)."&teamid=" . $teamid . "&err=". $err);
}?>
