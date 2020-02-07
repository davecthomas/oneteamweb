<?php
// This is included by payment-history, among others. Prerequisites:
// 	whomode, pageMode, userid (if whomode == user)
include_once("utils.php");

if ((!isset($pageMode)) && (isset($_GET["pagemode"]))) {
	$pageMode = $_GET["pagemode"];
} else {
	$pageMode = "standalone";
}
if ($pageMode == "standalone") {
	$expandimg = "collapse";
	$expandclass = "showit";
} else { ?>
<link rel="stylesheet" type="text/css" href="1team.css"/>
<link rel="stylesheet" type="text/css" href="https://ajax.googleapis.com/ajax/libs/dojo/1.3/dijit/themes/<?php echo dojostyle?>/<?php echo dojostyle?>.css" />
<script type="text/javascript" src="/1team/utils.js"></script>
<?php
	$session = getSession();
	$expandimg = "expand";
	$expandclass = "hideit";
	if (!isset($teamid)) {
		$userid = $_GET["id"];
	} 
} 

if (!isset($teamid)) {
	$teamid = $_GET["teamid"];
} 
if ((isset($_GET["year"])) && (is_numeric($_GET["year"]))) {
	$paymentyear = $_GET["year"];
	$expand = "showit";
	$expandimg = "collapse";
} else {
	$paymentyear = date("Y");
	$expand = "hideit";
	$expandimg = "expand";
}
$sortRequest = "paymentdate DESC";
if (isset($_GET["sort"])) {
	$sortRequest = getCleanInput($_GET["sort"]);
}

// Set default for programid if not set
if (!isset($programid)) $programid = Program_Undefined;
if (!isset($recentlyexpired)) $recentlyexpired = "1 mon";
$bError = false;
if (!isset($whomode)) $whomode = "user";

if (($whomode == "user") && (!canIAdministerThisUser( $session, $userid))) {
	$bError = true;
	$errStr = Error;
} 

