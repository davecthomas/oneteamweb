<?php
// Only admins and coaches can execute this script. Header.php enforces this.
$isadminorcoachrequired = true;

if (isset($_REQUEST["sms"])){
	$sms = true;
	$title = "Send Text Message";
	$textemail = "text";
} else {
	$title = "Send Email Message";
	$sms = false;
	$textemail = "email";
}

include('header.php');
$dbh = getDBH($session);  

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
	if ( isset($_REQUEST["teamid"])){
		$teamid = $_REQUEST["teamid"];
	} else {
		$bError = true;
		$err = "t";
	}
}


if (!$bError){?>
<div id="bodycontent">
<h3><?php echo $title . " to " .$teamterms["termmember"]?>s</h3>
<div class="helpbox">
<div class="helpboxtitle"><h5><a class="linkopacity" href="javascript:togglevis('overview');">Help with <?php echo $textemail?>s<img src="/1team/img/a_expand.gif" id="overview_img" border="0" alt="expand region"></a></h5></div>
<div class="hideit" id="overview">
<div class="helpboxtext">
<?php
	if ($sms){?>
<p>Send a short text message to your <?php echo $teamterms["termmember"]?>s (maximum <?php echo smsTextLimit?> characters). Each <?php echo $teamterms["termmember"]?> must have a mobile phone number and wireless carrier stored in their profile.</p>
<?php } else {?>
<p>You only need to include the main message text. <?php appname?> will automatically add the member's first name at the beginning of the message and your full name at the end. Each <?php echo $teamterms["termmember"]?> must have an email address stored in their profile.</p>
<?php }?>
</div></div></div>
<form name="emailteamform" id="emailteamform" action="/1team/email-team.php" method="post" name="emailteamform" id="emailteamform">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<input type="hidden" name="sms" value="<?php if ($sms) echo "1"; else echo "0"?>">
<table class="noborders">
<?php if (!$sms){?>
<tr>
<td class="strong">Subject</td>
<td ><input type="text" value="" name="subject" size="120"></td>
</tr>
<?php }?>
<tr>
<td class="strong" valign="top">Message</td>
<td><textarea rows="5" cols="120" value="" name="message" wrap="hard"></textarea></td></tr>
<tr><td class="strong" valign="top">Send me a copy</td><td><input type="checkbox" name="sendmecopy" id="sendmecopy" checked/></td></tr>
<tr><td class="strong">Select recipient group</td>
<td>
<select name="recipientgroup"  onchange="recipientGroupChanged('emailrecipientlistright', 'submitform')"  onkeyup="this.blur();this.focus();">
<option value=<?php echo emailRecipientGroupUndefined?> selected>Select a recipient group...</option>
<option value="<?php echo emailRecipientGroupAllActiveMembers?>" >All active <?php echo $teamterms["termmember"]?>s</option>
<option value="<?php echo emailRecipientGroupArbitrarySelection?>" >Select <?php echo $teamterms["termmember"]?>s from list</option>
<option value="<?php echo emailRecipientGroupNewMembers?>" ><?php echo $teamterms["termmember"]?>s joined in the past month</option>
<option value="<?php echo emailRecipientGroupNonParticipants?>" ><?php echo $teamterms["termmember"]?>s who have not purchased services in a given program</option>
<option value="<?php echo emailRecipientGroupActiveParticipants?>" ><?php echo $teamterms["termmember"]?>s who have purchased services in a given program</option>
<option value="<?php echo emailRecipientGroupRecentlyExpired?>" ><?php echo $teamterms["termmember"]?>s who have had services expire in the past month</option>
<option value="<?php echo emailRecipientGroupPastMembers?>" >Past <?php echo $teamterms["termmember"]?>s (no longer active with <?php echo $teaminfo["teamname"]?>)</option>
</select>
</td>
</tr>
</table>
<script type="text/javascript">
// If the recipient list has > 0 members, or pre-defined list selected, enable the submit button
function recipientGroupChanged(listToEnableSubmit, submitButton){
	var submitButtonElm = document.getElementById(submitButton);
	var listElm = document.getElementById(listToEnableSubmit);

	switch (Number(document.emailteamform.recipientgroup.value)){
		case Number(<?php echo emailRecipientGroupUndefined?>):
			hideit('rosterdiv');
			hideit('programselectordiv');
			hideit('nonparticipantprogramselectordiv');
			hideit('participantprogramselectordiv');
			hideit('recentlyexpiredselectordiv');
			submitButtonElm.disabled=true;
			break;
		case Number(<?php echo emailRecipientGroupArbitrarySelection?>):
			showit('rosterdiv');
			hideit('programselectordiv');
			hideit('nonparticipantprogramselectordiv');
			hideit('participantprogramselectordiv');
			hideit('recentlyexpiredselectordiv');
			if (listElm.options.length > 0){
				submitButtonElm.disabled=false;
			} else {
				submitButtonElm.disabled=true;
			}
		break;

		// For these cases, we need to show a program selector
		case Number(<?php echo emailRecipientGroupNonParticipants?>):
			showit('nonparticipantprogramselectordiv');
			showit('programselectordiv');
			hideit('participantprogramselectordiv');
			hideit('rosterdiv');
			hideit('recentlyexpiredselectordiv');
			document.getElementById('programid').options.selectedIndex = <?php echo Program_Undefined?>;
			submitButtonElm.disabled=true;
			break;

		case Number(<?php echo emailRecipientGroupActiveParticipants?>):
			showit('participantprogramselectordiv');
			showit('programselectordiv');
			hideit('recentlyexpiredselectordiv');
			hideit('nonparticipantprogramselectordiv');
			hideit('rosterdiv');
			document.getElementById('programid').options.selectedIndex = <?php echo Program_Undefined?>;
			submitButtonElm.disabled=true;
			break;

		// For this case, we need to show a SKU selector
		case Number(<?php echo emailRecipientGroupRecentlyExpired?>):
			showit('recentlyexpiredselectordiv');
			showit('programselectordiv');
			hideit('nonparticipantprogramselectordiv');
			hideit('participantprogramselectordiv');
			hideit('rosterdiv');
			document.getElementById('programid').options.selectedIndex = <?php echo Program_Undefined?>;
			submitButtonElm.disabled=true;
			break;

		// No selector required.
		case Number(<?php echo emailRecipientGroupAllActiveMembers?>):
		case Number(<?php echo emailRecipientGroupNewMembers?>):
		case Number(<?php echo emailRecipientGroupPastMembers?>):
			hideit('programselectordiv');
			hideit('recentlyexpiredselectordiv');
			hideit('nonparticipantprogramselectordiv');
			hideit('rosterdiv');
			hideit('participantprogramselectordiv');
			submitButtonElm.disabled=false;
			break;
		default:
			hideit('recentlyexpiredselectordiv');
			hideit('nonparticipantprogramselectordiv');
			hideit('rosterdiv');
			hideit('programselectordiv');
			submitButtonElm.disabled=true;
		break;
	}

}
//
function programSelectionChanged(listToEnableSubmit, submitButton){
	var submitButtonElm = document.getElementById(submitButton);
	var listElm = document.getElementById(listToEnableSubmit);
	// If there is a real program selected
	if (listElm.options.selectedIndex != <?php echo Program_Undefined?>){
		submitButtonElm.disabled=false;
	} else {
		submitButtonElm.disabled=true;
	}

}
function moveOptions( srcList, destList, ListToEnableSubmit,submitButton )
{
	var srcListElm = document.getElementById(srcList);
	var destListElm = document.getElementById(destList);
	var ListToEnableSubmitElm = document.getElementById(ListToEnableSubmit);
	var submitButtonElm = document.getElementById(submitButton);
	moveSelectedOptions(srcListElm, destListElm);
	sortList(srcListElm);
	sortList(destListElm);
	// Check the list to enable for length then enable/disable submit
	var removeButtonElm;
	removeButtonElm = document.getElementById('remove')
	if (ListToEnableSubmitElm.options.length > 0) {
		submitButtonElm.disabled=false;
		removeButtonElm.disabled=false;
	}
	else {
		submitButtonElm.disabled=true;
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
function doSubmit(theForm){
	addHiddenInput( theForm, 'emailrecipientlistselections', getSelectList( 'emailrecipientlistright'));
	document.forms[theForm].submit();
}

// Adds a field of email address selections
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
	for (var i=1; i<=srcListElm.options.length; i++){
		listArray.push(srcListElm.options[i-1].value);
	}
	return listArray;
}
</script>
<?php // This is the conditionally displayed div for the member selection list
	// set up sort order
	$sortRequest = "firstname";
	if (isset($_GET["sort"])) {
		$sortRequest = trim($_GET["sort"]) . "";
		$sortRequest = cleanSQL($sortRequest);
	}

	$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email FROM users, useraccountinfo WHERE users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status = " . UserAccountStatus_Active . " AND users.teamid = ? ORDER BY firstname;";
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array($teamid));
	$results = $pdostatement->fetchAll();?>
<div class="hideit" id="rosterdiv">
<h4>Select From List</h4>
<p>Send <?php echo $textemail?> to the selected <?php echo $teamterms["termmember"]?>s.</p>
<table class="noborders">
<tr><td class="bigstrongcenter"><?php echo $teamterms["termteam"]?> Roster</td><td></td><td class="bigstrongcenter">Send <?php echo $textemail?> to</td></tr>
<tr><td>
<?php
	$rowCount = 0;
	$numRows = count($results);
	if ($numRows > 0){?>
<select size="<?php
		if ($numRows < memberSelectionListMaxRows) echo $numRows;
		else echo memberSelectionListMaxRows;?>" style="width: 300px" multiple="multiple" id="emailrecipientlistleft" name="emailrecipientlistleft">
<?php
		while ($rowCount < $numRows) {
			$accountStatus = $results[$rowCount]["status"];
			echo '<option value="'. $results[$rowCount]["userid"] .'">';
			echo $results[$rowCount][ "firstname"] . "&nbsp;". $results[$rowCount][ "lastname"];
			echo "</option>\n";
			$rowCount++;

		} ?>
</select>
<?php } else {?>
<p>No <?php echo $teamterms["termmember"]?>s exist in the team <br>"
<a href="/1team/new-user-form.php?'<?php echo returnRequiredParams($session) ?>">Create a <?php echo $teamterms["termmember"]?></a></p>
<?php }?>
</td>
<td align="center">
<input type="button" class="btnbig" onmouseover="this.className='btnbig btnbighover'" onmouseout="this.className='btnbig'" onclick="moveOptions( 'emailrecipientlistleft', 'emailrecipientlistright', 'emailrecipientlistright', 'submitform')" name="present" value="   To  >  ">
<br>
<br>
<br>
<br>
<br>
<input type="button" id="remove" class="btnbig" onmouseover="this.className='btnbig btnbighover'" onmouseout="this.className='btnbig'" onclick="moveOptions( 'emailrecipientlistright',  'emailrecipientlistleft', 'emailrecipientlistright', 'submitform')" name="notpresent" value="< Remove" disabled>
<br>
</td>
<td>
<select size="<?php
	if ($numRows < memberSelectionListMaxRows) echo $numRows;
	else echo memberSelectionListMaxRows;?>" multiple="multiple" style="width: 300px" id="emailrecipientlistright"  name="emailrecipientlistright">
</select>
</td>
</tr>
</table>
</div>
<?php // This is the conditionally displayed div for the program selector list ?>
<div id="nonparticipantprogramselectordiv" class="hideit">
<h4>Program Non-participants</h4>
<p>Send <?php echo $textemail?> to <?php echo $teamterms["termmember"]?>s that do not participate in the selected program.</p>
</div>
<div id="participantprogramselectordiv" class="hideit">
<h4>Program Participants</h4>
<p>Send <?php echo $textemail?> to <?php echo $teamterms["termmember"]?>s that participate in the selected program.</p>
</div>
<div id="recentlyexpiredselectordiv" class="hideit">
<h4>Recently Expired Program Payments or No Remaining Classes</h4>
<p>Send <?php echo $textemail?> to <?php echo $teamterms["termmember"]?>s that previously participated in the selected program but their payment has expired or they have used their last <?php echo $teamterms["termclass"]?>.</p>
</div>
<div id="programselectordiv" class="hideit">
<table class="noborders">
<tr>
<td class="strong">Program</td>
<td><?php
		$strSQL = "SELECT * FROM programs WHERE teamid = ? ORDER BY name;";
		$pdostatement = $dbh->prepare($strSQL);
		$pdostatement->execute(array($teamid));

		$programsResults = $pdostatement->fetchAll();

		$rowCount = 0;
		$loopMax = count($programsResults);
		if ($loopMax > 0){ ?>
<select name="programid" id="programid" onchange="programSelectionChanged('programid', 'submitform')">
<option value="<?php echo Program_Undefined?>" selected>Select a program...</option>
<?php
			while ($rowCount < $loopMax) {
				echo  "<option ";
				echo  'value="' . $programsResults[$rowCount]["id"] . '"';
				echo  ">";
				echo $programsResults[$rowCount]["name"];
				echo  "</option>";
				$rowCount++;
			}?>
</select>
<?php

		} else {
			echo 'No programs are defined for ' . $teaminfo["teamname"] . '. <a href="/1team/manage-programs-form.php?teamid=' . $teamid . buildRequiredParamsConcat($session) . '">Define programs</a>.';
		} ?>
</td>
</tr>
</table>
</div>
<input type="button" value="Send" name="submitform" id="submitform" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" disabled onclick="doSubmit('emailteamform')"/>&nbsp;<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = '<?php echo $_SERVER["HTTP_REFERER"]?>'"/>
</form>
<?php
// Error
} else {
	echo '<p class="error">' . $err . '</p>';
}
if (isset($_GET["err"])){
	showError("Error", "Email not sent.  ".$_GET["err"], "");
} else if (isset($_GET["done"])){
	showMessage("Success", "The following " . $teamterms["termmember"] ."s were sent ". $textemail.": ".$_GET["done"]);
}
// Start footer section
include('footer.php'); ?>
</body>
</html>