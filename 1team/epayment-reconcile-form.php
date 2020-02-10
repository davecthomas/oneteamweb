<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "ePayment Reconciler";
include('header.php');
echo "<h3>" . getTitle($session, $title) . "</h3>";

$bError = false;
$teamid = NotFound;
$err = "";
// teamid depends on who is calling
if (isUser($session, Role_TeamAdmin)){
	$teamid = getTeamID($session);
} else {
	if (isset($_REQUEST["teamid"])){
		$teamid = $_REQUEST["teamid"];
	} else{
		$bError = true;
		$err = "t";
	}
}
$objname = getTeamName($teamid, $dbconn);

// set up sort order
$sortRequest = "firstname";
if (isset($_GET["sort"])) {
	$sortRequest = trim($_GET["sort"]) . "";
	$sortRequest = cleanSQL($sortRequest);
}

if (!$bError){
	$teamname = getTeamName($teamid, $dbconn);

	// All new orderitems
	$strSQL = "select * from epayments where reconciled <> TRUE and teamid = ? ORDER BY date DESC";
	$dbconn = getConnection();
	$results = executeQuery($dbconn, $strSQL, $bError, array($teamid));
	$numPayments = count($results);

	// Recognized SKUs?>
<h4>Unreconciled ePayments for <?php echo $teamname?></h4>
<form >
<table class="memberlist">
<thead class="head">
<tr>
<th valign="top">Date</th>
<th valign="top">Transaction ID</th>
<th valign="top">Payer Email</th>
<th valign="top">For Account</th>
<th valign="top">SKU</th>
<th valign="top">Description</th>
<th valign="top">Gross</th>
<th valign="top">Fee</th>
<th valign="top">Net</th>
<?php
	if (isAnyAdminLoggedIn($session)) { ?>
<th valign="top" colspan="3">Actions</th>
<?php
	}?>
</tr>
</thead>
<tbody>
<?php
	$rowCountPayments = 0;
	$sumFees = 0;
	$sumGross = 0;
	$sumNet = 0;

	while ($rowCountPayments < $numPayments) {
		$canReconcile = true;	// If any value is detected as unknown, below, we'll change this
		$canEdit = false;		// If any value is detected as unknown, below, we'll change this
		$epayid = $results[$rowCountPayments]["id"];
		$amount = $results[$rowCountPayments]["amount"];
		$txn_id = $results[$rowCountPayments]["txid"];
		$payeremail = $results[$rowCountPayments]["payeremail"];
		$payeremailupper = strtoupper($payeremail);
		$skuname = $results[$rowCountPayments]["skuname"];
		$fee = - $results[$rowCountPayments]["fee"];
		$item = $results[$rowCountPayments]["item"];
		$paymentdate = $results[$rowCountPayments]["date"];
		$uid = $results[$rowCountPayments]["userid"];	// Get this out just in case it's a guest user

		// Get the user for this payment. May be more than one account with the same email. Give a selector if so
		if ($uid != User::UserID_Guest){
			$strSQL = "select useraccountinfo.userid, useraccountinfo.status, users.firstname, users.lastname from users, useraccountinfo, epayments WHERE users.id = useraccountinfo.userid AND epayments.id = ? AND UPPER(epayments.payeremail) = ? and UPPER(epayments.payeremail) = UPPER(useraccountinfo.email);";
			$resultsUserSelect = executeQuery($dbconn, $strSQL, $bError, array($epayid, $payeremailupper));
			$numUsers = count($resultsUserSelect );
		} else $numUsers = 1;

		// Get the sku for this payment. Give a selector if you can't decide
		$strSQL = "SELECT programs.name AS programs_name, skus.*, skus.id as skuid FROM epayments INNER JOIN (programs INNER JOIN skus on programs.id = skus.programid) ON epayments.skuname = skus.name WHERE epayments.teamid = ? AND epayments.id = ?";
		$resultsSkuEpayment = executeQuery($dbconn, $strSQL, $bError, array($teamid, $epayid));
		$numSkusInEpayment = count($resultsSkuEpayment );

		// Get all SKUs for selector
		$strSQL = "SELECT * FROM skus WHERE teamid = ? ORDER BY listorder";
		$resultsSkuSelect = executeQuery($dbconn, $strSQL, $bError, array($teamid));
		$numSkus = count( $resultsSkuSelect);


?>
<tr class="<?php
		if ( ($rowCountPayments+1) % 2 ) echo("even");
		else echo("odd");?>">
<td><?php
	 	echo $results[$rowCountPayments]["date"]?></td>
<td><?php
		if (!empty($txn_id)){
			echo '<a href="pp\TransactionDetails.php?' . returnRequiredParams($session) . '&teamid=' . $teamid . '&transactionID=' . $txn_id .'">';
			echo $txn_id . "</a>";
		} else {
			$canReconcile = false;
			$canEdit = true;
		}?></td>
<td><a href="mailto:<?php echo $payeremail?>?Subject=Payment%20<?php echo $txn_id = $results[$rowCountPayments]["txid"]?>%20on%20date%20<?php echo $paymentdate?>"><?php echo $payeremail?></a></td>
<td>
<?php
		if ($numUsers == 1) {
			if ($uid != User::UserID_Guest){
				$uid = $resultsUserSelect[0][ "userid"];?>
<a href="user-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $resultsUserSelect[0][ "userid"]?>"><span <?php echo subdueInactive($resultsUserSelect[0]["status"])?>><?php echo $resultsUserSelect[0][ "firstname"]?>&nbsp;<?php echo $resultsUserSelect[0][ "lastname"] ?></span></a>
<?php
			} else echo User::Username_Guest;
		} else if ($numUsers > 1){
			$canReconcile = false;
			$canEdit = true;
			$uid = $results[$rowCountPayments]["userid"]; ?>
<select name="uid<?php echo $rowCountPayments?>" id="uid<?php echo $rowCountPayments?>" onchange="if (this.selectedIndex != 0) showit('editepaybutton<?php echo $rowCountPayments?>'); else hideit('editepaybutton<?php echo $rowCountPayments?>');">
<option value="<?php echo User::UserID_Undefined?>" selected>Select a <?php echo $teamterms["termmember"]?>...</option>
<?php
			$rowCountUser = 0;
			while ($rowCountUser < $numUsers) {
				echo '<option value="' . $resultsUserSelect[$rowCountUser][ "userid"] . '"';
				if (!empty($results[$rowCountPayments]["userid"]))
					if ($resultsUserSelect[$rowCountUser][ "userid"] == $results[$rowCountPayments]["userid"]) {
						echo " selected";
						// The fact that we know which user is here allows us to reconcile this. It may get overridden in sku section
							$canReconcile = true;						}
				echo ">";
				echo $resultsUserSelect[$rowCountUser][ "firstname"] . " " . $resultsUserSelect[$rowCountUser][ "lastname"];
				if ($resultsUserSelect[$rowCountUser]["status"] == UserAccountStatus_Inactive)
					echo " (" . $aStatus[UserAccountStatus_ArrayOffset+$resultsUserSelect[$rowCountUser]["status"]] . ")";
				echo "</option>";
				$rowCountUser++;
			} ?>
</select>
<?php
		// No users match
		} else if ($numUsers == 0){
			$canReconcile = false;
			$canEdit = true;
			$uid = User::UserID_Undefined;
			// Removed "isbillable = true and " so I can take orders from non-billable students
			$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid FROM users, useraccountinfo WHERE status = " . UserAccountStatus_Active . " and users.teamid = ? and users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
			$userResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));
			$countRows = 0;
			$numUserRows = count($userResults);

			// If no members, tell them so and don't display the selector
			if ($numUserRows == 0) {
				echo "<p>No " . $teamterms["termmember"]. "s exist in the team " .$teamname . "<br>\n";
				echo '<a href="/1team/new-user-form.php?' . returnRequiredParams($session) . '&roleid='. Role_Member.'">Create a new ' . $teamterms["termmember"].'</a></p>';
				echo "\n";
				$bDisplayUserSelector = false;
			} else $bDisplayUserSelector = true;

			if ($bDisplayUserSelector) {
				echo '<a href="/1team/new-user-form.php?' . returnRequiredParams($session) . '&roleid='. Role_Member.'">Create a new ' . $teamterms["termmember"].'</a> or <br>'; ?>
<select id="uid<?php echo $rowCountPayments?>" name="uid<?php echo $rowCountPayments?>" onchange="if (this.selectedIndex != 0) showit('editepaybutton<?php echo $rowCountPayments?>'); else hideit('editepaybutton<?php echo $rowCountPayments?>');">
<option value="0" selected>Select a <?php echo $teamterms["termmember"]?>&hellip;</option>
<option value="<?php echo User::UserID_Guest . "\">Non-"  . $teamterms["termmember"] . " (" . User::Username_Guest . ")";?></option>
<?php			while ($countRows < $numUserRows) {
					echo "<option value=\"";
					echo $userResults[$countRows]["id"];
					echo "\"";
					if ( $uid == $userResults[$countRows]["id"] ) {
						echo("selected");
						$programid_select = $userResults[$countRows]["programid"];
					}
					echo ">";
					echo $userResults[$countRows]["firstname"];
					echo " ";
					echo $userResults[$countRows]["lastname"];
					echo " " . roleToStr($userResults[$countRows]["roleid"], $teamterms);
					echo "</option>\n";
					$countRows ++;
				} ?>
</select>
<?php
			}
		} ?>
