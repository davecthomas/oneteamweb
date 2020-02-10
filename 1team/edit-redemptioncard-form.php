<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Redemption Card";

// Redemption Card (guest pass) support
// This triggers either a "new" or "edit" form
include('header.php');
if ((isset($_GET["id"])) && (is_numeric($_GET["id"]))) {
	$id = $_GET["id"];
	$mode = EditCard;
} else {
	$title = "New Redemption Card";
	$mode = NewCard;
	$id = RedemptionCardID_Unknown;
}


// Default values
$cardTypes = RedemptionCard::getRedemptionCardTypes();
$type = RedemptionCard::TypeGuestPass;
$skuid = Sku::SkuID_Undefined;
$paymentmethod = PaymentMethod_Undefined;
$facevalue = 0.00;
$amountpaid = 0.00;
$numeventsremaining = 0;
$expiresdate = new DateTime(date("d-m-Y") . " +1 year");
$expires = $expiresdate->format("Y-m-d");
$description = "";
$uid = User::UserID_Undefined;
?>
<script type="text/javascript">
dojo.require("dijit.form.DateTextBox");
dojo.require("dijit.form.CurrencyTextBox");
</script>

<?php

$bError = false;

// teamid depends on who is calling
if (isUser($session, Role_TeamAdmin)){
	if (isset($session["teamid"])){
		$teamid = $session["teamid"];
	} else {
		$bError = true;
	}
} else {
	if (isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	} else {
		$bError = true;
	}
}


