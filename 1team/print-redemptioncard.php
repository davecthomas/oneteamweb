<?php
//ob_start(); 	// This caches non-header output, allowing us to redirect after header.php
$bError = false;
$err = "";
$title = "Redemption Card";
include_once('header-minimal.php');
require("php-barcode/php-barcode.php");

$cardTypes = RedemptionCard::getRedemptionCardTypes();
?>
<body>
<div id="userbanner">
Signed in as <?php echo roleToStr($session["roleid"],$teamterms)?>&nbsp;<?php echo $session["fullname"]?>. Session time remaining:&nbsp;<?php echo getSessionTimeRemaining($session)?> <a href="/1team/logout.php<?php buildRequiredParams($session)?>">Sign out</a>
</div>
<div id="wrapper">
<div style="background-color:#FFFFFF">
<?php
// Assure we have the input we need, else send them to default.php
if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid = getUserID()) == RC_RequiredInputMissing)){
	redirect("default.php?rc=" . RC_RequiredInputMissing);
}
// Get $session array initialized
$session = startSession($sessionkey, $userid);
if (! isValidSession($session )){
	redirect("default.php?rc=" . $session);
	$bError = true;
}
// Only admins can execute this script
redirectToLoginIfNotAdminOrCoach( $session);

// teamid depends on who is calling 
if ( !isUser($session, Role_ApplicationAdmin)){
	if ( !isset($session["teamid"])){
		$bError = true;
		$err = "at.";
	} else {
		$teamid = $session["teamid"];
	}
} else {
	if ( isset($_REQUEST["teamid"])){
		$teamid = $_REQUEST["teamid"];
		if (!is_numeric($teamid)){
			$bError = true;
			$err = "t";
		}
	} else {
		$bError = true;
		$err = "t";
	}
}

if ( isset($_REQUEST["id"])){
	$id = $_REQUEST["id"];
} else {
	$bError = true;
	$err = "i";
}

if (strlen($session["teamimageurl"]) > 0) {
	$teamlogo = $session["teamimageurl"];
} else {
	$teamlogo = "/1team/img/1teamweb-logo-200.png";
}

$returnErrString = "";
if (!$bError) {
	
	$returnString = "";
	// Get team terms gracefully handles the case where app admin has no team
	$teamterms = getTeamTerms(getTeamID($session), getDBH($session));
	$rolename = roleToStr($session["roleid"], $teamterms);
	$teaminfo = getTeamInfo( $teamid);
	if ((isset($session)) && (isset($session["teamimageurl"]))) {
		if (strlen($session["teamimageurl"]) > 0) {
			$logoimg = $session["teamimageurl"];
		} else {
			$logoimg = "/1team/img/1teamweb-logo-200.png";
		}
	} else {
		$logoimg = "/1team/img/1teamweb-logo-200.png";
	}
	$teamname = $teaminfo["teamname"];

	// Now get the sum of the face value since this should be very interesting for guest passes and other give-aways
	$strSQL = "SELECT redemptioncards.*, users.firstname, users.lastname FROM redemptioncards, users WHERE redemptioncards.teamid = ? AND redemptioncards.id = ? AND users.id = redemptioncards.userid;";
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array($teamid, $id));
	$redemptioncardResults = $pdostatement->fetch();

	if (isset($redemptioncardResults["id"])){
		$sku = new Sku($session, $redemptioncardResults["skuid"]);
		$cardforname = $redemptioncardResults["firstname"] . " ". $redemptioncardResults["lastname"];
		// Print out the card ?>
<table class="idcard">
<tr><td align="center" class="idcard_cell"><div id="idcard">
<span class="smallbold"><?php
		$type = $redemptioncardResults["type"];
		$cardtypeText = $cardTypes[$type] ;
		$numevents = $redemptioncardResults["numeventsremaining"];

		echo $teamname."&nbsp;".$cardtypeText;?>
</span><br/>
<span class="small"><?php echo $cardforname;
		if ($numevents == Sku::NumEventsUnlimited) $numeventsstr ="Unlimited";
		else if ($numevents == Sku::NumEventsUndefined) $numeventsstr = "";
		else $numeventsstr = $numevents;
		echo ": ". $numeventsstr . " ". $sku->getName();?>
</span><br/>
<?php	$barcode = $redemptioncardResults["code"];?>
<img class="idcard_redemptioncardbarcode" src="./php-barcode/barcode.php?code=<?php echo $barcode?>&encoding=39" alt="barcode"><br/>
<?php echo '<span class="small">' . $sku->getDescription(). '. Expires ' . $sku->getExpires() . '</span>'?></div>
</td></tr>
<tr><td align="center" class="idcard_cell">
<img class="idcard_logo" src="<?php echo $teamlogo?>" alt="logo">
</td></tr></table>
<?php
	} else {
		$err = "c";
		$bError = true;
	}
}

if ($bError) {
//	ob_end_flush();

	if (isDevelopmentServer() || isStagingServer())
		echo "<br/>".$err;
	else
		redirect($_SERVER["HTTP_REFERER"] . "&err=" . urlencode($err));
}?>
</div>
<?php
$cancelbutton = 1;	// places a "return" button just above the footer
// Start footer section
include('footer.php');
?>
</body>
</html>