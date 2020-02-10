<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title= " Make a Team Payment" ;
include('header.php'); ?>
<script type="text/javascript"> 
dojo.require("dijit.form.DateTextBox");
dojo.require("dijit.form.CurrencyTextBox");
</script>

<?php
$bError = false;
$teamid = NotFound;
// teamid depends on who is calling
if (isUser($session, Role_TeamAdmin)){
	$teamid = getTeamID($session);
} else {
	if (isset($_REQUEST["teamid"])){
		$teamid = $_REQUEST["teamid"];
	} else{
		$bError = true;
	}
}

   ?>
<h3><?php echo $title?></h3>
<p>All prices are listed per month. 1 year commitment is required, so a team that costs $1 per month, must pay $12 for 12 months up front.</p>
<p>You must subscribe to an automated payment plan with PayPal. This
will charge your PayPal account on the same day of each year. You can cancel at
any time through PayPal, but will not be refunded for any unused portion of the year.</p>


<table  class="memberlist">
<thead class="head">
    <th width="25%" class="bold">Plan Members</th>
    <th width="15%" align="left">Price per month</th>
    <th width="25%" align="left">Subscribe for 12 months</th>
</thead>
<?php
$numPlans = count($aTeamCost);
// Skip first array entry since that represents "undefined"
for ($loopPlans = 1; $loopPlans < $numPlans; $loopPlans++){
?>
<tr>
<td width="15%" class="bold">Up to <?php echo $aTeamPlanMaxMembers[$loopPlans] . " "?><?php echo $teamterms["termmember"]?>s</td>
<td><?php echo formatMoney($aTeamCost[$loopPlans])?></td>
<td>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick-subscriptions">
<input type="hidden" name="business" value="orders@austinjiujitsu.com">
<input type="hidden" name="item_name" value="Annual payment for <?php echo $aTeamPlanMaxMembers[$loopPlans] . " "?> users">
<input type="hidden" name="item_number" value="1tw-annual-<?php echo $aTeamPlanMaxMembers[$loopPlans]?>">
<input type="hidden" name="no_note" value="1">  <!-- Don't provide space for a customer note-->
<input type="hidden" name="currency_code" value="USD">
<input type="image" src="https://www.paypal.com/images/x-click-but20.gif" border="0" name="I1" alt="Make payments with PayPal - it's fast, free and secure!" width="62" height="31">
<input type="hidden" name="a3" value="<?php echo $aTeamCost[$loopPlans]?>">
<input type="hidden" name="p3" value="1">   <!-- billing cycle is 1 year-->
<input type="hidden" name="t3" value="Y">   <!-- billing cycle is 1 year-->
<input type="hidden" name="src" value="1">  <!-- recurring-->
<input type="hidden" name="sra" value="1">  <!-- reattempt if insufficient funds-->
</form>
</td>
</tr>
<?php
}?>
</table>
<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<hr>
<input type="image" src="https://www.paypal.com/images/view_cart.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" width="130" height="32">
<input type="hidden" name="display" value="1">
<input type="hidden" name="cmd" value="_cart">
<input type="hidden" name="business" value="orders@austinjiujitsu.com">
</form>
<br><b>Cancel an existing subscription<br><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=orders%40austinjiujitsu.com">
<img src="https://www.paypal.com/en_US/i/btn/cancel_subscribe_gen.gif" border="0" width="139" height="21">
</a></b>

<?php

// Start footer section
include('footer.php'); ?>
</body>
</html>