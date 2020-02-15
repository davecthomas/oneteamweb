<?php
//ob_start(); 	// This caches non-header output, allowing us to redirect after header.php
$bError = false;
$err = "";
$title = "Redemption Card";
include_once('header-minimal.php');
require("php-barcode/php-barcode.php");
define("rowsPerPage", 8);
define("numColumns", 3);
define("userList", 1);

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
if ( isset($_REQUEST["mode"])){
	$mode = $_REQUEST["mode"];
	if ($mode == EditCard){
		if ( isset($_REQUEST["id"])){
			$id = $_REQUEST["id"];
		} else {
			$bError = true;
			$err = "i";
		}
	}
} else {
	$bError = true;
	$err = "m";
}
if ( isset($_REQUEST["type"])){
	$type = $_REQUEST["type"];
	if ($type == RedemptionCard::TypeGiftCard)
		$cardtypeText = $cardTypes[$type] ;
	else
		$cardtypeText = $cardTypes[$type] ;
} else {
	$bError = true;
	$err = "t";
}



if ( isset($_REQUEST["skuid"])){
	$skuid = $_REQUEST["skuid"];
	$strSQL = "SELECT name FROM skus WHERE id = ? AND teamid = ?";
	$dbconn = getConnectionFromSession($session);
	$skuname = executeQueryFetchColumn($dbconn, $strSQL, $bError, array($skuid, $teamid));

} else {
	$bError = true;
	$err = "s";
}

if ( isset($_REQUEST["facevalue"])){
	$facevalue = $_REQUEST["facevalue"];
} else {
	$bError = true;
	$err = "f";
}
if ( isset($_REQUEST["amountpaid"])){
	$amountpaid = $_REQUEST["amountpaid"];
} else {
	$bError = true;
	$err = "a";
}
if ( isset($_REQUEST["paymentmethod"])){
	$paymentmethod = $_REQUEST["paymentmethod"];
} else {
	$bError = true;
	$err = "p";
}
if ( isset($_REQUEST["expires"])){
	$expires = $_REQUEST["expires"];
} else {
	$bError = true;
	$err = "d";
}
if ( isset($_REQUEST["numevents"])){
	$numevents = $_REQUEST["numevents"];
} else {
	$bError = true;
	$err = "ne";
}
if ( isset($_REQUEST["description"])){
	$description = $_REQUEST["description"];
} else {
	$description = "";

}

if (isset($_REQUEST["redemptioncardgroup"])){
	$redemptioncardgroup = $_REQUEST["redemptioncardgroup"];
} else {
	$redemptioncardgroup = RedemptionCard::RecipientGroupArbitrarySelection;
}

if (strlen($session["teamimageurl"]) > 0) {
	$teamlogo = $session["teamimageurl"];
} else {
	$teamlogo = "/1team/img/1teamweb-logo-200.png";
}

// Array indices for the redemptioncard element in the redemptioncards array
define("redemptioncard_userid", 0);
define("redemptioncard_firstname", 1);
define("redemptioncard_lastname", 2);
define("redemptioncard_email", 3);
define("redemptioncard_smsphone", 4);
define("redemptioncard_smsphonecarrier", 5);