if (!$bError) {
	if ($mode == EditCard){
		$redemptioncard = new RedemptionCard($session, $id);
		if (! $redemptioncard->isValid()) {
			$mode = NewCard;
			$title = "New Redemption Card";
		} else {
			$type = $redemptioncard->getType();
			$skuid = $redemptioncard->getSkuID();
			$paymentmethod = $redemptioncard->getPaymentMethod();
			$facevalue = $redemptioncard->getFaceValue();
			$amountpaid = $redemptioncard->getAmountPaid();
			$numeventsremaining = $redemptioncard->getNumEventsRemaining();
			$expires = $redemptioncard->getExpires();
			$description = $redemptioncard->getDescription();
			$uid = $redemptioncard->getUserID();
		}
	}?>
<h3><?php	echo $title . " for " . $teaminfo["teamname"]?></h3>
<div class="helpbox">
<div class="helpboxtitle"><h5><a class="linkopacity" href="javascript:togglevis('overview')">Instructions for Redemption Cards<img src="/1team/img/a_expand.gif" id="overview_img" border="0" alt="expand region"></a></h5></div>
<div class="hideit" id="overview">
<div class="helpboxtext">
<p>Redemption cards can be used as gift certificates, guest passes, or for an electronic &quot;punch-card&quot; method for tracking attendance for guests or members for
purchased or gifted services.</p>
<ol>
<li>Print the generated ID Cards page duplex (both sides of the paper). The printouts work best with the Chrome or Firefox browsers. Shift the left margin over by an additional 0.2 inches from the default.</li>
<li>Heat laminate the sheet (FedexKinkos does it for about $4)</li>
<li>Cut the laminated cards and round the sharp corners (again, FedexKinkos has simple tools for this)</li>
<li>Hand out the cards to your members</li>
<li>Get a <a href="http://www.amazon.com/Contact-Barcode-Scanner-Rugged-Design/dp/B000GGTTC8/ref=pd_bbs_sr_1?ie=UTF8&s=electronics&qid=1210964398&sr=8-1" target="_blank">USB Bar Code Reader</a> and plug it into your attendance console's USB port</li>
<li>Log member attendance here: <a href="scan-form.php<?php buildRequiredParams($session) ?>">Start Attendance Scanning</a>, where members pass their ID card under the bar code reader.
</ol>
</div>
</div>
<form id="redemptioncardform" name="redemptioncardform" action="/1team/edit-redemptioncard.php" method="post">
<input type="hidden" name="mode" value="<?php echo $mode ?>"/>
<?php	if ($mode == EditCard){?>
<input type="hidden" name="id" value="<?php echo $id ?>"/>
<?php	}?>
<?php buildRequiredPostFields($session) ?>
<table class="noborders">
<tr><td class="bold" valign="top">Redemption Card Type</td>
<td><input type="radio" name="type" value="<?php echo RedemptionCard::TypeGuestPass?>" onclick="onTypeClicked(this)" <?php if (($mode == EditCard) && ($type == RedemptionCard::TypeGuestPass)) echo "checked"; else if ($mode == NewCard) echo "checked";?>> Guest Pass<br>
<input type="radio" name="type" value="<?php echo RedemptionCard::TypeGiftCard?>" onclick="onTypeClicked(this)" <?php if (($mode == EditCard)  && ($type == RedemptionCard::TypeGiftCard)) echo "checked";?>> Gift Certificate<br>
<input type="radio" name="type" value="<?php echo RedemptionCard::TypeEPunch?>" onclick="onTypeClicked(this)" <?php if (($mode == EditCard)  && ($type == RedemptionCard::TypeEPunch)) echo "checked";?>> Electronic Punch Card
</td></tr>
<tr><td class="bold">Description</td><td><input type="text" id="description" name="description" value="<?php echo $description?>" maxlength="128" size="64"/></td></tr>
<tr><td class="bold">Redeem this card for SKU</td><td>
<?php
	// GEt payment methods for this team
	$strSQL = "SELECT * FROM skus WHERE teamid = ? ORDER BY listorder";
	$dbconn = getConnection();
	$skuResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));
	$rowCountS = count( $skuResults);

	// Display skus for this team
	if ($rowCountS > 0) {
		$countRowsS = 0; ?>
<select name="skuid" onchange="onSkuChanged(this.selectedIndex);">
<option value="<?php echo Sku::SkuID_Undefined?>" <?php if ($skuid == Sku::SkuID_Undefined) echo ' selected'?>>Select SKU...</option>
<?php
		while ($countRowsS < $rowCountS) {
			echo "<option value=\"";
			echo $skuResults[$countRowsS]["id"];
			echo "\"";
			if ( $skuid == $skuResults[$countRowsS]["id"] ) {
				echo(" selected");
			}
			echo ">";
			echo $skuResults[$countRowsS]["name"];
			echo "</option>\n";
			$countRowsS ++;
		} ?>
</select>
<?php
	}  else {
		echo 'No SKUs are defined for ' . $teamname . '. <a href="/1team/manage-skus-form.php?teamid=' . $teamid . buildRequiredParamsConcat($session) . '">Define SKUs</a>.';
	} ?>
</td></tr>
<script type="text/javascript">
var skuID=new Array();
var skuAmount=new Array();
var skuNumEvents=new Array();
skuID[0] = <?php echo Sku::SkuID_Undefined.";\n";?>
skuAmount[0] = 0.00;
skuNumEvents[0] = 0;
<?php
		// build array for javascript
		if ($rowCountS > 0) {
			$countRowsS = 0;
			while ($countRowsS < $rowCountS) {
				echo "skuID[". ($countRowsS+1) . "] = " . $skuResults[$countRowsS]["id"] . ";\n";
				echo "skuAmount[". ($countRowsS+1) . "] = " . $skuResults[$countRowsS]["price"] . ";\n";
				$numEvents = $skuResults[$countRowsS]["numevents"];
				if (is_null($numEvents)) $numEvents = Sku::NumEventsUndefined;
				echo "skuNumEvents[". ($countRowsS+1) . "] = " . $numEvents . ";\n";
				$countRowsS ++;
			}
		}?>

