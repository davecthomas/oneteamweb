<?php
ob_start(); 	// This caches non-header output, allowing us to redirect after header.php
include('utils.php');
include_once('obj/Mail.php');
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

if (isset($_REQUEST["message"])){
	$messagein = trim($_REQUEST["message"]);
} else {
	$bError = true;
	$err = "m";
}

// The checkbox for accepted must be selected.
if (isset($_REQUEST["sendmecopy"])) {
	$sendmecopy = true;
	$user = new User($session, $session["userid"]);
	$sendmecopyarray = array($user->getUserID(), $user->getFirstname(), $user->getLastname(),$user->getEmail(),
		$user->getSmsphone(), $user->getSmsphonecarrier());

} else {
	$sendmecopy = false;
	$sendmecopyarray = array();
}

$sms = false;
if ( isset($_REQUEST["sms"])){
	$sms = (boolean) $_REQUEST["sms"];
	if ($sms)
		$subject = "";
}

if (!$sms){
	$messagein = nl2br($messagein);	// Replace new lines with <br>
	// For email message text, find URLs and convert them into anchor tags for friendlier email interactivity.
	if (findURLInString($messagein, $matches, 0) > 0){
		for ($i = 0; $i<count($matches[0]); $i++){
			$replstr = '<a href="'.$matches[0][$i][0].'">'.$matches[0][$i][0].'</a>';
			$messagein = str_replace( $matches[0][$i][0], $replstr, $messagein);
		}
	}
	if (isset($_REQUEST["subject"])){
		$subject = trim($_REQUEST["subject"]);
	} else {
		$bError = true;
		$err = "s";
	}
}