</td>
<td>
<?php
		if ($numSkusInEpayment == 1) {
			$skuid = $resultsSkuEpayment[0][ "skuid"];?>
<a href="edit-sku-form.php?<?php echo returnRequiredParams($session) . '&teamid=' . $teamid . '&id=' . $resultsSkuEpayment[0]["skuid"] .'">' . $resultsSkuEpayment[0]["name"] . "</a>";
		} else {
			$skuid = Sku::SkuID_Undefined;
			$canEdit = true;
			$canReconcile = false; ?>
<select name="skuid<?php echo $rowCountPayments?>" id="skuid<?php echo $rowCountPayments?>" onchange="if (this.selectedIndex != 0) showit('editepaybutton<?php echo $rowCountPayments?>'); else hideit('editepaybutton<?php echo $rowCountPayments?>');">
<option value="<?php echo Sku::SkuID_Undefined?>" selected>Select a SKU...</option>
<?php
			$rowCountSkus = 0;
			while ($rowCountSkus < $numSkus) {
				echo '<option value="' . $resultsSkuSelect[$rowCountSkus]["id"] . '">';
				echo $resultsSkuSelect[$rowCountSkus][ "name"];
				echo "</option>";
				$rowCountSkus++;
			}
?>
</select>
<?php
		}
?>
</td>
<td><?php
		$item = $results[$rowCountPayments]["item"];
		if (strlen($item) > ePaymentItemDisplayLength){
			echo substr($item, 0, ePaymentItemDisplayLength-1) . "...";
		} else {
			echo $results[$rowCountPayments]["item"];
		}?>
