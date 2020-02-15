<?php
// Only admins and coaches can execute this script. Header.php enforces this.
$isadminorcoachrequired = true;

$title = "Member Attendance Roster";
include_once('header-minimal.php');

?>
<div id="userbanner">
Signed in as <?php echo roleToStr($session["roleid"],$teamterms)?>&nbsp;<?php echo $session["fullname"]?>. <a href="/1team/logout.php<?php buildRequiredParams($session)?>">Sign out</a><br />
Session time remaining:&nbsp;<?php echo getSessionTimeRemaining($session)?>
</div>
<script type="text/javascript">
function moveOptions( srcList, destList, ListToEnableSubmit, presentpasswordButton )
{
	var srcListElm = document.getElementById(srcList);
	var destListElm = document.getElementById(destList);
	var ListToEnableSubmitElm = document.getElementById(ListToEnableSubmit);
	var presentpasswordButtonElm = document.getElementById(presentpasswordButton);
	moveSelectedOptions(srcListElm, destListElm);
	sortList(srcListElm);
	sortList(destListElm);
	if (ListToEnableSubmitElm.options.length > 0){
		presentpasswordButtonElm.disabled=false;
	} else {
		presentpasswordButtonElm.disabled=true;
	}
	// Check the list to enable for length then enable/disable submit
	var removeButtonElm;
	removeButtonElm = document.getElementById('remove')
	if (ListToEnableSubmitElm.options.length > 0) {
		presentpasswordButtonElm.disabled=false;
		removeButtonElm.disabled=false;
	}
	else {
		presentpasswordButtonElm.disabled=true;
		removeButtonElm.disabled=true;
		removeButtonElm.setAttribute('class', 'btnbig');
	}

}

function moveSelectedOptions(from,to) {
	for (var i=0; i<from.options.length; i++) {
		for(var j=0;j<to.options.length;j++){
			if((from.options[i].selected)&&(from.options[i].text == to.options[j].text) ){
				i++;
			}
		}

		var o = from.options[i];
		if (i<from.options.length && o.selected) {
			to.options[to.options.length] = new Option( o.text, o.value, false, false);

		}


	}
	// Remove from the source list
	for (var i=(from.options.length-1); i>=0; i--) {
		var o = from.options[i];
		if (o.selected) {
			//from.options[i] = from.options[i];
			from.options[i] = null;
		}
	}

	//reset selections to NONE
	from.selectedIndex = -1;

}

function sortList(list) {
	arrTexts = new Array();
	arrValues = new Array();

	for(i=0; i<list.length; i++)  {
	  arrTexts[i] = list.options[i].text;
	  arrValues[i] = list.options[i].value;
	}

	arrTexts.sort();

	for(i=0; i<list.length; i++)  {
	  list.options[i].text = arrTexts[i];
	  list.options[i].value = arrValues[i];
	}
}

function doSubmit(){
	document.memberattendancerosterform.submit();
}

function addHiddenInput(theForm, name, value) {
	var e = document.createElement('input');
	e.setAttribute('type', 'hidden');
	e.setAttribute('name', name);
	e.setAttribute('value', value);
	var f = document.getElementById(theForm);
	f.appendChild(e);

}

function getSelectList( srcList){
	var srcListElm = document.getElementById(srcList);
	var listArray = new Array(srcListElm.options.length + 1);
	// element 0 in array is the length
	listArray[0] = srcListElm.options.length;
	for (var i=1; i<=srcListElm.options.length; i++){
		listArray[i] = new Array(srcListElm.options[i-1].value, srcListElm.options[i-1].text);
	}
	return listArray;
}

// AJAX support for presenting password and checking
var xmlhttp;
const passcheckReasonReturn = 1;
const passcheckReasonSubmit = 2;
const passcheckReasonDefault = passcheckReasonReturn;
var passcheckReason = passcheckReasonDefault;