// Array indices for the recipient element in the recipients array
define("recipient_userid", 0);
define("recipient_firstname", 1);
define("recipient_lastname", 2);
define("recipient_email", 3);
define("recipient_smsphone", 4);
define("recipient_smsphonecarrier", 5);
$dbconn = getConnection();
if (isset($_REQUEST["recipientgroup"])){

	$recipientgroup = $_REQUEST["recipientgroup"];
	// Lots of stuff is based on recipientgroup
	// This switch statement needs to assign an array of recipients (id, fullname, email, smsphone, smsphonecarrier...)
	switch ($recipientgroup){
		case emailRecipientGroupArbitrarySelection:
			if (isset($_REQUEST["emailrecipientlistselections"])){
				$recipientlist = trim($_REQUEST["emailrecipientlistselections"]);
				// Array format: uid1, ...
				$recipientsStrArray = explode(",",$recipientlist);
				$numrecipients = count($recipientsStrArray);
				// Verify each entry is a number
				$strSQLInjectionProtection = "";
				$recipients = array();
				for ($i=0; $i<$numrecipients; $i++){
					$strSQLInjectionProtection .="?";
					if ($i != ($numrecipients-1)) $strSQLInjectionProtection .=",";
					array_push($recipients, (int)$recipientsStrArray[$i]);
				}
				$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email FROM users, useraccountinfo WHERE users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status = " . UserAccountStatus_Active . " AND users.teamid = ".$teamid." AND userid IN (".$strSQLInjectionProtection.")";
				$results = executeQuery($dbconn, $strSQL, $bError, $recipients);
				$rowCount = 0;
				$numrecipients = count($results);
				$recipients = array();
				while ($rowCount < $numrecipients) {
					array_push($recipients, array($results[$rowCount]["userid"], $results[$rowCount]["firstname"], $results[$rowCount]["lastname"],$results[$rowCount]["email"], $results[$rowCount]["smsphone"], $results[$rowCount]["smsphonecarrier"]) );
					$rowCount++;
				}

			} else {
				$bError = true;
				$err = "rl";
			}

			break;

		case emailRecipientGroupAllActiveMembers:
			$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email FROM users, useraccountinfo WHERE users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status = " . UserAccountStatus_Active . " AND users.teamid = ?;";
			$results = executeQuery($dbconn, $strSQL, $bError, array($teamid));
			$rowCount = 0;
			$numrecipients = count($results);
			$recipients = array();
			while ($rowCount < $numrecipients) {
				array_push($recipients, array($results[$rowCount]["userid"], $results[$rowCount]["firstname"], $results[$rowCount]["lastname"],$results[$rowCount]["email"], $results[$rowCount]["smsphone"], $results[$rowCount]["smsphonecarrier"]) );
				$rowCount++;
			}
			break;

		case emailRecipientGroupNewMembers:
			$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email from users, useraccountinfo WHERE users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status = " . UserAccountStatus_Active . " AND users.teamid = ? and (startdate > (current_date - '1 mon'::interval));";
			$results = executeQuery($dbconn, $strSQL, $bError, array($teamid));
			$rowCount = 0;
			$numrecipients = count($results);
			$recipients = array();
			while ($rowCount < $numrecipients) {
				array_push($recipients, array($results[$rowCount]["userid"], $results[$rowCount]["firstname"], $results[$rowCount]["lastname"],$results[$rowCount]["email"], $results[$rowCount]["smsphone"], $results[$rowCount]["smsphonecarrier"]) );
				$rowCount++;
			}
			break;

		case emailRecipientGroupNonParticipants:		// Those who do not have an active (non-expired) order within a given program
			if (isset($_REQUEST["programid"])){
				$programid_nonparticipant = $_REQUEST["programid"];
			} else {
				$bError = true;
				$err = "np";
			}
			$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email from users, useraccountinfo where users.teamid = ? AND users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status = " . UserAccountStatus_Active . " EXCEPT (SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email FROM useraccountinfo, (programs INNER JOIN (users RIGHT OUTER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid) on orderitems.programid = programs.id) WHERE orderitems.programid = ? AND orderitems.teamid = ? AND useraccountinfo.id = users.useraccountinfo AND (paymentdate + expires >= current_date));";
			$results = executeQuery($dbconn, $strSQL, $bError, array($teamid, $programid_nonparticipant, $teamid));
			$rowCount = 0;
			$numrecipients = count($results);
			$recipients = array();
			while ($rowCount < $numrecipients) {
				array_push($recipients, array($results[$rowCount]["userid"], $results[$rowCount]["firstname"], $results[$rowCount]["lastname"],$results[$rowCount]["email"], $results[$rowCount]["smsphone"], $results[$rowCount]["smsphonecarrier"]) );
				$rowCount++;
			}
			break;

		case emailRecipientGroupActiveParticipants:	// Those who do have an active (non-expired) order within a given program
			if (isset($_REQUEST["programid"])){
				$programid_participant = $_REQUEST["programid"];
			} else {
				$bError = true;
				$err = "p";
			}

			$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email, paymentmethods.name as paymentmethodname, programs.name as programname, skus.*, skus.name as skuname, orderitems.id as payid, orderitems.* FROM useraccountinfo, (paymentmethods INNER JOIN (programs INNER JOIN (users RIGHT OUTER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid) on orderitems.programid = programs.id) on orderitems.paymentmethod = paymentmethods.id) WHERE orderitems.numeventsremaining <> 0 AND orderitems.programid = ? AND orderitems.teamid = ? AND (paymentdate + expires >= current_date) AND useraccountinfo.id = users.useraccountinfo AND useraccountinfo.status = " . UserAccountStatus_Active . ";";
			$results = executeQuery($dbconn, $strSQL, $bError, array($programid_participant, $teamid));
			$rowCount = 0;
			$numrecipients = count($results);
			$recipients = array();
			while ($rowCount < $numrecipients) {
				array_push($recipients, array($results[$rowCount]["userid"], $results[$rowCount]["firstname"], $results[$rowCount]["lastname"],$results[$rowCount]["email"], $results[$rowCount]["smsphone"], $results[$rowCount]["smsphonecarrier"]) );
				$rowCount++;
			}
			break;

		case emailRecipientGroupRecentlyExpired:		// People who have recently had a given program
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
			$numrecipients = count($results);
			$recipients = array();
			while ($rowCount < $numrecipients) {
				array_push($recipients, array($results[$rowCount]["userid"], $results[$rowCount]["firstname"], $results[$rowCount]["lastname"],$results[$rowCount]["email"], $results[$rowCount]["smsphone"], $results[$rowCount]["smsphonecarrier"]) );
				$rowCount++;
			}
			break;

		case emailRecipientGroupPastMembers:			// Inactive members
			$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email, useraccountinfo.status FROM users, useraccountinfo WHERE users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status = " . UserAccountStatus_Inactive . " AND users.teamid = ?;";
			$results = executeQuery($dbconn, $strSQL, $bError, array($teamid));
			$rowCount = 0;
			$numrecipients = count($results);
			$recipients = array();
			while ($rowCount < $numrecipients) {
				array_push($recipients, array( $results[$rowCount]["userid"], $results[$rowCount]["firstname"], $results[$rowCount]["lastname"],$results[$rowCount]["email"], $results[$rowCount]["smsphone"], $results[$rowCount]["smsphonecarrier"]) );
				$rowCount++;
			}

			break;

		default:
			$bError = true;
			$err = "rg";
			break;

	}
} else {
	$bError = true;
	$err = "g";
}