</td>
<td><?php
		if ($amount < 0) echo '<span class="debit">';
		echo formatMoney($amount);
		$sumGross += $amount;
		if ($amount < 0) echo '</span>';?>
</td>
<td><?php
		echo '<span class="debit">';
		$sumFees += $fee;
		echo formatMoney($fee);
		echo '</span>';?></td>
<td><?php
		if ($amount < 0) echo '<span class="debit">';
		$sumNet += $amount+$fee;
		echo formatMoney( $amount+$fee);
		if ($amount < 0) echo '</span>';?>
</td>
<td>
<?php  	if (isAnyAdminLoggedIn($session)) {
			// Only allow accepting epayments with all fields known
			if ($canReconcile) { ?>
<a href="#" title="Reconcile and accept ePayment" onClick="document.location.href = 'new-order.php?<?php echo returnRequiredParams($session) . "&teamid=" . $teamid . "&uid=" . $uid . "&orderdate=" . $paymentdate . "&paymentmethod=" . AccountPaymentMethod_Paypal . "&order=" . $skuid . ",". $amount . "," . $fee . "&epayid=" . $epayid ?>'"><img src="img/accept.png" alt="Accept and reconcile" border="0"></a>
<?php		} ?>
</td>
<td>
<?php		if ($canEdit) { ?>
<div id="editepaybutton<?php echo $rowCountPayments?>" class="hideit"><a href="#" title="Modify ePayment" onClick="doEdit(<?php echo $epayid?>,<?php
				if ($numUsers == 1) {
					echo $uid . ",";
				} else {
					echo "document.getElementById(" . chr(39) . "uid" . $rowCountPayments . chr(39) . ").options[document.getElementById(" . chr(39) . "uid" . $rowCountPayments . chr(39) . ").selectedIndex].value,";
				}
				if ($numSkusInEpayment == 1) {
					echo $skuid . ");";
				} else {
					echo "document.getElementById(" . chr(39) . "skuid" . $rowCountPayments . chr(39) . ").options[document.getElementById(" . chr(39) . "skuid". $rowCountPayments . chr(39) . ").selectedIndex].value);";
				} ?>
"><img src="img/edit.gif" alt="Change ePayment" border="0"></a></div>&nbsp;
<?php		} ?>
</td>
<td>
<a href="#" onClick="confDelete(<?php echo chr(39) . $txn_id . chr(39) . ", " .$epayid?>);" title="Delete ePayment" target="_top"><img src="img/delete.png" alt="Delete ePayment" border="0"></a>
</td>
<?php  	} ?>
</tr>
<?php
		$rowCountPayments ++;
	}
	// Only show orderitems total if admin
	if ($rowCountPayments > 0) { ?>
<tr><td class="moneytotal">Totals</td><td class="moneytotal"></td><td class="moneytotal"></td><td class="moneytotal"></td><td class="moneytotal"></td><td class="moneytotal"></td>
<td class="<?php echo getMoneyTotalClass($sumGross)?>"><?php echo formatMoney($sumGross) ?></td>
<td class="<?php echo getMoneyTotalClass($sumFees)?>"><?php echo formatMoney($sumFees) ?></td>
<td class="<?php echo getMoneyTotalClass($sumNet)?>"><?php echo formatMoney($sumNet) ?></td>
<td class="moneytotal"></td><td class="moneytotal"></td><td class="moneytotal"></td></tr>
<?php }
	if ($numPayments == 0) { ?>
<tr><td colspan="10">There are no unreconciled ePayments.</td></tr>
<?php
	}
