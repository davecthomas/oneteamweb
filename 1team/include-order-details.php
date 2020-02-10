<?php
	// To prevent this from being called directly, validate session and user login
	if (! isValidSession($session )){
		redirectToLogin();
	}
	redirectToLoginIfNotAdminOrCoach( $session);

	
	$teamname = getTeamName($teamid, $dbconn);
	$strSQL = "SELECT users.firstname, users.lastname,  paymentmethods.name as paymentmethodname, programs.name as programname, skus.*, skus.name as skuname,
		orderitems.id as orderitemid, orderitems.*,
		orders.paymentmethod as orderpaymentmethod, orders.ispaid as orderispaid, orders.*
		FROM (orders INNER JOIN (paymentmethods
			RIGHT OUTER JOIN (programs
				INNER JOIN (users
					RIGHT OUTER JOIN (orderitems
						LEFT OUTER JOIN skus ON (skus.id = orderitems.skuid))
					on users.id = orderitems.userid and users.id = ? )
				on orderitems.programid = programs.id) on orderitems.paymentmethod = paymentmethods.id)
			ON orderitems.orderid = orders.id)
		WHERE orderid = ? AND orderitems.teamid = ? ORDER BY paymentdate DESC;";
	$pdostatement = $dbh->prepare($strSQL);
	$bError = ! $pdostatement->execute(array($userid, $orderid, $teamid));
	$orderResults = $pdostatement->fetchAll();
	$countOrders = 0;
	$numOrders = count($orderResults);

	if ($numOrders > 0){

		$ispaid = $orderResults[0]["ispaid"];
		$duedate = $orderResults[0]["duedate"];
		// Check for overdue
		$isoverdue = false;
		if (!$ispaid){
			$daysDue = dateDiffNumDays($dbh, date("m/d/Y"), $duedate);
		}
		if ( $userid >= UserID_Base ) {
			$bDisplayUserSelector = false;
			$username = getUserName( $userid, $dbconn); ?>
<h3><?php if ($ispaid) echo "Receipt for payment from "; 
		else echo "Invoice for payment due from ";
		if ($email) echo '<a href="user-props-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $userid?>"><?php echo $username?></a>';
		echo '&nbsp;to '. $teamname ?></h3>
<table class="noborders" width="65%">
<?php
		} else {
			$bError = true;
		}
		// Get team name, address, and logo
		$strSQL = "SELECT teams.id as id_team, teams.*, teamaccountinfo.*, images.* FROM teamaccountinfo, teams LEFT OUTER JOIN images ON (images.teamid = teams.id and images.type = ?) WHERE teamaccountinfo.teamid = teams.id AND teams.id = ?";
		$pdostatement = $dbh->prepare($strSQL);
		$pdostatement->execute(array(ImageType_Team, $teamid, $teamid));
		$teamresults = $pdostatement->fetch(PDO::FETCH_ASSOC);
		// Print team logo, etc
		if (!$bError ){ 

			if ((!is_null($teamresults["url"])) && (is_url($teamresults["url"]))) {?>
<tr valign="top"><td><img src="<?php echo $teamresults["url"]?>" id="" border=0" height ="200"></td>
<?php
			} else if (isset($teamresults["filename"])){ ?>
<tr valign="top"><td><img src="<?php echo getRoot() ."/1team/".uploadsDir."/$teamid/".$teamresults["filename"]?>" id="" border=0" height ="200"></td>
<?php
			}?>
<td><span class="strong">Today's date:</span> <?php echo date("m/d/Y")?></td></tr>
<tr><td><span class="strong">Sold by:</span>&nbsp;<?php echo $teamname?>&nbsp;<?php echo $teamresults["website"]?><br/>
<?php			if (strlen($teamresults["address1"])>0) echo $teamresults["address1"]."<br>";?>
<?php			if (strlen($teamresults["address2"])>0) echo $teamresults["address2"]."<br>";?>
<?php			if (strlen($teamresults["city"])>0) echo $teamresults["city"] . ", ". $teamresults["state"] . " " .$teamresults["postalcode"]."<br>";?>
<?php			if (strlen($teamresults["phone"])>0) echo $teamresults["phone"]."<br>";?>
<?php			if (strlen($teamresults["email"])>0) echo '<span class="strong">'.$teamterms["termadmin"].' email:</span> '.$teamresults["email"].'<br>';?>
</td></tr>
<tr><td><span class="strong">Sold to:</span>&nbsp;<?php echo $orderResults[0]["firstname"] . " " . $orderResults[0]["lastname"]?>&nbsp;on <?php echo $orderResults[0]["orderdate"]?></td></tr>
<?php	}

?>
</table>
<div id="duedatediv" <?php if ($ispaid) echo 'class="hideit"';?>>
<table class="noborders">
<tr><?php if ((!$ispaid) && ($daysDue < 0)){?>
<td style="font-size: 1.5em;font-weight: bolder;color:red">Payment was due <?php echo $orderResults[0]["duedate"]?> and is overdue <?php echo -$daysDue?> day<?php if (abs($daysDue) != 1) echo "s"?>. Please pay immediately.</td>
<?php	} else if (!$ispaid) {?>
<td style="font-size: 1.5em;font-weight: bolder;">Please pay by due date: <?php echo $orderResults[0]["duedate"]?></td>
<?php	} ?>
</tr></table>
</div>
<table class="noborders" width="65%" id="ordertable">
<thead class="head">
<tr>
<th style="text-align:left" width="5%" ></th>
<th style="text-align:left" width="45%" >SKU</th>
<th style="text-align:left" width="20%">Amount ($US)</th>
<th style="text-align:left" width="20%">Fee ($US)</th>
</tr>
</thead>
<?php
		// GEt skus once for use in the selector used in the loop 
		$strSQL = "SELECT * FROM skus WHERE teamid = ? ORDER BY listorder";
		$pdostatementS = $dbh->prepare($strSQL);
		$bError = ! $pdostatementS->execute(array($teamid));
		$skuResults = $pdostatementS->fetchAll();
		$rowCountS = count( $skuResults);
		$amountTotal = 0.00;
		$feeTotal = 0.00;
		$grandTotal = 0.00;

		// One set of edit controls per orderitem 
		while ($countOrders < $numOrders){ ?>
<tr id="orderitem<?php echo $orderResults[$countOrders]["orderitemid"]?>" class="<?php if ((bool)( ($countOrders) % 2 )) echo("even"); else echo("odd") ?>">
<td width="5%"><?php echo $countOrders+1?></td>
<td width="45%"><?php echo $orderResults[$countOrders]["skuname"]?></td>
<td width="20%"><?php echo $orderResults[$countOrders]["amount"]?></td>
<td width="20%"<?php if ($orderResults[$countOrders]["fee"] < 0) echo 'class="debit"';?>><?php echo $orderResults[$countOrders]["fee"]?></td>
<?php 
			$amountTotal += $orderResults[$countOrders]["amount"];
			$feeTotal += $orderResults[$countOrders]["fee"];
			$grandTotal += $orderResults[$countOrders]["amount"] + $orderResults[$countOrders]["fee"];
			$countOrders++;
		} ?>
</tr>
<thead>
<tr><th colspan="5"></th></tr>
</thead>
</table>
<table class="noborders" width="65%">
<thead>
<tr><th colspan="5"></th></tr>
</thead>
<tr class="totalrow">
<td width="5%" class="bold"></td>
<td width="45%" style="font-size: 1.5em;font-weight: bolder;"><?php if ($orderResults[0]["ispaid"]) echo 'Amount Paid'; else echo 'Invoice Amount'?>&nbsp;<?php echo formatMoney($grandTotal);?></td>
<td width="20%" class="bold"><?php echo formatMoney($amountTotal);?></td>
<td width="20%" class="debit"><?php echo formatMoney($feeTotal);?></td>
</tr>
</table>
<table class="noborders" width="65%">
<thead><tr><th colspan="5"></th></tr>
</thead>
</table>
<?php
} else $bError = true;?>