$returnErrString = "";
if (!$bError) {
	$returnString = "";
	$fromemail = getUserEmail($session, $dbconn);
	// Get team terms gracefully handles the case where app admin has no team
	$teamterms = getTeamTerms(getTeamID($session), $dbconn);
	$rolename = roleToStr($session["roleid"], $teamterms);
	$teaminfo = getTeamInfo( $teamid);
	if ((isset($session)) && (isset($session["teamimageurl"]))) {
		if (strlen($session["teamimageurl"]) > 0) {
			$logoimg = url_sslroot .$session["teamimageurl"];
		} else {
			$logoimg =  url_sslroot.Default_Logo;
		}
	} else {
		$logoimg = url_sslroot .Default_Logo;
	}
	if ((isset($teaminfo["website"])) && (strlen($teaminfo["website"])>strlen("http://.com"))) {
		$teamnamesite = "<a href=\"" . $teaminfo["website"] . "\">" . $teaminfo["teamname"] . "</a>";
	} else {
		$teamnamesite = $teaminfo["teamname"];
	}

	// Before we start to send, tack sendmecopy on, if requested
	if (($sendmecopy) && (count($sendmecopyarray)>0)){
		array_push($recipients, $sendmecopyarray );
		$numrecipients++;
	}
	// Each recipient is made up of an array of values
	for ($i=0; $i<$numrecipients; $i++){
		$message = $messagein;
		$recipientarray = $recipients[$i];
		if ($sms){
			$subject = "";
			$toemail = $recipientarray[recipient_smsphone]."@".getSmsCarrierEmail($recipientarray[recipient_smsphonecarrier]);
			$message = $recipientarray[recipient_firstname] . "- " . $message . " -" . $teaminfo["teamname"];
		} else{
			$toemail = $recipientarray[recipient_email];
			$message = "
<style type=\"text/css\"><!--
#wrapper {
    position: absolute;
    top: 1.25em;
    left: 5;
	min-height: 95%;
	height: auto !important;
	height: 95%;
	width: 95%;
	margin: 0 auto -4em;
}

html, body {
	font-family: verdana, arial;
}
p.strong { font-weight:bold ;}
p.smaller {	font-size: .8em;}
span.strong { font-weight:bold ;}
span.smaller {	font-size: .8em;}
p{
	font-size: .8em;
}
#footer {
	font-size: .6em;
	float: left;
	position:absolute;
	bottom: 0; left: 0;
	height: 1em;
}
#logo {
	float: right;
	position:absolute;
	bottom: 0; right: 0;
}
div.pushlogo {
	height: 200px;
}
div.push {
	height: 1em;
}--></style>
<html>
<head>
<title>".$subject."</title>
</head>
<body>
<div id=\"wrapper\">
<p class=\"strong\">".trim($recipientarray[recipient_firstname]) . ",</p>
<p>".$message."</p>
<p>Thank you,</p>
<p><span class=\"strong\">". $session["fullname"] . "</span><br/>
<span class=\"smaller\">".$teamnamesite." " .$rolename."</span></p>
<div class=\"push\"></div>
<div class=\"push-logo\"></div>
<div id=\"logo\"><img alt=\"". $teaminfo["teamname"] ."\" src=\"".$logoimg."\" width=\"200\" align=\"right\"/></div>
<div class=\"push\"></div>
<div id=\"footer\">
<p>This message was sent from <a href=\"" . siteurl . "\">" . companyname . "</a> on ". date("l F j, Y") . ".</p>
</div>
</div>
</body>
</html>";

		}
		if (emailMember( $session, $teamid, $subject, $message, $toemail, $fromemail,  $sms, $err) != RC_Success){
			if (strlen($returnErrString) > 0) {
				$returnErrString .= ", ";
				if ($i == $numrecipients-1) $returnErrString .= " and ";
			}
			$returnErrString .= $recipientarray[recipient_firstname]. " reason: ".$err;
			if ($i == $numrecipients-1) $returnErrString .= ".";
			$bError = true;
		} else {
			if (strlen($returnString) > 0) {
				if ($numrecipients > 2)
					$returnString .= ", ";
				if ($i == $numrecipients-1) $returnString .= " and ";
			}
			$returnString .= $recipientarray[recipient_firstname] . " ". $recipientarray[recipient_lastname];
			if ($i == $numrecipients-1) $returnString .= ".";
		}
	}
	if ($numrecipients == 0) {
		$returnErrString = "No ".$teamterms["termmember"]."s found meet the recipient list criteria.";
		// Not really an error, but this is the best way for the user to see the message
		$bError = true;
	}

	if (!$bError){
		redirect($_SERVER["HTTP_REFERER"] . "&done=" . urlencode($returnString));
		ob_end_flush();
	}
}
if ($bError) {
	redirect($_SERVER["HTTP_REFERER"] . "&err=" . urlencode($returnErrString));
	ob_end_flush();
}


function emailMember( $session, $teamid, $subject, $message, $toemail, $fromemail, $sms, &$err){
	$bError = false;
	$err = "";

	if (!$bError)  {
		if (!isValidEmail($toemail)){
			return RC_EmailAddrInvalid;
		}

		$headers = "From: ".$fromemail."\r\nReply-To: ".$fromemail."\r\n";
		if (!$sms){
			// Always set content-type when sending HTML email
			$headers .= "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
			//define the headers we want passed. Note that they are separated with \r\n
		}

		ini_set("SMTP", MailServer);
		$m = new Mail();
		$statuscode = $m->mail($toemail, $subject, $message );
		if (!$m->statusok($statuscode)){
			$bError = true;
			$err = "mail";
		}
	}
	if (!$bError ) return RC_Success;
	else return RC_EmailFailed;

}?>