?>
</tbody>
</table>
</form>
<?php
	// Only show orderitems total if admin
	if (($rowCountPayments > 0) && (isAnyAdminLoggedIn( $session))) { ?>
<p><a href="team-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $teamid?>">
<?php echo $objname?></a>: <?php echo $rowCountPayments?> unreconciled epayments, totaling&nbsp;<span class="strong"><?php echo formatMoney($sumGross) ?></span></p>
<?php
	}



	// Previously reconciled ePayments

	if ((isset($_GET["year"])) && (is_numeric($_GET["year"]))) {
		$paymentyear = $_GET["year"];
		$expand = "showit";
		$expandimg = "collapse";
	} else {
		$paymentyear = date("Y");
		$expand = "hideit";
		$expandimg = "expand";
	}

	$strSQL = "select * from epayments where reconciled = TRUE and teamid = ? AND date >= '1/1/" . $paymentyear . "' AND date <= '12/31/" . $paymentyear . "' ORDER BY date DESC";
	$results = executeQuery($dbconn, $strSQL, $bError, array($teamid));
	$numPayments = count($results);
?>
<h4 class="expandable"><a class="linkopacity" href="javascript:void(0)" onclick="javascript:togglevis('reconciled');return false">Previously Reconciled ePayments for <?php echo $teamname?><img src="/1team/img/a_<?php echo $expandimg?>.gif" id="reconciled_img" border="0" alt="expand or collapse"></a></h4>
<div class="<?php echo $expand?>" id="reconciled">
<form action="">
<table class="memberlist">
<thead class="head">
<tr>
<th align="left"><a target="_top" href="epayment-reconcile-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&year=<?php echo $paymentyear-1?>"><img src="img/a_previous.gif" border="0" alt="previous">Previous year</a></th>
<th align="center" colspan="9"><span class="bigstrong"><?php echo $paymentyear ?></span></th>
<th align="right"><a target="_top" class="linkopacity" href="epayment-reconcile-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&year=<?php echo $paymentyear+1?>">Next year<img src="img/a_next.gif" border="0" alt="next"></a></th>
</tr>
<tr>
<th valign="top">Date</th>
<th valign="top">Transaction ID</th>
<th valign="top">Payer Email</th>
<th valign="top">For Account</th>
<th valign="top">SKU</th>
<th valign="top">Description</th>
<th valign="top">Gross</th>
<th valign="top">Fee</th>
<th valign="top">Net</th>
<?php
	if (isAnyAdminLoggedIn($session)) { ?>
<th valign="top" colspan="2">Actions</th>
<?php
	}?>