// Lots of stuff is based on redemptioncardgroup
// This switch statement needs to assign an array of redemptioncards (id, fullname, email, smsphone, smsphonecarrier...)
switch ($redemptioncardgroup){
	case RedemptionCard::RecipientGroupGuest:
		$redemptioncards = array();
		array_push($redemptioncards, array(User::UserID_Guest, User::Username_Guest, User::Username_Guest, "", "", "") );
		$numredemptioncards = 1;
		break;

	case RedemptionCard::RecipientGroupArbitrarySelection:
		if (isset($_REQUEST["redemptioncardlistselections"])){
			$redemptioncardlist = trim($_REQUEST["redemptioncardlistselections"]);
			// Array format: uid1, ...
			$redemptioncardsStrArray = explode(",",$redemptioncardlist);
			$numredemptioncards = count($redemptioncardsStrArray);
			// Verify each entry is a number
			$strSQLInjectionProtection = "";
			$redemptioncards = array();
			for ($i=0; $i<$numredemptioncards; $i++){
				$strSQLInjectionProtection .="?";
				if ($i != ($numredemptioncards-1)) $strSQLInjectionProtection .=",";
				array_push($redemptioncards, (int)$redemptioncardsStrArray[$i]);
			}
			$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email FROM users, useraccountinfo WHERE users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status = " . UserAccountStatus_Active . " AND users.teamid = ".$teamid." AND userid IN (".$strSQLInjectionProtection.")";
			$results = executeQuery($dbconn, $strSQL, $bError, $redemptioncards);
			$rowCount = 0;
			$numredemptioncards = count($results);
			$redemptioncards = array();
			while ($rowCount < $numredemptioncards) {
				array_push($redemptioncards, array($results[$rowCount]["userid"], $results[$rowCount]["firstname"], $results[$rowCount]["lastname"],$results[$rowCount]["email"], $results[$rowCount]["smsphone"], $results[$rowCount]["smsphonecarrier"]) );
				$rowCount++;
			}

		} else {
			$bError = true;
			$err = "rl";
		}

		break;

	case RedemptionCard::RecipientGroupAllActiveMembers:
		$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email FROM users, useraccountinfo WHERE users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status = " . UserAccountStatus_Active . " AND users.teamid = ?;";
		$results = executeQuery($dbconn, $strSQL, $bError, array($teamid));
		$rowCount = 0;
		$numredemptioncards = count($results);
		$redemptioncards = array();
		while ($rowCount < $numredemptioncards) {
			array_push($redemptioncards, array($results[$rowCount]["userid"], $results[$rowCount]["firstname"], $results[$rowCount]["lastname"],$results[$rowCount]["email"], $results[$rowCount]["smsphone"], $results[$rowCount]["smsphonecarrier"]) );
			$rowCount++;
		}
		break;

	case RedemptionCard::RecipientGroupNewMembers:
		$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email from users, useraccountinfo WHERE users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status = " . UserAccountStatus_Active . " AND users.teamid = ? and (startdate > (current_date - '1 mon'::interval));";
		$results = executeQuery($dbconn, $strSQL, $bError, array($teamid));
		$rowCount = 0;
		$numredemptioncards = count($results);
		$redemptioncards = array();
		while ($rowCount < $numredemptioncards) {
			array_push($redemptioncards, array($results[$rowCount]["userid"], $results[$rowCount]["firstname"], $results[$rowCount]["lastname"],$results[$rowCount]["email"], $results[$rowCount]["smsphone"], $results[$rowCount]["smsphonecarrier"]) );
			$rowCount++;
		}
		break;

	case RedemptionCard::RecipientGroupNonParticipants:		// Those who do not have an active (non-expired) order within a given program
		if (isset($_REQUEST["programid"])){
			$programid_nonparticipant = $_REQUEST["programid"];
		} else {
			$bError = true;
			$err = "np";
		}
		$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email from users, useraccountinfo where users.teamid = ? AND users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status = " . UserAccountStatus_Active . " EXCEPT (SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email FROM useraccountinfo, (programs INNER JOIN (users RIGHT OUTER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid) on orderitems.programid = programs.id) WHERE orderitems.programid = ? AND orderitems.teamid = ? AND useraccountinfo.id = users.useraccountinfo AND (paymentdate + expires >= current_date));";
		$results = executeQuery($dbconn, $strSQL, $bError, array($teamid, $programid_nonparticipant, $teamid));
		$rowCount = 0;
		$numredemptioncards = count($results);
		$redemptioncards = array();
		while ($rowCount < $numredemptioncards) {
			array_push($redemptioncards, array($results[$rowCount]["userid"], $results[$rowCount]["firstname"], $results[$rowCount]["lastname"],$results[$rowCount]["email"], $results[$rowCount]["smsphone"], $results[$rowCount]["smsphonecarrier"]) );
			$rowCount++;
		}
		break;

	case RedemptionCard::RecipientGroupActiveParticipants:	// Those who do have an active (non-expired) order within a given program
		if (isset($_REQUEST["programid"])){
			$programid_participant = $_REQUEST["programid"];
		} else {
			$bError = true;
			$err = "p";
		}

		$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email, paymentmethods.name as paymentmethodname, programs.name as programname, skus.*, skus.name as skuname, orderitems.id as payid, orderitems.* FROM useraccountinfo, (paymentmethods INNER JOIN (programs INNER JOIN (users RIGHT OUTER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid) on orderitems.programid = programs.id) on orderitems.paymentmethod = paymentmethods.id) WHERE orderitems.numeventsremaining <> 0 AND orderitems.programid = ? AND orderitems.teamid = ? AND (paymentdate + expires >= current_date) AND useraccountinfo.id = users.useraccountinfo AND useraccountinfo.status = " . UserAccountStatus_Active . ";";
		$results = executeQuery($dbconn, $strSQL, $bError, array($programid_participant, $teamid));
		$rowCount = 0;
		$numredemptioncards = count($results);
		$redemptioncards = array();
		while ($rowCount < $numredemptioncards) {
			array_push($redemptioncards, array($results[$rowCount]["userid"], $results[$rowCount]["firstname"], $results[$rowCount]["lastname"],$results[$rowCount]["email"], $results[$rowCount]["smsphone"], $results[$rowCount]["smsphonecarrier"]) );
			$rowCount++;
		}
		break;

	case RedemptionCard::RecipientGroupRecentlyExpired:		// People who have recently had a given program
		if (isset($_REQUEST["programid"])){
			$programid_participant = $_REQUEST["programid"];
		} else {
			$bError = true;
			$err = "p";
		}
		// Recently expired orderitems, or active orderitems with no remaining events
		// note, the form won't allow the "Any program" variation (but should).
		if ((isset($programid_participant)) && ($programid_participant != Program_Undefined)){
			$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email, paymentmethods.name as paymentmethodname, programs.name as programname, skus.*, skus.name as skuname, orderitems.id as payid, orderitems.* FROM useraccountinfo, (paymentmethods INNER JOIN (programs INNER JOIN (users RIGHT OUTER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid) on orderitems.programid = programs.id) on orderitems.paymentmethod = paymentmethods.id) WHERE orderitems.programid = ? AND orderitems.teamid = ? and (((paymentdate + expires > (current_date - '1 mon'::interval)) AND (paymentdate + expires < current_date)) OR ((paymentdate + expires >= current_date) and numeventsremaining = 0)) AND useraccountinfo.id = users.useraccountinfo AND useraccountinfo.status = " . UserAccountStatus_Active . ";";
			$results = executeQuery($dbconn, $strSQL, $bError, array($programid_participant , $teamid));

		// Or to list all orderitems, regardless of program
		} else {
			$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email, paymentmethods.name as paymentmethodname, programs.name as programname, skus.*, skus.name as skuname, orderitems.id as payid, orderitems.* FROM useraccountinfo, (paymentmethods INNER JOIN (programs INNER JOIN (users RIGHT OUTER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid) on orderitems.programid = programs.id) on orderitems.paymentmethod = paymentmethods.id) WHERE orderitems.teamid = ? and (((paymentdate + expires > (current_date - '1 mon'::interval)) AND (paymentdate + expires < current_date)) OR ((paymentdate + expires >= current_date) and numeventsremaining = 0)) AND useraccountinfo.id = users.useraccountinfo AND useraccountinfo.status = " . UserAccountStatus_Active . ";";
			$results = executeQuery($dbconn, $strSQL, $bError, array($teamid));
		}
		$rowCount = 0;
		$numredemptioncards = count($results);
		$redemptioncards = array();
		while ($rowCount < $numredemptioncards) {
			array_push($redemptioncards, array($results[$rowCount]["userid"], $results[$rowCount]["firstname"], $results[$rowCount]["lastname"],$results[$rowCount]["email"], $results[$rowCount]["smsphone"], $results[$rowCount]["smsphonecarrier"]) );
			$rowCount++;
		}
		break;

	case RedemptionCard::RecipientGroupPastMembers:			// Inactive members
		$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email, useraccountinfo.status FROM users, useraccountinfo WHERE users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status = " . UserAccountStatus_Inactive . " AND users.teamid = ?;";
		$results = executeQuery($dbconn, $strSQL, $bError, array($teamid));
		$rowCount = 0;
		$numredemptioncards = count($results);
		$redemptioncards = array();
		while ($rowCount < $numredemptioncards) {
			array_push($redemptioncards, array( $results[$rowCount]["userid"], $results[$rowCount]["firstname"], $results[$rowCount]["lastname"],$results[$rowCount]["email"], $results[$rowCount]["smsphone"], $results[$rowCount]["smsphonecarrier"]) );
			$rowCount++;
		}

		break;

	default:
		$bError = true;
		$err = "rg";
		break;

}

