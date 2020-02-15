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
	if (isset($_REQUEST["teamid"])){
		$teamid = $_REQUEST["teamid"];
	} else {
		$bError = true;
		$err = "tid";
	}
}

if (isset($_REQUEST["id"])) {
	$epaymentid = $_REQUEST["id"];
} else {
	$bError = true;
	$err = "pid";
}

// uid is the user that submitted the epayment. It may not be known yet, so it is not required
if (isset($_REQUEST["uid"])) {
	$uid = $_REQUEST["uid"];
} else {
	$uid = User::UserID_Undefined;
}



// Verify this epayment exists
if (!$bError) {
	$strSQL = "SELECT * from epayments WHERE id = ? AND teamid = ?";
	$dbconn = getConnectionFromSession($session);
	$resultsEpayments = executeQuery($dbconn, $strSQL, $bError, array($epaymentid, $teamid));
	if ($bError){
		$err = "epn";
	} else {
		if (count($resultsEpayments) == 1){
			$item = $resultsEpayments[0]["item"];
		} else {
			$bError = true;
			$err = "pnf";
		}
	}
}
// SKU is not required
if ((isset($_REQUEST["skuid"])) && (!$bError)) {
	$skuid = (int)$_REQUEST["skuid"];
	$strSQL = "select name, description from skus where id = ? and teamid = ?";
	$skuResults = executeQuery($dbconn, $strSQL, $bError, array($skuid, $teamid));
	if ($bError){
		$err = $skuid;
	} else {
		$countSkus = count($skuResults);
		if ($countSkus == 1) {
			$skuname = $skuResults[0]["name"];
			// replace the item with the sku description
			$item = $skuResults[0]["description"];
		} else {
			$skuid = Sku::SkuID_Undefined;
			$skuname = "";
		}
	}

} else {
	$skuid = Sku::SkuID_Undefined;
	$skuname = "";
}

if (!$bError) {
	$strSQL = "UPDATE epayments SET skuname = ?, userid = ?, item = ? WHERE id = ? AND teamid = ?;";
	executeQuery($dbconn, $strSQL, $bError, array($skuname, $uid, $item, $epaymentid, $teamid));
	$err = "s";
}
if (!$bError){
//	print_r( array($skuname, $uid, $item, $epaymentid, $teamid));
	redirect("epayment-reconcile-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&done=1");
} else {
	redirect("epayment-reconcile-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1&rc=" . $err);
} ?>