</tr>
</thead>
<tbody>
<?php
	$rowCountPayments = 0;
	$sumGross = 0;
	$sumFees = 0;
	$sumNet = 0;

	while ($rowCountPayments < $numPayments) {
		$epayid = $results[$rowCountPayments]["id"];
		$amount = $results[$rowCountPayments]["amount"];
		$txn_id = $results[$rowCountPayments]["txid"];
		$payeremail = $results[$rowCountPayments]["payeremail"];
		$payeremailupper = strtoupper($payeremail);
		$skuname = $results[$rowCountPayments]["skuname"];
		$fee = - $results[$rowCountPayments]["fee"];
		$item = $results[$rowCountPayments]["item"];
		$paymentdate = $results[$rowCountPayments]["date"];
		$uid = $results[$rowCountPayments]["userid"];
		// Get the user for this payment. May be more than one account with the same email. Give a selector if so
		$strSQL = "select useraccountinfo.userid, useraccountinfo.status, users.firstname, users.lastname from users, useraccountinfo, epayments WHERE users.id = useraccountinfo.userid AND epayments.id = ? AND UPPER(epayments.payeremail) = ? and UPPER(epayments.payeremail) = UPPER(useraccountinfo.email);";
		$resultsUserSelect = executeQuery($dbconn, $strSQL, $bError, array($epayid, $payeremailupper));
		$numUsers = count($resultsUserSelect );

		// Get the sku for this payment. Give a selector if you can't decide
		$strSQL = "SELECT programs.name AS programs_name, skus.*, skus.id as skuid FROM epayments INNER JOIN (programs INNER JOIN skus on programs.id = skus.programid) ON epayments.skuname = skus.name WHERE epayments.teamid = ? AND epayments.id = ?";
		$resultsSkuEpayment = executeQuery($dbconn, $strSQL, $bError, array($teamid, $epayid));
		$numSkusInEpayment = count($resultsSkuEpayment );

		// Get all SKUs for selector
		$strSQL = "SELECT * FROM skus WHERE teamid = ? ORDER BY listorder";
		$resultsSkuSelect = executeQuery($dbconn, $strSQL, $bError, array($teamid));
		$numSkus = count( $resultsSkuSelect);


?>
<tr class="<?php
		if ( ($rowCountPayments+1) % 2 ) echo("even");
		else echo("odd");?>">
<td><?php
	 	echo $results[$rowCountPayments]["date"]?></td>
<td><?php
		if (!empty($txn_id)){
			echo '<a href="pp\TransactionDetails.php?' . returnRequiredParams($session) . '&teamid=' . $teamid . '&transactionID=' . $txn_id .'">';
			echo $txn_id . "</a>";
		} ?></td>
<td><a href="mailto:<?php echo $payeremail?>?Subject=Payment%20<?php echo $txn_id = $results[$rowCountPayments]["txid"]?>%20on%20date%20<?php echo $paymentdate?>"><?php echo $payeremail?></a></td>
<td><?php
		if ($results[$rowCountPayments]["userid"] == User::UserID_Guest) {
			echo User::Username_Guest;
		} else  {?>
<a href="user-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $resultsUserSelect[0][ "userid"]?>"><span <?php echo subdueInactive($resultsUserSelect[0]["status"])?>><?php echo $resultsUserSelect[0][ "firstname"]?>&nbsp;<?php echo $resultsUserSelect[0][ "lastname"] ?></span></a>
<?php
		} ?>
</td>
<td>
<a href="edit-sku-form.php?<?php
		echo returnRequiredParams($session) . '&teamid=' . $teamid . '&id=' . $resultsSkuEpayment[0]["skuid"] .'">' . $resultsSkuEpayment[0]["name"];?></a>
</td>
<td><?php
		$item = $results[$rowCountPayments]["item"];
		if (strlen($item) > ePaymentItemDisplayLength){
			echo substr($item, 0, ePaymentItemDisplayLength-1) . "...";
		} else {
			echo $results[$rowCountPayments]["item"];
		}?>
</td>
<td><?php
		if ($amount < 0) echo '<span class="debit">';
		echo formatMoney($amount);
		$sumGross += $amount;
		if ($amount < 0) echo '</span>';?>
</td>
<td><?php
		echo '<span class="debit">';
		$sumFees += $fee;
		echo formatMoney($fee);
		echo '</span>';?></td>
<td><?php
		if ($amount < 0) echo '<span class="debit">';
		$sumNet += $amount+$fee;
		echo formatMoney( $amount+$fee);
		if ($amount < 0) echo '</span>';?>
</td>
<td>
<?php  	if (isAnyAdminLoggedIn($session)) { ?>
<a href="#" title="Unreconcile ePayment" onClick="document.location.href = 'unreconcile-epayment.php?<?php echo returnRequiredParams($session) . "&teamid=" . $teamid . "&id=" . $epayid ?>'"><img src="img/arrow-undo.png" alt="Unreconcile" border="0"></a>
</td>
<td>
<a href="#" onClick="confDelete(<?php echo chr(39) . $txn_id . chr(39) . ", " .$epayid?>);" title="Delete ePayment" target="_top"><img src="img/delete.png" alt="Delete ePayment" border="0"></a>
<?php  	} ?>
</td>
</tr>
<?php
		$rowCountPayments ++;
	}
	// Only show orderitems total if admin
	if ($rowCountPayments > 0) { ?>
<tr><td class="moneytotal">Totals</td><td class="moneytotal"></td><td class="moneytotal"></td><td class="moneytotal"></td><td class="moneytotal"></td><td class="moneytotal"></td>
<td class="<?php echo getMoneyTotalClass($sumGross)?>"><?php echo formatMoney($sumGross) ?></td>
<td class="<?php echo getMoneyTotalClass($sumFees)?>"><?php echo formatMoney($sumFees) ?></td>
<td class="<?php echo getMoneyTotalClass($sumNet)?>"><?php echo formatMoney($sumNet) ?></td>
<td class="moneytotal"></td><td class="moneytotal"></td></tr>
<?php } ?>
</tbody>
</table>
</form>
<?php
	// Only show orderitems total if admin
	if (($rowCountPayments > 0) && (isAnyAdminLoggedIn( $session))) { ?>
<p><a href="team-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $teamid?>">
<?php echo $objname?></a>: <?php echo $rowCountPayments?> previously reconciled epayments, totaling&nbsp;<span class="strong"><?php echo formatMoney($sumGross) ?></span></p>
<?php
	}
	if ($numPayments == 0) { ?>
<p class="error">No epayments found.</p>
<?php
	} ?>
</div>
<?php
// end !bError
}
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "There was an error processing this ePayment.", "");
} else if (isset($_GET["done"])){
	showMessage("Success", "The ePayment was processed successfully.");
}
// Start footer section
include('footer.php');
?>
<script type="text/javascript">
function confDelete(name, id) {
	if (confirm('Are you sure you want to delete the ' + name + ' ePayment?')) {
		document.location.href = 'delete-epayment.php?<?php echo returnRequiredParams($session) . "&teamid=" . $teamid . "&id="?>' + id;
	}
}

function doEdit( epayid, uid, skuid ){
	document.location.href = 'edit-epayment.php?<?php echo returnRequiredParams($session) . "&teamid=" . $teamid . "&id=" ?>' + epayid + '&uid=' + uid + '&skuid=' + skuid;
}
</script></body>
</html>
