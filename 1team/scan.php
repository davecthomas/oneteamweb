<?php  
ob_start(); 	// This caches non-header output, allowing us to redirect after header.php
include('utils.php');
include_once('obj/Objects.php');
$bError = false;
$err = "";
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

// This is a special page for a couple of reasons
// 1. It must be run from the administrative console by a team admin
// 2. It automatically redirects to the originating scan-form since scanning
//    is considered a continuous mode until broken out of. 
// 3. There is no header, menu, etc, since we don't want the user to have access to the admin's menus.
if (isset($_REQUEST["eventid"])){
	$eventid = $_REQUEST["eventid"];
} else {
	$bError = true;
	$err = "i";
} 
if (isset($_REQUEST["eventname"])){
	$eventname = $_REQUEST["eventname"];
} else {
	$bError = true;
	$err = "i";
}

if (!$bError){
	$redemptioncardfound = false;
	$teamid = getTeamID($session);
	$memberID = User::UserID_Undefined;
	$teamterms = getTeamTerms($teamid, getConnectionFromSession($session));
	$formScan = cleanSQL($_POST["scan"]);
	
	// Support new cards (zero left padded memberid with the last digit chopped off)
	if (is_numeric($formScan)){
		$memberID = substr($formScan, 0, strlen($formScan)-1);
		if (!isValidUserID($memberID)) {
			$bError = true;
			$err = "m".$memberID;
		}
	// Support old AJJ cards (teamid-memberid)
	// And support new alphanumeric redemption cards (barcode format "39")
	} else {
		// First test for a redemption card hit
		$redemptioncard = new RedemptionCard($session, DbObject::DbID_Undefined, $formScan);
		if ((DbObject::isDbObject($redemptioncard))&& ($redemptioncard->isValid())){
			// trigger special behavior in log-attendance included below
			$redemptioncardfound = true;

		// Support original AJJ formatted cards
		} else {
			$splitscan = explode("-", $formScan);
			// Validate strings
			if ((is_array($splitscan)) && (count($splitscan) == 2)) {
				$teamid = $splitscan[0];
				$memberID = $splitscan[1];
				if (!isValidUserID($memberID)) {
					$bError = true;
					$err = "m".$memberID;
				}
			// Unrecognized card
			} else {
				$bError = true;
				$err = $formScan;
			}
		}
	}

	if ($bError){
		redirect("scan-form.php?" . returnRequiredParams($session) . "&eventid=" . $eventid . "&eventname=" . $eventname . "&badcard=".$formScan."&err=" . $err);
	}?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><?php echo appname . ": Scan " . $teamterms["termmember"]?></title>
<link rel="stylesheet" type="text/css" href="1team.css"/>
</head>
<body>
<?php
	$attendanceDate = date("m/d/Y");
	// Here we decide if we should pass our attendance logging to regular attendance or redemption card processing
	// This include must have attendanceDate, eventid, memberid and teamid defined and initialized first
	if (!$redemptioncardfound) include("include-log-attendance.php");
	else include("include-log-redemptioncard.php");
	
	if (!$bError) { ?>
<script type="text/javascript">
	setTimeout('document.location="scan-form.php?<?php echo returnRequiredParams($session) . "&eventid=" . $eventid . "&eventname=" . $eventname?>"', <?php echo scanTimeResultDisplayTimeout?>);
</script>
<?php
	} else { ?>
<p><a href="<?php echo $_SERVER["HTTP_REFERER"] ."&err=" . $err?>" title="Back">Return to scanner</a>.</p>
<?php
	}	
	// Start footer section
	include('footer.php'); ?>
</body>
</html>
<?php
} else {
	redirect($_SERVER["HTTP_REFERER"]."&err=" . $err);
}
ob_end_flush(); ?>