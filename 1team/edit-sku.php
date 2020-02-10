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
	}
}

if (isset($_POST["id"])) {
	$skuid = $_POST["id"];
} else {
	$bError = true;
}

if (isset($_POST["name"])) {
	$skuname = $_POST["name"];
} else {
	$bError = true;
}
if (isset($_POST["price"])) {
	$price = $_POST["price"];
} else {
	$bError = true;
}
if (isset($_POST["description"])) {
	$description = $_POST["description"];
} else {
	$bError = true;
}
if (isset($_POST["programid"])) {
	$programid = $_POST["programid"];
} else {
	$bError = true;
}
if (isset($_POST["programid"])) {
	$programid = $_POST["programid"];
} else {
	$bError = true;
}
// This is a checkbox. If it's set, it's on
if (isset($_POST["unlimitednumevents"])) {
	$numevents = Sku::NumEventsUnlimited;
}
// This may not be set since it may not be visible (in unlimited case)
else if (isset($_POST["numevents"])) {
	$numevents = $_POST["numevents"];
	if (!is_numeric($numevents)){
		$bError = true;
	}
} else {
	$numevents = Sku::NumEventsUndefined;
}
if (isset($_POST["expirationnum"])) {
	$expirationnum = $_POST["expirationnum"];
	if (!is_numeric($expirationnum)){
		$bError = true;
	}
} else {
	$bError = true;
}if (isset($_POST["expirationunits"])) {
	$expirationunits = $_POST["expirationunits"];
} else {
	$bError = true;
}

// Get the user input convered into SQL
$expires = createExpirationSQL($expirationnum, $expirationunits);

if (!$bError) {


	$strSQL = "UPDATE skus SET name = ?, price = ?, description = ?, programid = ?, numevents = ?, expires = ? WHERE id = ? AND teamid = ?;";
	$dbconn = getConnection();
	executeQuery($dbconn, $strSQL, $bError, array($skuname, $price, $description, $programid, $numevents, $expires, $skuid, $teamid));

	redirect("manage-skus-form.php?" .returnRequiredParams($session) . "&teamid=" . $teamid . "&done=1");
} else {
	redirect("manage-skus-form.php?" .returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1");
}

function createExpirationSQL($expirationnum, $expirationunits){
	switch ($expirationunits){
		case skuExpirationUnits_Days:
			$retval = $expirationnum . " " . skuSQL_Days;
			break;
		case skuExpirationUnits_Weeks:
			$retval = $expirationnum . " " . skuSQL_Weeks;
			break;
		case skuExpirationUnits_Months:
			$retval = $expirationnum . " " . skuSQL_Months;
			break;
		case skuExpirationUnits_Years:
			$retval = $expirationnum . " " . skuSQL_Years;
			break;
		default:
			$retval = "1 day";
			break;

	}
	return $retval;
}
