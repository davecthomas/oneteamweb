<?php
// Only admins or coaches can execute this script. Header.php enforces this.
$isadminorcoachrequired = true;
include_once('utilsbase.php');
$title = " Scan Attendance";
$bError = false;
// Assure we have the input we need, else send them to default.php
if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid = getUserID()) == RC_RequiredInputMissing)){
	redirect("default.php?rc=" . RC_RequiredInputMissing);
}
// Get $session array initialized
$session = startSession($sessionkey, $userid);
if (! isValidSession($session )){
	redirect("default.php?rc=" . $session);
}

$teamid = getTeamID($session);
if ($teamid == TeamID_Undefined){
	$bError = true;
	$err = "t";
}
if (isset($_REQUEST["eventid"])){
	$eventid = $_REQUEST["eventid"];
} else {
	$bError = true;
	$err = "ei";
}
if (isset($_REQUEST["eventname"])){
	$eventname = $_REQUEST["eventname"];
} else {
	$bError = true;
	$err = "en";
}

if (!$bError){
	$userid_login = getUserID();


	if (AttendanceConsole::isAttendanceConsole($session)){
		$teamterms = getTeamTerms($teamid, getConnectionFromSession($session));
		$teaminfo = getTeamInfo($teamid ); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta name="keywords" content="1TeamWeb Team Management Sports Membership Clubs Site"/>
<meta name="description" content="1 Team Web: Focus On Your Team"/>
<title><?php  echo getTitle($session, $title)?></title>
<link rel="stylesheet" type="text/css" href="1team.css"/>
<script type="text/javascript" src="/1team/utils.js"></script>
<link rel="icon" type="image/png" href="/1team/img/1teamweb-logo-200.png" />
</head>
<body>
<div id="userbanner">
Signed in as <?php echo roleToStr($session["roleid"],$teamterms)?>&nbsp;<?php echo $session["fullname"]?>. <a href="/1team/logout.php<?php buildRequiredParams($session)?>">Sign out</a><br />
Session time remaining:&nbsp;<?php echo getSessionTimeRemaining($session)?>
</div>
<div id="wrapper">
<h3><?php echo $title . " for " . $teaminfo["teamname"] . " " . $teamterms["termmember"]?> : <?php echo $eventname ?></h3>
<p><?php echo $eventname ?>: Please scan <?php echo $teamterms["termmember"]?>'s or other redemption card to record attendance to <?php echo $teaminfo["teamname"]?> today.</p>
<div class="scanbox">
<form name="scanform" action="/1team/scan.php" method="post">
<?php buildRequiredPostFields($session) ?>
<table class="noborders">
<tr><td class="bold">Scan Card Here</td><td><input type="password" id="scan" name="scan" maxlength="50"></td></tr>
</table>
<input type="hidden" name="eventid" value="<?php echo $eventid?>"/>
<input type="hidden" name="eventname" value="<?php echo $eventname?>"/>
</form>
<p>If your card isn't scanning, please check the following:</p>
<ol><li>Make sure the web browser window has focus (is "on top")</li>
<li>Click the mouse in the "Scan Card Here" field to ensure the scanner input is received.</li></ol>
</div>
<script type="text/javascript">
	document.scanform.scan.focus()
	var charfield=document.getElementById("scan");
	var isTimerRunning;
	var timerScan = 0;

	charfield.onkeydown=function(e){
		// Reset the old timer
		if (timerScan != 0) {
			clearTimeout(timerScan);
			timerscan = 0;
		}
		//Start a timer that will automatically submit form in one second
		timerScan = setTimeout("document.forms['scanform'].submit()",1000);
	}

	function docLoad(){
		isTimerRunning = 0;
	}

	// AJAX support for presenting password and checking
	var xmlhttp;
	const passcheckReasonReturn = 1;
	const passcheckReasonDefault = passcheckReasonReturn;
	var passcheckReason = passcheckReasonDefault;

	// Shows the password div and optionally adds hidden form field
	function presentPassword(reason){
		passcheckReason = reason;
		if (reason == passcheckReasonReturn){
			document.getElementById('submitpass').value="Submit password and Return";
		}
		showit('passworddiv');
	}

	function checkPass(str, uid){
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)	{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url="check-pass.php";
		str = escape(str);
		url=url+"?<?php echo returnRequiredParams($session)?>&p="+str+"&uid="+uid;
		xmlhttp.onreadystatechange=stateChanged;
		xmlhttp.open("GET",url,true);
		xmlhttp.send(null);
	}

	function stateChanged(){
		if (xmlhttp.readyState==4){
			if (parseInt(xmlhttp.responseText) == parseInt("<?php echo $userid_login?>")){
				if (passcheckReason == passcheckReasonReturn) {
					document.location.href= '<?php
						if ((isset($_SERVER['HTTP_REFERER'])) && (strlen($_SERVER['HTTP_REFERER'])>0)) echo $_SERVER['HTTP_REFERER'];
						else echo "home.php?".returnRequiredParams($session)."&teamid=" . $session["teamid"];?>';
				}
			} else {
				changeclass('errorboxPW','errorboxshow');
				var passerrElm = document.getElementById('passerr');
				passerrElm.innerHTML = xmlhttp.responseText;
			}
		}
	}

	function GetXmlHttpObject(){
		// code for IE7+, Firefox, Chrome, Opera, Safari
		if (window.XMLHttpRequest)  {
			return new XMLHttpRequest();
		}
		// code for IE6, IE5
		if (window.ActiveXObject){
			return new ActiveXObject("Microsoft.XMLHTTP");
		}
		return null;
	}



</script>
<input type="button" class="btn" value="Return" name="return" id="return" onclick="presentPassword(passcheckReasonReturn)" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<div id="passworddiv" class="hideit">
<?php // The onsubmit handler below is crucial to prevent the enter key from submitting this form. This form is actually never submitted, so we need to swallow submit?>
<form name="passform" id="passform" onsubmit="return false;">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<table class="noborders">
<tr><td colspan="2">In order to exit this form, a password is required for a <?php echo $teamterms["termadmin"] ." or ". $teamterms["termcoach"] ?>.</td></tr>
<tr><td class="bold"><?php echo $teamterms["termadmin"] ." or ". $teamterms["termcoach"] ?>&nbsp; name:</td>
<td><select name="uid">
<?php
	$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid FROM users WHERE ((roleid & " . Role_TeamAdmin . ") = " . Role_TeamAdmin . " OR (roleid & " . Role_Coach . ") = " . Role_Coach . ") AND users.teamid = ? ORDER BY firstname;";
	$dbconn = getConnectionFromSession($session);
	$userResults = executeQuery($dbconn, $strSQL, $bError);
	$rowCount = 0;
	$numRows = count($userResults);
	if ( $userid_login == 0 ) {
		echo("<option value=\"0\" selected>Select member...</option>");
	}
	while($rowCount < $numRows) {
		echo "<option value=\"";
		echo $userResults[$rowCount]["id"];
		echo "\"";
		if ( $userid_login == $userResults[$rowCount]["id"] ) {
			echo(" selected");
		}
		echo ">";
		echo $userResults[$rowCount]["firstname"];
		echo " ";
		echo $userResults[$rowCount]["lastname"];
		echo ": " . roleToStr($userResults[$rowCount]["roleid"], $teamterms) ;
		echo "</option>\n";
		$rowCount++;
	} ?>
</select></td>
</tr>
<tr><td class="bold"><?php echo $teamterms["termadmin"] ." or ". $teamterms["termcoach"] ?>&nbsp;password</td><td><input type="password"  name="password" maxlength="50"/></td></tr>
<tr><td></td><td><input type="button" class="btn" value="Return" name="submitpass" id="submitpass" onclick="checkPass(this.form.password.value, <?php echo $session["userid"]?>)" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/></td><td></td></tr>
</table>
</form>
</div>
<div class="hideit" id="errorboxPW">
<div class="errorboxtitle">Incorrect credentials<div class="errorboxclose"><a class="linkopacity" href="javascript:togglevis2('errorboxPW', 'errorboxshow', 'errorboxhide');"><img src="img/x-closebutton.png" border="0"/></a></div><hr /></div>
A <?php echo $teamterms["termadmin"] ." or ".$teamterms["termcoach"] ?> is required to enter their password to submit this form.<br>Error: <div id="passerr"></div>
<br/>
<div class="boxbutton"><input type="button" value="Ok" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onClick="javascript:togglevis2('errorboxPW', 'errorboxshow', 'errorboxhide');"/></div>
</div>
<?php
		if (isset($_GET["badcard"])){
			showError("Error Scanning Card", "The card is not recognized.<br />Please assure you are scanning a valid " . appname . "&nbsp;card.", "document.scanform.scan.focus();");
		}
		include('footer.php');
	// Not team admin system
	} else {
		redirect("default.php");
	}
// bError
} else {
	redirect("default.php");
}?>