function onSkuChanged( idx){
	var numeventsdiv = document.getElementById('numeventsremaining');
	if (skuNumEvents[idx] == <?php echo Sku::NumEventsUnlimited?>) numEventsTxt = 'Unlimited';
	else if (skuNumEvents[idx] == <?php echo Sku::NumEventsUndefined?>) numEventsTxt = 'Not an attendance event';
	else numEventsTxt = skuNumEvents[idx];
	numeventsdiv.innerHTML = numEventsTxt;
	// Unlike the above field, the hidden field requires a numeric value
	document.redemptioncardform.numevents.value = skuNumEvents[idx];
	var price;
	price = skuAmount[idx];
	dojo.byId('facevalue').value = price.toFixed(2);
	// This is a hack to get the dojo widget to pretty up the auto-entered value
	dojo.byId('facevalue').focus();
	// Now put focus back to the select list
	document.redemptioncardform.skuid.focus();
	submitButtonEnabler('submitform');
}
var cardType=new Array();
<?php
		// Make a javascript array of [cardtype] = Type description string
		echo "cardType[".RedemptionCard::TypeUndefined."] = '".$cardTypes[RedemptionCard::TypeUndefined]."';\n";
		echo "cardType[".RedemptionCard::TypeGiftCard."] = '".$cardTypes[RedemptionCard::TypeGiftCard]."';\n";
		echo "cardType[".RedemptionCard::TypeGuestPass."] = '".$cardTypes[RedemptionCard::TypeGuestPass]."';\n";
		echo "cardType[".RedemptionCard::TypeEPunch."] = '".$cardTypes[RedemptionCard::TypeEPunch]."';\n";
?>
function onTypeClicked( elm){
	var expirationdatediv = document.getElementById('expirationdate');
	expirationdatediv.innerHTML = cardType[elm.value] + " expiration date";

}
</script>
<tr><td class="bold">Face Value ($US)</td><td><input type="text"  id="facevalue" name="facevalue" value="<?php echo $facevalue;?>" dojoType="dijit.form.CurrencyTextBox" required="true" constraints="{fractional:true}" currency="USD" /></td></tr>
<tr><td class="bold">Price Charged ($US)</td><td><input type="text"  id="amountpaid" name="amountpaid" value="<?php echo $amountpaid;?>" dojoType="dijit.form.CurrencyTextBox" required="true" constraints="{fractional:true}" currency="USD"/></td></tr>
<tr><td class="bold">Payment Method</td><td>
<?php
	// GEt payment methods for this team
	$strSQL = "SELECT * FROM paymentmethods WHERE teamid = ? ORDER BY listorder;";
	$paymentmethodResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));
	$rowCountPM	  = count( $paymentmethodResults);

	// Display paymentmethods for this team
	if ($rowCountPM > 0) {
		$countRowsPM = 0; ?>
<select name="paymentmethod">
	<option value="<?php echo PaymentMethod_Undefined?>" <?php if ($mode == NewCard) echo " selected"?>>Select payment method</option>
<?php
		while ($countRowsPM < $rowCountPM) {
			echo "<option value=\"";
			echo $paymentmethodResults[$countRowsPM]["id"];
			echo "\"";
			if ( $paymentmethod == $paymentmethodResults[$countRowsPM]["id"] ) {
				echo(" selected");
			}
			echo ">";
			echo $paymentmethodResults[$countRowsPM]["name"];
			echo "</option>\n";
			$countRowsPM ++;
		} ?>
</select>
<?php
	} else {
		echo 'No payment methods are defined for ' . $teamname . '. <a href="/1team/manage-payment-types-form.php?teamid=' . $teamid . buildRequiredParamsConcat($session) . '">Define payment methods</a>.';
	} ?>