// Shows the password div and optionally adds hidden form field
function presentPassword(formName, reason){
	passcheckReason = reason;
	if (reason == passcheckReasonSubmit){
		document.getElementById('submitpass').value="Submit password to log attendees";
		addHiddenInput( formName, 'attendancerosterlistselections', getSelectList( 'attendancerosterlistright'));
	} else {
		document.getElementById('submitpass').value="Submit password and Return";
	}
	var presentpasswordButtonElm = document.getElementById('presentpassword');
	presentpasswordButtonElm.style.visibility = 'hidden';
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
			if (passcheckReason == passcheckReasonSubmit){
				doSubmit();
			} else if (passcheckReason == passcheckReasonReturn) {
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
<?php
$rowCount = 0;
// Set up teamid from session or input
$bError = FALSE;

// teamid depends on who is calling
if ( !isUser($session, Role_ApplicationAdmin)){
	if ( !isset($session["teamid"])){
		$bError = true;
		$err = "A team must be selected.";
	} else {
		$teamid = $session["teamid"];
	}
} else {
	if ( isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	} else {
		$bError = true;
		$err = "A team must be selected, Admin!";
	}
}

if (!AttendanceConsole::isAttendanceConsole($session)){
	redirect($_SERVER['HTTP_REFERER']);
}

if (!$bError) {
	// set up sort order
	$sortRequest = "firstname";
	if (isset($_GET["sort"])) {
		$sortRequest = trim($_GET["sort"]) . "";
		$sortRequest = cleanSQL($sortRequest);
	}

	// $isBillable triggers special features on this page for next payment due
	$isBillable = 0;
	if (isset($_GET["isbillable"]) && is_numeric($_GET["isbillable"])) {
		$isBillable = $_GET["isbillable"];
	}

	// Figure out how the roster should be filtered
	$filterRequest = "";
	if (isset($_GET["filter"])) {
		$filterRequest = trim($_GET["filter"]);
		$filterRequest = cleanSQL($filterRequest);
		if ((strlen($filterRequest) > 0) && (! is_numeric($filterRequest)) ) {
			$filterRequestSQL = " AND " . $filterRequest;
		} else {
			$filterRequestSQL = "";
		}
	} else {
		$filterRequestSQL = "";
	}

	$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.roleid, users.imageid, useraccountinfo.status, useraccountinfo.isbillable, images.* FROM useraccountinfo, teams RIGHT OUTER JOIN images RIGHT OUTER JOIN users ON users.imageid = images.id ON images.teamid = teams.id WHERE users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status = " . UserAccountStatus_Active . " AND users.teamid = ? " . $filterRequestSQL . " ORDER BY " . $sortRequest . ";";

  $dbconn = getConnectionFromSession($session);
  $results = executeQuery($dbconn, $strSQL, $bError, array($teamid));
	$numUsers = count($results);

	// Now that we've done the query, we need to strip the secondary sort column off.
	$sortRequest = substr($sortRequest, 0, strpos($sortRequest, ","));

	// Allow app admin to browse all teams
	if (isUser( $session, Role_ApplicationAdmin)){

		// While it may seem inefficient to query the count of teams every time this page is rendered, this only affects the app admin (me), so tough noogies, Dave.
		$strSQL2 = "SELECT COUNT(*) AS ROW_COUNT FROM teams;";
		$numteams = executeQueryFetchColumn($dbconn, $strSQL, $bError);?>

<div class="navtop">
<ul id="nav">
<li><div align="left"><a id="prev" class="linkopacity"
<?php
		if ($teamid > 1) {?>
href="member-roster.php<?php buildRequiredParams($session) ?>&teamid=<?php echo (int)($teamid-1)?>"
<?php
		}
?>
><img src="img/a_previous.gif" border="0" alt="previous">Previous team</a></div></li>
<li><div align="right"><a id="next" class="linkopacity"
<?php
		if (($teamid+1) <= $numteams) { ?>
href="member-roster.php<?php buildRequiredParams($session) ?>&teamid=<?php echo (int)($teamid+1)?>"
<?php
		}?>
>Next team<img src="img/a_next.gif" border="0" alt="next"></a></div></li>
</ul>
</div>

<?php
	}

	// If none found
	if (count($results) == 0) { ?>
<h3><?php echo $title?></h3>
<?php
		echo "<p>No members found.<br>\n";
// If only one result, go to props page for that user
	} else { ?>
<div id="bodycontent">
<h3><?php echo $title?> for <?php echo getTeamName($teamid, $dbconn)?></h3>
<form action="/1team/member-attendance-roster.php" method="post" name="memberattendancerosterform" id="memberattendancerosterform">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<input type="hidden" name="attendanceroster-orig">
<table class="noborders">
<?php

	if ( isUser($session, Role_ApplicationAdmin) ) {
		$strSQL = "SELECT * FROM events order by listorder;";
		$event_records = executeQuery($dbconn, $strSQL, $bError);
	} else {
		$strSQL = "SELECT * FROM events WHERE teamid = ? order by listorder;";
		$event_records = executeQuery($dbconn, $strSQL, $bError, array($session["teamid"]));
	} ?>
<?php if (isAnyAdminLoggedIn($session)){?>
<tr>
<td class="bold">Date</td>
<td><input type="text" name="date" id="date" value="<?php echo date("Y-m-d")?>" dojoType="dijit.form.DateTextBox" required="true" onchange="document.getElementById('datediv').innerHTML=document.getElementById('date').value"/>
</td></tr>
<?php } else { ?>
<input type="hidden" name="date" value="<?php echo date("Y-m-d")?>"/>
<?php }?>
<tr><td class="bold">Event:</td>
<td><select name="eventid">
<?php
	$rowCount = 0;
	foreach ($event_records as $row) {
		$rowCount++;
		$eventdate = $row["eventdate"];
		if (( strlen($eventdate) < 1 ) || ( is_null($eventdate) )) {
			$eventdate = "any date";
		}
		$location = $row["location"];
		if (( strlen($location) < 1 ) || ( is_null($eventdate) )) {
			$location = "any location";
		}
		echo '<option value="';
		echo $row["id"];
		echo '"';
		echo ">";
		echo $row["name"] . " on " . $eventdate . " at " . $location;
		echo "</option>";
	}
	if ($rowCount < 1) {
		$bError = true;
		$errorStr = "No members found";
	} ?>
</select></td></tr>
</table>
<table class="noborders">
<tr><td class="bigstrongcenter"><?php echo $teamterms["termteam"]?> Roster</td><td></td><td class="bigstrongcenter">Present&nbsp;<div id="datediv"><?php echo date("F jS, Y")?></div></td></tr>
<tr><td>
<select size="<?php
		if ($numUsers < memberSelectionListMaxRows) echo $numUsers;
		else echo memberSelectionListMaxRows;?>" style="width: 300px" multiple="multiple" id="attendancerosterlistleft" name="attendancerosterlistleft">
<?php
		$rowCount = 0;
		$numRows = count($results);
		while ($rowCount < $numRows) {
			$accountStatus = $results[$rowCount]["status"]; ?>
<?php
			echo '<option value="'. $results[$rowCount]["userid"] .'">';
			echo $results[$rowCount][ "firstname"] . "&nbsp;". $results[$rowCount][ "lastname"];
			echo "</option>\n";
			$rowCount++;

		} ?>
</select>
</td>
<td align="center">
<input type="button" class="btnbig" onmouseover="this.className='btnbig btnbighover'" onmouseout="this.className='btnbig'" onclick="moveOptions( 'attendancerosterlistleft', 'attendancerosterlistright', 'attendancerosterlistright', 'presentpassword')" name="present" value="   Present  >  ">
<br>
<br>
<br>
<br>
<br>
<input id="remove" type="button" class="btnbig" onmouseover="this.className='btnbig btnbighover'" onmouseout="this.className='btnbig'" onclick="moveOptions( 'attendancerosterlistright',  'attendancerosterlistleft', 'attendancerosterlistright', 'presentpassword')" name="notpresent" value="< Not present" disabled>
<br>
</td>
<td>
<select size="<?php
		if ($numUsers < memberSelectionListMaxRows) echo $numUsers;
		else echo memberSelectionListMaxRows;?>" multiple="multiple" style="width: 300px" id="attendancerosterlistright"  name="attendancerosterlistright">
</select>
</td>
</tr>
<tr><td></td><td  align="center"><input type="button" class="btn" value="Submit Attendees" name="presentpassword" id="presentpassword" onclick="presentPassword('memberattendancerosterform', passcheckReasonSubmit)" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" disabled/></td><td></td></tr>
</table>
</form>
<input type="button" class="btn" value="Return" name="return" id="return" onclick="presentPassword('memberattendancerosterform', passcheckReasonReturn)" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
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
	$userResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));
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
<tr><td></td><td><input type="button" class="btn" value="Submit Attendees" name="submitpass" id="submitpass" onclick="checkPass(this.form.password.value, <?php echo $session["userid"]?>)" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/></td><td></td></tr>
</table>
</form>
</div>
<div class="hideit" id="errorboxPW">
<div class="errorboxtitle">Error submitting attendees<div class="errorboxclose"><a class="linkopacity" href="javascript:togglevis2('errorboxPW', 'errorboxshow', 'errorboxhide');"><img src="img/x-closebutton.png" border="0"/></a></div><hr /></div>
A <?php echo $teamterms["termadmin"] ." or ".$teamterms["termcoach"] ?> is required to enter their password to submit this form.<br>Error: <div id="passerr"></div>
<br/>
<div class="boxbutton"><input type="button" value="Ok" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onClick="javascript:togglevis2('errorboxPW', 'errorboxshow', 'errorboxhide');"/></div>
</div>
<?php
	}
// Error
} else {
	echo '<p class="error">' . $err . '</p>';
}
if (isset($_GET["err"])){
	showError("Error", "The following attendance roster was not saved successfully:  ".$_GET["err"], "");
} else if (isset($_GET["done"])){
	showMessage("Success", "The following attendance roster was saved successfully: ".$_GET["done"]);
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