$returnErrString = "";
if (!$bError) {
	$returnString = "";
	// Get team terms gracefully handles the case where app admin has no team
	$teamterms = getTeamTerms(getTeamID($session), $dbconn);
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

	// Each redemptioncard is made up of an array of values
	$rowCount = 0;
	for ($loopCount=1; $loopCount<=$numredemptioncards; $loopCount++){
		if (($loopCount % numColumns) == 1){     // 1, 4, 7, 10, ...
			// Every 3 members, we start a new row
			$rowCount++;
			// Every 8 rows we start a new table and force a page break before it
			if (($rowCount % rowsPerPage) == 1) {  // 9, 17, ..
				if ($rowCount != 1) {
					// Close out a page with a table closing,
					// page break
					// then back page of logos (for duplex hotness)
					// page break
					echo "</table>\n";?>
<?php				// Every 8 rows, we ask the page to break (relevant for printing only) ?>
<div style="page-break-before:always;font-size:1;margin:0;border:0;"><span style="visibility: hidden;">-</span></div>
<?php				// Generate a page full of logos so when printed duplex, the logos are on the back of the barcode cards ?>
<table class="idcard">
<?php				for ($loopLogo=1; $loopLogo <= rowsPerPage; $loopLogo++){?>
<tr>
<?php					for ($loopRow = 1; $loopRow <= numColumns; $loopRow++){ ?>
<td align="center" class="idcard_cell"><span class="strong">&nbsp</span><br/>&nbsp<br><img class="idcard_logo" src="<?php echo $teamlogo?>" alt="logo">&nbsp<br/><br/></td>
<?php					} ?>
</tr>
<?php				} ?>
</table>
<div style="page-break-before:always;font-size:1;margin:0;border:0;"><span style="visibility: hidden;">-</span></div>
<?php
				} ?>
<table class="idcard">
<?php		}?>
<tr>
<?php	}
		// Get one card's array data and insert into DB
		$redemptioncardarray = $redemptioncards[$loopCount-1];
		if ($mode == NewCard){
			$strSQL = "INSERT INTO redemptioncards VALUES (DEFAULT, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id";
			$redemptioncardvalues = array($teamid, $redemptioncardarray[redemptioncard_userid], $skuid, date('m-d-Y'), $amountpaid, $numevents, $expires, $paymentmethod, $description, $type, $facevalue);
			$id = executeQueryFetchColumn($dbconn, $strSQL, $bError, $redemptioncardvalues);
		} else {
			$strSQL = "UPDATE redemptioncards SET teamid=?, userid=?, skuid=?, createdate=?, amountpaid=?, numeventsremaining=?, expires=?, paymentmethod=?, description=?, type=?, facevalue=? WHERE id = ?;";
			$redemptioncardvalues = array($teamid, $redemptioncardarray[redemptioncard_userid], $skuid, date('m-d-Y'), $amountpaid, $numevents, $expires, $paymentmethod, $description, $type, $facevalue, $id);
			executeQuery($dbconn, $strSQL, $bError, $redemptioncardvalues);
		}
		$cardforname = $redemptioncardarray[redemptioncard_firstname] . " ". $redemptioncardarray[redemptioncard_lastname];
		// Print out the card ?>
<td align="center" class="idcard_cell">
<div id="idcard">
<span class="smallbold"><?php
		echo $teamname."&nbsp;".$cardtypeText . "</span><br/>\n";
		echo '<span class="small">'.$cardforname;
		if ($numevents == Sku::NumEventsUnlimited) $numeventsstr ="Unlimited";
		else if ($numevents == Sku::NumEventsUndefined) $numeventsstr = "";
		else $numeventsstr = $numevents;
		echo ": ". $numeventsstr . " ". $skuname . "</span><br/>\n";
		// rawcode format (pre hash):
		//	[0] redemption card type (1,2,3)><zero pads><redemptioncardid>
		//   eg: 2000000000045	- This represents a guest pass for redemption card ID 45
		$rawcode = str_pad(getUserBarcodeNumber($teamid, $id), barcodeLength-1, "0", STR_PAD_LEFT);
		$rawcode = $type.$rawcode;
		// Chop hash off at 12. It's ok, we're really not neededing the hash data. It's just a way to scramble the text
		$barcode = substr(hash('sha1', $rawcode), 0, barcodeLength);
		// Uppdate the record with the barcode
		$strSQL = "UPDATE redemptioncards SET code=? WHERE id = ?;";
		$redemptioncardvalues = array($barcode, $id);
		executeQuery($dbconn, $strSQL, $bError, $redemptioncardvalues);?>
<img class="idcard_redemptioncardbarcode" src="./php-barcode/barcode.php?code=<?php echo $barcode?>&encoding=39" alt="barcode"></div>
<?php echo '<span class="small">' . $description. '. Expires ' . $expires . '</span>'?><br>
</td>




<?php
		// move to the next card so we can see if we are done, so we know how much to pad or close row
		if (($loopCount == $numredemptioncards) || (($loopCount % numColumns) == 0)) {    //
			for ($loopPad = 1; $loopPad <= (numColumns - $loopCount); $loopPad++){ ?>
<td class="idcard_cell"><div id="idcard"></div></td>
<?php		} ?>
</tr>
<?php   	}
	}
	// Now that we're done with all the cards, close the table and dump out logos for the back of the page ?>
</table>
<div style="page-break-before:always;font-size:1;margin:0;border:0;"><span style="visibility: hidden;">-</span></div>
<table class="idcard">
<?php
		// tweak the rowcount to prevent more than a full page of logos at the end
		if ($rowCount > rowsPerPage) {
			$rowCount = rowsPerPage;
		}
		for ($loopLogo=1; $loopLogo <= $rowCount; $loopLogo++){ ?>
<tr>
<?php		for ($loopRow = 1; $loopRow <= numColumns; $loopRow++){  ?>
<td align="center" class="idcard_cell"><img class="idcard_logo" src="<?php echo $teamlogo?>" alt="logo"></td>
<?php		} ?>
</tr>
<?php   	}  ?>
</table>
<?php
	if ($numredemptioncards == 0) {
		$returnErrString = "No ".$teamterms["termmember"]."s found meet the redemptioncard list criteria.";
		// Not really an error, but this is the best way for the user to see the message
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
