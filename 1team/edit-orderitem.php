<?php
include('utils.php');
// Assure we have the input we need, else send them to default.php
if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid = getUserID()) == RC_RequiredInputMissing)){
	redirect("default.php?rc=" . RC_RequiredInputMissing);
}
// Get $session array initialized
$session = startSession($sessionkey, $userid);
if (! isValidSession($session )){
	redirect("default.php?rc=" . $session);
}
// Only admins can execute this script
redirectToLoginIfNotAdmin( $session);

$bError = false;

// teamid depends on who is calling
if (isUser($session, Role_TeamAdmin)){
	if (isset($session["teamid"])){
		$teamid = $session["teamid"];
	}
} else {
	if (isset($_POST["teamid"])){
		$teamid = $_POST["teamid"];
	} else {
		$bError = true;
		$err = "tid";
	}
}

if (isset($_POST["paymentid"])) {
	$paymentid = $_POST["paymentid"];
} else {
	$bError = true;
	$err = "pmid";
}

if (isset($_POST["amount"])) {
	$amount = $_POST["amount"];
} else {
	$bError = true;
	$err = "pay";
}

if (isset($_POST["uid"])) {
	$uid = $_POST["uid"];
} else {
	$bError = true;
	$err = "u";
}
if (isset($_POST["paymentdate"])) {
	$paymentdate = $_POST["paymentdate"];
} else {
	$paymentdate = date("m-d-Y");
}

if (isset($_POST["paymentmethod"])) {
	$paymentmethod = $_POST["paymentmethod"];
} else {
	$bError = true;
	$err = "pm";
}

if (isset($_POST["numeventsremaining"])) {
	$numeventsremaining = $_POST["numeventsremaining"];
	if (strcasecmp($numeventsremaining,'Unlimited')==0) $numeventsremaining = Sku::NumEventsUnlimited;
} else {
	$bError = true;
	$err = "ner";
}

if (isset($_POST["programid"])) {
	$programid = $_POST["programid"];
} else {
	$bError = true;
	$err = "pid";
}

// SKU is not required
if (isset($_POST["skuid"])) {
	$skuid = $_POST["skuid"];
} else {
	$bError = true;
	$err = "sk";
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

if (!$bError) {
	// Fee is not required
	if (isset($_REQUEST["fee"])){
		$fee = $_REQUEST["fee"];
		if (empty($fee) || (!is_numeric($fee))) $fee = 0;
	} else $fee = 0;



	$strSQL = "UPDATE orderitems SET amount = ?, paymentdate = ?, paymentmethod = ?, programid = ?, numeventsremaining = ?, skuid = ?, fee = ?, ispaid = ?, isrefunded = ? WHERE id = ? AND teamid = ?;";
	$dbconn = getConnection();
	executeQuery($dbconn, $strSQL, $bError, array($amount, $paymentdate, $paymentmethod, $programid, $numeventsremaining, $skuid, $fee, $ispaidsql, $isrefundedsql, $paymentid, $teamid));
	if (!$bError)
		redirect("payment-history.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&id=" . $uid . "&done=1");
	else
		$err = "s";
}
if ($bError) {
	redirect("payment-history.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&id=" . $uid . "&err=" . $err);
} ?>