if (($whomode == "team") && (!isAnyAdminLoggedIn($session))) {
	$bError = true;
	$errStr = Error;
}	
if (!$bError ) {
	if ((!isset($userid)) && ($whomode == "user")) {
		$userid = $_GET["id"];
		if (!isset($username)) {
			$username = getUserName($userid);
		} 
	} 
	
	$dbh = getDBH($session);  
	// User mode: make sure they can adminster this user
	if ($whomode == "user") {
		$objid = $userid;	
		$objname = getUserName2($userid, $dbh);
		$sqlwhere = "attendance.memberid = " . $userid;
	} else {
		$sqlwhere = "attendance.teamid = " . $teamid;
		$objid = $teamid;
		$objname = getTeamName2($teamid, $dbh);
	}
	// Standalone page needs some formatting that embedded version doesn't require
	if ($pageMode == "standalone") { ?>
<h4><?php echo $objname?> Payment History</h4>
<div class="indented-group-noborder">
<?php		
	} 
	// Tweak query based on whomode
	if ($whomode == "user")	{
		if ($userid != User::UserID_Guest)
			$modquery = " AND users.id = ? "; 
		else 
			$modquery = " AND orderitems.userid = ? ";
	} else {
		$modquery = "";
	}

	// Active orderitems table - all non-expired orderitems
	// Query may be to list all orderitems of a specific program
	if ((isset($programid)) && ($programid != Program_Undefined)){
		$strSQL = "SELECT users.firstname, users.lastname,  paymentmethods.name as paymentmethodname, programs.name as programname, skus.*, skus.name as skuname, orderitems.id as payid, orderitems.* FROM (paymentmethods INNER JOIN (programs INNER JOIN (users RIGHT OUTER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid) on orderitems.programid = programs.id) on orderitems.paymentmethod = paymentmethods.id) WHERE orderitems.numeventsremaining <> 0 " . $modquery . " AND orderitems.programid = ? AND orderitems.teamid = ? AND (paymentdate + expires >= current_date) ORDER BY paymentdate DESC;";
		$pdostatement = $dbh->prepare($strSQL);
		if ($whomode == "user") {
			$pdostatement->execute(array($userid, $programid, $teamid));
		} else {
			$pdostatement->execute(array($programid, $teamid));
		}
	// Or to list all orderitems, regardless of program
	} else {
		$strSQL = "SELECT users.firstname, users.lastname,  paymentmethods.name as paymentmethodname, programs.name as programname, skus.*, skus.name as skuname, orderitems.id as payid, orderitems.* FROM (paymentmethods INNER JOIN (programs INNER JOIN (users RIGHT OUTER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid) on orderitems.programid = programs.id) on orderitems.paymentmethod = paymentmethods.id) WHERE orderitems.numeventsremaining <> 0 " . $modquery . " AND orderitems.teamid = ? AND (paymentdate + expires >= current_date) ORDER BY paymentdate DESC;";
		$pdostatement = $dbh->prepare($strSQL);
		if ($whomode == "user") {
			$pdostatement->execute(array($userid, $teamid));
		} else {
			$pdostatement->execute(array($teamid));
		}
	}
	$results = $pdostatement->fetchAll();
	$numPayments = count($results); ?>
<h5 class="expandable"><a class="linkopacity" href="javascript:void(0)" onclick="javascript:togglevis('unexpired');return false">Unexpired Program Payments With Remaining Events<img src="img/a_expand.gif" alt="expand section" id="unexpired_img" border="0"></a></h5>
<div class="hideit" id="unexpired" name="unexpired">
<div class="indented-group-noborder">
<table class="memberlist"> 
<thead class="head">
<tr>
<th valign="top">Date</th>
<?php
	if ($whomode == "team") {?>
<th valign="top">Member</th><?php
	} ?>
<th valign="top">SKU</th>
<th valign="top">Program</th>
<th valign="top">Num events</th>
<th valign="top">Events remaining</th>
<th valign="top">Method</th>
<th valign="top">Gross</th>
<th valign="top">Fee</th>
<th valign="top">Net</th>
<?php 
	if (isAnyAdminLoggedIn($session)) { ?>
<th valign="top">Actions</th>
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
		$payment = $results[$rowCountPayments]["amount"];
		$sumGross = $sumGross + $payment;
		$fee = $results[$rowCountPayments]["fee"];	
		$sumFees = $sumFees + $fee;
		$net = $payment + $fee;
		$sumNet = $sumNet + $net;
		$paymentid = $results[$rowCountPayments]["payid"];
		$orderid = $results[$rowCountPayments]["orderid"];
?>
<tr class="<?php  
		if ( ($rowCountPayments+1) % 2 ) echo("even"); 
		else echo("odd");?>">
<td><?php
	 	echo $results[$rowCountPayments]["paymentdate"]?></td>
<?php	
		if ($whomode == "team") {
			echo "<td>\n";
			if ($results[$rowCountPayments]["userid"] == User::UserID_Guest)
				echo User::Username_Guest;
			else  
				echo '<a target="_top" href="user-props-form.php?' . buildRequiredParamsConcat($session) . '&id=' . $results[$rowCountPayments]["userid"] . '">' . $results[$rowCountPayments]["firstname"] . ' ' . $results[$rowCountPayments]["lastname"] . '</a>';
			echo "</td>\n"; 
		} ?> 
<td><?php
		echo $results[$rowCountPayments]["skuname"]?></td>
<td><?php
		echo $results[$rowCountPayments]["programname"]?></td>
<td><?php 		
		switch ($results[$rowCountPayments]["numevents"]) {
			case Sku::NumEventsUnlimited:
				echo "Unlimited";
				break;
			default: echo $results[$rowCountPayments]["numevents"];
				break;
		} ?></td>
<td><?php
		$numeventsremaining = $results[$rowCountPayments]["numeventsremaining"];
		switch ($numeventsremaining) {
			case Sku::NumEventsUnlimited:
				echo "Unlimited";
				break;
			default:
				if ($numeventsremaining < 1) echo '<span class="noevents">'; 
				echo $numeventsremaining;
				if ($numeventsremaining < 1) echo '</span>'; 
				break;
		} ?></td>
<td><?php 	
		echo $results[$rowCountPayments]["paymentmethodname"]?></td>
<td class="<?php echo getMoneyClass($payment)?>"><?php 	
		echo formatMoney($payment); ?>
</td>
<td class="<?php echo getMoneyClass($fee)?>"><?php 	
		echo formatMoney($fee); ?>
</td>
<td class="<?php echo getMoneyClass($net)?>"><?php 	
		echo formatMoney($net); ?>
</td>
<?php  	if (isAnyAdminLoggedIn($session)) { ?>
<td>
<a href="edit-order-form.php?<?php echo returnRequiredParams($session)?>&teamid=<?php echo $teamid?>&uid=<?php echo $results[$rowCountPayments]["userid"]?>&id=<?php echo $orderid?>" title="Edit order" target="_top"><img src="img/cart.png" alt="Edit order" border="0"></a>&nbsp;
<a href="edit-orderitem-form.php?<?php echo returnRequiredParams($session)?>&teamid=<?php echo $teamid?>&paymentid=<?php echo $paymentid?>&whomode=<?php echo $whomode?>&uid=<?php echo $results[$rowCountPayments]["userid"]?>&id=<?php echo $objid?>" target="_top" title="Edit order item"><img src="img/edit.gif" alt="Edit order item" border="0"></a>&nbsp;
<a href="delete-orderitem.php?<?php echo returnRequiredParams($session)?>&teamid=<?php echo $teamid?>&paymentid=<?php echo $paymentid?>&whomode=<?php echo $whomode?>&id=<?php echo $objid?>" title="Delete" target="_top"><img src="img/delete.png" alt="Delete" border="0"></a>
</td>
<?php  	} ?>
</tr>
<?php             
		$rowCountPayments ++;
	}	
	// Only show orderitems total if admin 
	if (($rowCountPayments > 0) && (isAnyAdminLoggedIn( $session))){ 
?>
<tr><td class="moneytotal" colspan="<?php 
	if (isAnyAdminLoggedIn($session)) { 
		if ($whomode == "team") { 
			echo "7"; 
		} else { 
			echo "6";
		} 
	} else {
		echo "4"; 
	}?>">Totals</td>
<td class="<?php echo getMoneyTotalClass($sumGross)?>"><?php echo formatMoney($sumGross) ?></td>
<td class="<?php echo getMoneyTotalClass($sumFees)?>"><?php echo formatMoney($sumFees) ?></td>
<td class="<?php echo getMoneyTotalClass($sumNet)?>"><?php echo formatMoney($sumNet) ?></td>
<td class="moneytotal"></td></tr>
<?php } ?>
</tbody>
</table>
<?php
	// Only show orderitems total if admin 
	if (($sumGross > 0) && (isAnyAdminLoggedIn( $session))) { 
		if ($whomode == "user") { ?>
<p><a href="user-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $objid?>"> 
<?php	} else { ?>
<p><a href="team-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $objid?>">
<?php	} ?>
<?php echo $objname?></a>: <?php echo $rowCountPayments?> payments are active and unexpired.</p>
<?php
	}  
	if ($numPayments == 0) { ?>
<p class="error">No payments have remaining classes and are unexpired.</p>
<?php 
	}
	// Recently expired orderitems, or active orderitems with no remaining events
	// Query may be to list all orderitems of a specific program
	if ((isset($programid)) && ($programid != Program_Undefined)){
		$strSQL = "SELECT users.firstname, users.lastname,  paymentmethods.name as paymentmethodname, programs.name as programname, skus.*, skus.name as skuname, orderitems.id as payid, orderitems.* FROM (paymentmethods INNER JOIN (programs INNER JOIN (users RIGHT OUTER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid) on orderitems.programid = programs.id) on orderitems.paymentmethod = paymentmethods.id) WHERE orderitems.programid = ? " . $modquery . " AND orderitems.teamid = ? and (((paymentdate + expires > (current_date - '" . $recentlyexpired . "'::interval)) AND (paymentdate + expires < current_date)) OR ((paymentdate + expires >= current_date) and numeventsremaining = 0)) ORDER BY paymentdate DESC;"; 
		$pdostatement = $dbh->prepare($strSQL);
		if ($whomode == "user") {
			$pdostatement->execute(array($programid, $userid, $teamid));
		} else {
			$pdostatement->execute(array($programid, $teamid));
		}
	// Or to list all orderitems, regardless of program
	} else {
		$strSQL = "SELECT users.firstname, users.lastname,  paymentmethods.name as paymentmethodname, programs.name as programname, skus.*, skus.name as skuname, orderitems.id as payid, orderitems.* FROM (paymentmethods INNER JOIN (programs INNER JOIN (users RIGHT OUTER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid) on orderitems.programid = programs.id) on orderitems.paymentmethod = paymentmethods.id) WHERE orderitems.teamid = ? " . $modquery . " and (((paymentdate + expires > (current_date - '" . $recentlyexpired . "'::interval)) AND (paymentdate + expires < current_date)) OR ((paymentdate + expires >= current_date) and numeventsremaining = 0)) ORDER BY paymentdate DESC;"; 
		$pdostatement = $dbh->prepare($strSQL);
		if ($whomode == "user") {
			$pdostatement->execute(array($teamid, $userid));
		} else {
			$pdostatement->execute(array($teamid));
		}
	}
	$results = $pdostatement->fetchAll();
	$numPayments = count($results); ?>
</div></div>
<h5 class="expandable"><a class="linkopacity" href="javascript:void(0)" onclick="javascript:togglevis('recentexpired');return false">Recently Expired Program Payments (past <?php echo $recentlyexpired?>) or No Remaining Events<img src="img/a_expand.gif" alt="expand section" id="recentexpired_img" border="0"></a></h5>
<div class="hideit" id="recentexpired" name="recentexpired">
<div class="indented-group-noborder">
<?php if ($numPayments > 0){?>
<table class="memberlist"> 
<thead class="head">
<tr>
<th valign="top">Date</th>
<?php
		if ($whomode == "team") {?>
<th valign="top">Member</th><?php
		} ?>
<th valign="top">SKU</th>
<th valign="top">Program</th>
<th valign="top">Num events</th>
<th valign="top">Events remaining</th>
<th valign="top">Method</th>
<th valign="top">Gross</th>
<th valign="top">Fee</th>
<th valign="top">Net</th><?php 
		if (isAnyAdminLoggedIn($session)) { ?>
<th valign="top">Actions</th>
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
			$payment = $results[$rowCountPayments]["amount"];
			$sumGross = $sumGross + $payment;
			$fee = $results[$rowCountPayments]["fee"];
			$sumFees = $sumFees + $fee;
			$net = $payment + $fee;
			$sumNet = $sumNet + $net;
			$paymentid = $results[$rowCountPayments]["payid"];
			$numeventsremaining = $results[$rowCountPayments]["numeventsremaining"]; ?>
<tr class="<?php  
			if ( ($rowCountPayments+1) % 2 ) echo("even");
			else echo("odd");?>">
<td><?php
			echo $results[$rowCountPayments]["paymentdate"]?></td>
<?php	
			if ($whomode == "team") {
				echo "<td>\n";
				if ($results[$rowCountPayments]["userid"] == User::UserID_Guest)
					echo User::Username_Guest;
				else
					echo '<a target="_top" href="user-props-form.php?' . buildRequiredParamsConcat($session) . '&id=' . $results[$rowCountPayments]["userid"] . '">' . $results[$rowCountPayments]["firstname"] . ' ' . $results[$rowCountPayments]["lastname"] . '</a>';
				echo "</td>\n";
			} ?>
<td><?php
			echo $results[$rowCountPayments]["skuname"]?></td>
<td><?php
			echo $results[$rowCountPayments]["programname"]?></td>
<td><?php 		
			switch ($results[$rowCountPayments]["numevents"]) {
				case Sku::NumEventsUnlimited:
					echo "Unlimited";
					break;
				default: echo $results[$rowCountPayments]["numevents"];
					break;
			} ?></td>
<td><?php
			switch ($numeventsremaining) {
				case Sku::NumEventsUnlimited:
					echo "Unlimited";
					break;
				default:
					echo $numeventsremaining;
					break;
			} ?></td>
<td><?php 	
			echo $results[$rowCountPayments]["paymentmethodname"]?></td>
<td class="<?php echo getMoneyClass($payment)?>"><?php 	
			echo formatMoney($payment); ?>
</td>
<td class="<?php echo getMoneyClass($fee)?>"><?php 	
			echo formatMoney($fee); ?>
</td>
<td class="<?php echo getMoneyClass($net)?>"><?php 	
			echo formatMoney($net); ?>
</td>
<?php	  	if (isAnyAdminLoggedIn($session)) { ?>
<td>
<a href="delete-orderitem.php?<?php echo returnRequiredParams($session)?>&teamid=<?php echo $teamid?>&paymentid=<?php echo $paymentid?>&whomode=<?php echo $whomode?>&id=<?php echo $objid?>" title="Delete" target="_top"><img src="img/delete.gif" alt="Delete" border="0"></a>&nbsp;
<a href="edit-orderitem-form.php?<?php echo returnRequiredParams($session)?>&teamid=<?php echo $teamid?>&paymentid=<?php echo $paymentid?>&whomode=<?php echo $whomode?>&uid=<?php echo $results[$rowCountPayments]["userid"]?>&id=<?php echo $objid?>" target="_top" title="Edit"><img src="img/edit.gif" alt="Edit" border="0"></a>
</td>
<?php		} ?>
</tr>
<?php             
			$rowCountPayments ++;
		}
	
		// Only show orderitems total if admin
		if (($rowCountPayments > 0) && (isAnyAdminLoggedIn( $session))){ ?>
<tr><td class="moneytotal" colspan="<?php 
			if (isAnyAdminLoggedIn($session)) {
				if ($whomode == "team") {
					echo "7";
				} else {
					echo "6";
				}
			} else {
				echo "4";
			}?>">Totals</td>
<td class="<?php echo getMoneyTotalClass($sumGross)?>"><?php echo formatMoney($sumGross) ?></td>
<td class="<?php echo getMoneyTotalClass($sumFees)?>"><?php echo formatMoney($sumFees) ?></td>
<td class="<?php echo getMoneyTotalClass($sumNet)?>"><?php echo formatMoney($sumNet) ?></td>
<td class="moneytotal"></td></tr>
<?php	} ?>
	
</tbody>
</table>
<?php
		// Only show orderitems total if admin
		if (($sumGross > 0) && (isAnyAdminLoggedIn( $session))) {
			if ($whomode == "user") { ?>
<p><a href="user-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $objid?>"> 
<?php		} else { ?>
<p><a href="team-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $objid?>">
<?php		} ?>
<?php echo $objname?></a>: <?php echo $rowCountPayments?> orderitems are expired in the past <?php echo $recentlyexpired?>.</span></p>
<?php
		}
	} else {?>
<p>No orderitems are recently expired.</p>
<?php
	}?>
</div>
</div>
<?php // 	All orderitems (by year) table ?>
<h5 class="expandable"><a class="linkopacity" href="javascript:void(0)" onclick="javascript:togglevis('all');return false">All Payments History for <?php echo $paymentyear?><img src="/1team/img/a_<?php echo $expandimg?>.gif" id="all_img" border="0"></a></h5>
<div class="<?php echo $expand?>" id="all" name="all">
<div class="indented-group-noborder">
<table class="memberlist">
<thead class="head">
<tr>
<th align="left"><a target="_top" href="payment-history.php<?php buildRequiredParams($session) ?>&id=<?php echo $objid?>&teamid=<?php echo $teamid?>&year=<?php echo $paymentyear-1?>&sort=<?php echo $sortRequest?>&whomode=<?php echo $whomode?>&programid=<?php echo $programid?>"><img src="img/a_previous.gif" border="0" alt="previous">Previous year</a></th>	
<th align="center" colspan="<?php 
	if (isAnyAdminLoggedIn($session)) { 
		if ($whomode == "user") { 
			echo "6"; 
		} else { 
			echo "7";
		} 
	} else {
		echo "5"; 
	}?>"><span class="bigstrong"><?php echo $paymentyear ?></span></th>
<th align="right"><a target="_top" class="linkopacity" href="payment-history.php<?php buildRequiredParams($session) ?>&id=<?php echo $objid?>&teamid=<?php echo $teamid?>&year=<?php echo $paymentyear+1?>&sort=<?php echo $sortRequest?>&whomode=<?php echo $whomode?>&programid=<?php echo $programid?>">Next year<img src="img/a_next.gif" border="0" alt="next"></a></th>
</tr>
<?php
	// Query may be to list all orderitems of a specific program
	if ((isset($programid)) && ($programid != Program_Undefined)){
		$strSQL = "SELECT orderitems.userid as userid, users.firstname, users.lastname, programs.name as programname, paymentmethods.name as paymentmethodname, skus.name as skuname, orderitems.id as payid, orderitems.* FROM programs INNER JOIN (paymentmethods INNER JOIN (users RIGHT OUTER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid) on orderitems.paymentmethod = paymentmethods.id) ON programs.id = orderitems.programid WHERE orderitems.teamid = ? AND orderitems.programid = ? " . $modquery . " AND paymentdate >= '1/1/" . $paymentyear . "' AND paymentdate <= '12/31/" . $paymentyear . "' ORDER BY " . $sortRequest . ";";
		$pdostatement = $dbh->prepare($strSQL);
		if ($whomode == "user") {
			$pdostatement->execute(array($teamid, $programid, $userid ));
		} else {
			$pdostatement->execute(array($teamid, $programid));
		}
	// Or to list all orderitems, regardless of program
	} else {
		$strSQL = "SELECT orderitems.userid as userid, users.firstname, users.lastname, programs.name as programname, paymentmethods.name as paymentmethodname, skus.name as skuname, orderitems.id as payid, orderitems.* FROM programs INNER JOIN (paymentmethods INNER JOIN (users RIGHT OUTER JOIN (orderitems LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid)) on users.id = orderitems.userid) on orderitems.paymentmethod = paymentmethods.id) ON programs.id = orderitems.programid WHERE orderitems.teamid = ? " . $modquery . " AND paymentdate >= '1/1/" . $paymentyear . "' AND paymentdate <= '12/31/" . $paymentyear . "' ORDER BY " . $sortRequest . ";";
		$pdostatement = $dbh->prepare($strSQL);
		if ($whomode == "user") {
			$pdostatement->execute(array($teamid, $userid ));
		} else {
			$pdostatement->execute(array($teamid ));
		}
	}
	$results = $pdostatement->fetchAll();
	$numPayments = count($results);?>
<tr>
<th valign="top"><a target="_top" href="payment-history.php<?php buildRequiredParams($session) ?>&id=<?php echo $userid?>&teamid=<?php echo $teamid?>&sort=<?php echo sortModifier("paymentdate",$sortRequest)?>&programid=<?php echo $programid?>&whomode=<?php echo $whomode?>">Date</a></th>
<?php
	if ($whomode == "team") {?>
<th valign="top"><a target="_top" href="payment-history.php<?php buildRequiredParams($session) ?>&id=<?php echo $objid?>&teamid=<?php echo $teamid?>&sort=<?php echo sortModifier("membername",$sortRequest)?>&programid=<?php echo $programid?>&whomode=<?php echo $whomode?>">Member</a></th><?php
	} ?>
<th valign="top"><a target="_top" href="payment-history.php<?php buildRequiredParams($session) ?>&id=<?php echo $objid?>&teamid=<?php echo $teamid?>&sort=<?php echo sortModifier("skuname",$sortRequest)?>&programid=<?php echo $programid?>&whomode=<?php echo $whomode?>">SKU</a></th>
<th valign="top"><a target="_top" href="payment-history.php<?php buildRequiredParams($session) ?>&id=<?php echo $objid?>&teamid=<?php echo $teamid?>&sort=<?php echo sortModifier("programname",$sortRequest)?>&programid=<?php echo $programid?>&whomode=<?php echo $whomode?>">Program</a></th>
<th valign="top"><a target="_top" href="payment-history.php<?php buildRequiredParams($session) ?>&id=<?php echo $objid?>&teamid=<?php echo $teamid?>&sort=<?php echo sortModifier("paymentmethod",$sortRequest)?>&programid=<?php echo $programid?>&whomode=<?php echo $whomode?>">Method</a></th>
<th valign="top">Gross</th>
<th valign="top">Fee</th>
<th valign="top">Net</th> 
<?php 
	if (isAnyAdminLoggedIn($session)) { ?>
<th valign="top">Actions</th></tr>
<?php
	}?>
</thead>	
<tbody>
<?php 
	$rowCountPayments = 0;
	$sumGross = 0;
	$sumFees = 0;
	$sumNet = 0;
		
	while ($rowCountPayments < $numPayments) {
		$payment = $results[$rowCountPayments]["amount"];
		$sumGross = $sumGross + $payment;
		$fee = $results[$rowCountPayments]["fee"];	
		$sumFees = $sumFees + $fee;
		$net = $payment + $fee;
		$sumNet = $sumNet + $net;
		$paymentid = $results[$rowCountPayments]["payid"]; ?>
<tr class="<?php  
		if ( ($rowCountPayments+1) % 2 ) echo("even"); 
		else echo("odd");?>">
<td><?php
	 	echo $results[$rowCountPayments]["paymentdate"]?></td>
<?php	
		if ($whomode == "team") {
			echo "<td>\n";
			if ($results[$rowCountPayments]["userid"] == User::UserID_Guest)
				echo User::Username_Guest;
			else  
				echo '<a target="_top" href="user-props-form.php?' . buildRequiredParamsConcat($session) . '&id=' . $results[$rowCountPayments]["userid"] . '">' . $results[$rowCountPayments]["firstname"] . ' ' . $results[$rowCountPayments]["lastname"] . '</a>';
			echo "</td>\n"; 
		} ?> 
<td><?php
		echo $results[$rowCountPayments]["skuname"]?></td>
<td><?php
		echo $results[$rowCountPayments]["programname"]?></td>
<td><?php 	
		echo $results[$rowCountPayments]["paymentmethodname"]?></td>
<td class="<?php echo getMoneyClass($payment)?>"><?php 	
		echo formatMoney($payment); ?>
</td>
<td class="<?php echo getMoneyClass($fee)?>"><?php 	
		echo formatMoney($fee); ?>
</td>
<td class="<?php echo getMoneyClass($net)?>"><?php 	
		echo formatMoney($net); ?>
</td>
<?php  	if (isAnyAdminLoggedIn($session)) { ?>
<td>
<a href="delete-orderitem.php?<?php echo returnRequiredParams($session)?>&teamid=<?php echo $teamid?>&paymentid=<?php echo $paymentid?>&whomode=<?php echo $whomode?>&id=<?php echo $objid?>" title="Delete" target="_top"><img src="img/delete.gif" alt="Delete" border="0"></a>&nbsp;
<a href="edit-orderitem-form.php?<?php echo returnRequiredParams($session)?>&teamid=<?php echo $teamid?>&paymentid=<?php echo $paymentid?>&whomode=<?php echo $whomode?>&uid=<?php echo $results[$rowCountPayments]["userid"]?>&id=<?php echo $objid?>" target="_top" title="Edit"><img src="img/edit.gif" alt="Edit" border="0"></a>
</td>
<?php  	} ?>
</tr>
<?php             
		$rowCountPayments ++;
	}
	// Only show orderitems total if admin 
	if (($rowCountPayments > 0) && (isAnyAdminLoggedIn( $session))){ 
?>
<tr><td class="moneytotal" colspan="<?php 
	if (isAnyAdminLoggedIn($session)) { 
		if ($whomode == "team") { 
			echo "5"; 
		} else { 
			echo "4";
		} 
	} else {
		echo "3"; 
	}?>">Totals</td>
<td class="<?php echo getMoneyTotalClass($sumGross)?>"><?php echo formatMoney($sumGross) ?></td>
<td class="<?php echo getMoneyTotalClass($sumFees)?>"><?php echo formatMoney($sumFees) ?></td>
<td class="<?php echo getMoneyTotalClass($sumNet)?>"><?php echo formatMoney($sumNet) ?></td>
<td class="moneytotal"></td></tr>
<?php } ?>
</tbody>
</table>
<?php
	// Only show orderitems total if admin 
	if (($sumGross > 0) && (isAnyAdminLoggedIn( $session))) { 
		if ($whomode == "user") { ?>
<p><a href="user-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $objid?>"> 
<?php	} else { ?>
<p><a href="team-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $objid?>">
<?php	} ?>
<?php echo $objname?></a>: <?php echo $rowCountPayments?> orderitems in <?php echo $paymentyear ?>.</p>
<?php
	}  
	if ($numPayments == 0) { ?>
<p class="error">No orderitems made during <?php echo $paymentyear?>.</p>
<?php 
	} ?>
</div>
</div>
<?php 
	// Standalone page needs some formatting that embedded version doesn't require
	if ($pageMode == "standalone") { ?>
</div>
<?php
	}
	// Only show if member is active
	$teaminfo = getTeamInfo( $session["teamid"]); 
	// Billable members get a convenience link here
	if ((isUser($session, Role_Member)) && (utilIsUserBillable($session))) {
		echo '<a href="' . $teaminfo["paymenturl"] . '">Make a payment</a>';
	
	} 
} ?>