</td></tr>
<tr><td class="bold">Number of attendance events</td><td><input type="hidden" name="numevents" value=""/><div id="numeventsremaining"><?php echo $numeventsremaining;?></div></td></tr>
<tr><td class="bold"><div id="expirationdate"><?php echo $cardTypes[$type] . " expires";?></div></td><td><input type="text" name="expires" id="expires" value="<?php echo $expires;?>" dojoType="dijit.form.DateTextBox" required="true" /></td></tr>
<?php
	if ($mode == NewCard) {?>
<tr><td class="strong">Select group or <?php echo $teamterms["termmember"]?></td>
<td>
<select name="redemptioncardgroup"  onchange="redemptioncardgroupChanged('redemptioncardlistright', 'submitform')"  onkeyup="this.blur();this.focus();">
<option value=<?php echo RedemptionCard::RecipientGroupUndefined?> <?php if (($mode != EditCard) || ($uid == User::UserID_Undefined)) echo " selected"?>>Select a recipient group...</option>
<option value="<?php echo RedemptionCard::RecipientGroupGuest?>" <?php if (($mode == EditCard) && ($uid == User::UserID_Guest)) echo " selected"?>>Non-<?php echo $teamterms["termmember"]?> guest</option>
<option value="<?php echo RedemptionCard::RecipientGroupAllActiveMembers?>">All active <?php echo $teamterms["termmember"]?>s</option>
<option value="<?php echo RedemptionCard::RecipientGroupArbitrarySelection?>" <?php if (($mode == EditCard) && (($uid != User::UserID_Undefined) && ($uid != User::UserID_Guest))) echo " selected"?>>Select <?php echo $teamterms["termmember"]?>s from list</option>
<option value="<?php echo RedemptionCard::RecipientGroupNewMembers?>" ><?php echo $teamterms["termmember"]?>s joined in the past month</option>
<option value="<?php echo RedemptionCard::RecipientGroupNonParticipants?>" ><?php echo $teamterms["termmember"]?>s who have not purchased services in a given program</option>
<option value="<?php echo RedemptionCard::RecipientGroupActiveParticipants?>" ><?php echo $teamterms["termmember"]?>s who have purchased services in a given program</option>
<option value="<?php echo RedemptionCard::RecipientGroupRecentlyExpired?>" ><?php echo $teamterms["termmember"]?>s who have had services expire in the past month</option>
<option value="<?php echo RedemptionCard::RecipientGroupPastMembers?>" >Past <?php echo $teamterms["termmember"]?>s (no longer active with <?php echo $teaminfo["teamname"]?>)</option>
</select>
</td>
</tr>
<?php
	} ?>
</table>
<script type="text/javascript">
function submitButtonEnabler(submitButton){
	var submitButtonElm = document.getElementById(submitButton);
	// If there is a SKU selected
	if ((document.redemptioncardform.skuid.value != <?php echo Sku::SkuID_Undefined?>) &&
		// And a recipient group is picked
		(document.redemptioncardform.redemptioncardgroup.value != <?php echo RedemptionCard::RecipientGroupUndefined?>)){

		var groupval = document.redemptioncardform.redemptioncardgroup.value
		// If the recipient group requires a program selection
		if ((groupval == <?php echo RedemptionCard::RecipientGroupNonParticipants?>) ||
			(groupval == <?php echo RedemptionCard::RecipientGroupActiveParticipants?>) ||
			(groupval == <?php echo RedemptionCard::RecipientGroupRecentlyExpired?>)){
			// Program is selected
			if (document.getElementById('programid').options.selectedIndex != <?php echo Program_Undefined?>)
				submitButtonElm.disabled=false;
			else
				submitButtonElm.disabled=true;
		} else {
			submitButtonElm.disabled=false;
		}
	} else
		submitButtonElm.disabled=true;
}
// If the recipient list has > 0 members, or pre-defined list selected, enable the submit button
function redemptioncardgroupChanged(listToEnableSubmit, submitButton){
	var submitButtonElm = document.getElementById(submitButton);
	var listElm = document.getElementById(listToEnableSubmit);

	switch (Number(document.redemptioncardform.redemptioncardgroup.value)){
		case Number(<?php echo RedemptionCard::RecipientGroupUndefined?>):
			hideit('rosterdiv');
			hideit('programselectordiv');
			hideit('nonparticipantprogramselectordiv');
			hideit('participantprogramselectordiv');
			submitButtonEnabler(submitButton);
			break;

		case Number(<?php echo RedemptionCard::RecipientGroupArbitrarySelection?>):
			showit('rosterdiv');
			hideit('programselectordiv');
			hideit('nonparticipantprogramselectordiv');
			hideit('participantprogramselectordiv');
			hideit('recentlyexpiredselectordiv');
			if (listElm.options.length > 0) {
				submitButtonEnabler(submitButton);
			} else {
				submitButtonElm.disabled=true;
			}
		break;

		// For these cases, we need to show a program selector
		case Number(<?php echo RedemptionCard::RecipientGroupNonParticipants?>):
			showit('nonparticipantprogramselectordiv');
			showit('programselectordiv');
			hideit('participantprogramselectordiv');
			hideit('recentlyexpiredselectordiv');
			hideit('rosterdiv');
			document.getElementById('programid').options.selectedIndex = <?php echo Program_Undefined?>;
			submitButtonElm.disabled=true;
			break;

		case Number(<?php echo RedemptionCard::RecipientGroupActiveParticipants?>):
			showit('participantprogramselectordiv');
			showit('programselectordiv');
			hideit('nonparticipantprogramselectordiv');
			hideit('recentlyexpiredselectordiv');
			hideit('rosterdiv');
			submitButtonElm.disabled=true;
			document.getElementById('programid').options.selectedIndex = <?php echo Program_Undefined?>;
			break;

		// For this case, we need to show a SKU selector
		case Number(<?php echo RedemptionCard::RecipientGroupRecentlyExpired?>):
			showit('recentlyexpiredselectordiv');
			showit('programselectordiv');
			hideit('participantprogramselectordiv');
			hideit('nonparticipantprogramselectordiv');
			hideit('rosterdiv');
			submitButtonElm.disabled=true;
			document.getElementById('programid').options.selectedIndex = <?php echo Program_Undefined?>;
			break;

		// No selector required.
		case Number(<?php echo RedemptionCard::RecipientGroupAllActiveMembers?>):
		case Number(<?php echo RedemptionCard::RecipientGroupNewMembers?>):
		case Number(<?php echo RedemptionCard::RecipientGroupPastMembers?>):
			hideit('programselectordiv');
			hideit('recentlyexpiredselectordiv');
			hideit('nonparticipantprogramselectordiv');
			hideit('rosterdiv');
			submitButtonEnabler(submitButton);
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
		submitButtonEnabler(submitButton);
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
	addHiddenInput( theForm, 'redemptioncardlistselections', getSelectList( 'redemptioncardlistright'));
	document.forms[theForm].submit();
}

// Adds a field of redemption card selections
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
<?php
	// This is the conditionally displayed div for the member selection list
	if (($mode == EditCard) && (($uid != User::UserID_Undefined) && ($uid != User::UserID_Guest))) {
		// set up sort order
		$sortRequest = "firstname";
		if (isset($_GET["sort"])) {
			$sortRequest = trim($_GET["sort"]) . "";
			$sortRequest = cleanSQL($sortRequest);
		}

		$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.smsphone, users.smsphonecarrier, useraccountinfo.email FROM users, useraccountinfo WHERE users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status = " . UserAccountStatus_Active . " AND users.teamid = ? ORDER BY firstname;";
		$results = executeQuery($dbconn, $strSQL, $bError, array($teamid));
<div id="rosterdiv">
<h4>Select From List</h4>
<p>Selected <?php echo $teamterms["termmember"]?>s.</p>
<table class="noborders">
<tr><td class="bigstrongcenter"><?php echo $teamterms["termteam"]?> Roster</td><td></td><td class="bigstrongcenter">Create cards for</td></tr>
<tr><td>
<?php
		$rowCount = 0;
		$numRows = count($results);
		$EditCardUserName = "";
		if ($numRows > 0){?>
<select size="<?php
			if ($numRows < memberSelectionListMaxRows) echo $numRows;
			else echo memberSelectionListMaxRows;?>" style="width: 300px" multiple="multiple" id="redemptioncardlistleft" name="redemptioncardlistleft">
<?php
			while ($rowCount < $numRows) {
				$rowuserid = $results[$rowCount]["userid"];
				if (($mode == NewCard) || (($mode == EditCard) && ($uid != $rowuserid ))){
					echo '<option value="'. $rowuserid  .'">';
					echo $results[$rowCount][ "firstname"] . "&nbsp;". $results[$rowCount][ "lastname"];
					echo "</option>\n";
				} else
					$EditCardUserName = $results[$rowCount][ "firstname"] . "&nbsp;". $results[$rowCount][ "lastname"];
				$rowCount++;

			} ?>
</select>
<?php	} else {?>
<p>No <?php echo $teamterms["termmember"]?>s exist in the team <br>
<a href="/1team/new-user-form.php?'<?php echo returnRequiredParams($session) ?>">Create a <?php echo $teamterms["termmember"]?></a></p>
<?php	}?>
</td>
<td align="center">
<input type="button" class="btnbig" onmouseover="this.className='btnbig btnbighover'" onmouseout="this.className='btnbig'" onclick="moveOptions( 'redemptioncardlistleft', 'redemptioncardlistright', 'redemptioncardlistright', 'submitform')" name="present" value="   To  >  ">
<br>
<br>
<br>
<br>
<br>
<input type="button" id="remove" class="btnbig" onmouseover="this.className='btnbig btnbighover'" onmouseout="this.className='btnbig'" onclick="moveOptions( 'redemptioncardlistright',  'redemptioncardlistleft', 'redemptioncardlistright', 'submitform')" name="notpresent" value="< Remove" disabled>
<br>
</td>
<td>
<select size="<?php
		if ($numRows < memberSelectionListMaxRows) echo $numRows;
		else echo memberSelectionListMaxRows;?>" multiple="multiple" style="width: 300px" id="redemptioncardlistright"  name="redemptioncardlistright">
<?php
		if (($mode == EditCard) && ($uid != User::UserID_Undefined)) {
			echo '<option value="'. $uid .'" selected>';
			echo $EditCardUserName;
			echo "</option>\n";
		}?>
</select>
</td>
</tr>
</table>
</div>
<?php
		// End roster div
	}
	// This is the conditionally displayed div for the program selector list ?>
<div id="nonparticipantprogramselectordiv" class="hideit">
<h4>Program Non-participants</h4>
<p>Create redemption cards for <?php echo $teamterms["termmember"]?>s that do not participate in the selected program.</p>
</div>
<div id="participantprogramselectordiv" class="hideit">
<h4>Program Participants</h4>
<p>Create redemption cards for <?php echo $teamterms["termmember"]?>s that participate in the selected program.</p>
</div>
<div id="recentlyexpiredselectordiv" class="hideit">
<h4>Recently Expired Program Payments or No Remaining Classes</h4>
<p>Create redemption cards for <?php echo $teamterms["termmember"]?>s that previously participated in the selected program but their payment has expired or they have used their last <?php echo $teamterms["termclass"]?>.</p>
</div>
<div id="programselectordiv" class="hideit">
<table class="noborders">
<tr>
<td class="strong">Program</td>
<td><?php
		$strSQL = "SELECT * FROM programs WHERE teamid = ? ORDER BY name;";
		$executeQuery($dbconn, $strSQL, $bError, array($teamid));

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
<input type="button" value="<?php if ($mode == EditCard) echo "Save"; else echo "Create"?>" name="submitform" id="submitform" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" <?php if ($mode == NewCard) echo " disabled"?> onclick="doSubmit('redemptioncardform')"/>&nbsp;<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = '<?php echo $_SERVER["HTTP_REFERER"]?>'"/>
</form>
</div>
<?php
}
if ($bError) {
	echo '<p class="error">' . $err . '</p>';
}
if (isset($_GET["err"])){
	showError("Error", "Redemption cards not created.  ".$_GET["err"], "");
}

// Start footer section
include('footer.php'); ?>
</body>
</html>
