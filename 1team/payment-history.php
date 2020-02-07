<?php 
$title= " Payment History" ;
include('header.php');

$bError = false;
if (isset($_REQUEST["programid"])) {
	$programid = $_REQUEST["programid"];
} else {
	// Default triggers all programs
	$programid = Program_Undefined;
}

if (isset($_REQUEST["recentlyexpired"])) {
	$recentlyexpired = $_REQUEST["recentlyexpired"];
} else {
	// Default triggers 1 month check for recently expired payment
	$recentlyexpired = '1 mon';
}

if (isset($_REQUEST["whomode"])) {
	$whomode = $_REQUEST["whomode"];
	if (($whomode != "user") && ($whomode != "team")) $whomode = "user";
} else {
	$whomode = "user";
}

if ($whomode == "user") {
	if (isset($_REQUEST["id"])) {
		$userid = $_REQUEST["id"];
		$id = $userid;
	} else {
		$bError = true;
	}
} else {
	if ((isset($_REQUEST["id"])) && (isAdminLoggedIn($session))){
		$teamid = $_REQUEST["id"];
	} else {
		$teamid = $session["teamid"];
	}
	$id = $teamid;
}
if (! $bError ) { 
	// Only display prev/next for app admin, since this data spans teams
	if ((isUser($session, Role_ApplicationAdmin) ) && ($whomode == "user")){ ?>
<p></p>
<div class="navtop">
<ul id="nav">
<li><a href="<?php if ($userid > 1 ) echo("payment-history.php?id=" . $userid-1 . buildRequiredParamsConcat($session)) ?>"><img src="img/a_previous.gif" border="0" alt="previous">Previous member</a></li>	
<li><a class="linkopacity" href="payment-history.php?id=<?php echo($userid+1) . buildRequiredParamsConcat($session)?>">Next member<img src="img/a_next.gif" border="0" alt="next"></a></li>
</ul>
</div><p></p>
<?php 
	}
	
	if (($whomode == "user") && (!canIAdministerThisUser( $session, $userid))) {
		$bError = true;
		$errStr = "na";
	} 
	
	if (($whomode == "team") && (!isAnyAdminLoggedIn($session))) {
		$bError = true;
		$errStr = "ta";
	}	
	if (!$bError ) { 
		if (isAnyAdminLoggedIn($session)){
			// teamid depends on who is calling 
			if (!isset($teamid)){
				if (isUser($session, Role_TeamAdmin)){
					if (isset($session["teamid"])){
						$teamid = $session["teamid"];
					} 
				} else {
					if (isset($_GET["teamid"])){
						$teamid = $_GET["teamid"];
					} 
				}
			}
		?>
<h4>Filter Payment History Results</h4>
<div class="indented-group-noborder">
<script type="text/javascript">
function updateProgramID() {
	document.recentlyexpiredform.programid.value = document.selectprogramform.programid.value;
	document.forms['selectprogramform'].submit();
}
</script>
<form name="selectprogramform" action="/1team/payment-history.php" method="post">
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<input type="hidden" name="whomode" value="<?php echo $whomode ?>"/>
<input type="hidden" name="id" value="<?php echo $id ?>"/>
<input type="hidden" name="recentlyexpired" value="<?php echo $recentlyexpired?>"/>
<?php buildRequiredPostFields($session) ?>
<?php 
			$dbh = getDBH($session);  
			// GEt payment methods for this team 
			$strSQL = "SELECT * FROM programs WHERE teamid = ?";
			$pdostatementP = $dbh->prepare($strSQL);
			$bError = ! $pdostatementP->execute(array($teamid));
			$programResults = $pdostatementP->fetchAll();
			$rowCountP = count( $programResults);
			
			// Display programs for this team
			if ($rowCountP > 0) { 
				$countRowsP = 0; ?>
<table class="noborders">
<tr><td class="bold">Display payment for program</td><td>
<select name="programid" onchange="updateProgramID();">
<option value="<?php echo Program_Undefined?>" <?php if ($programid == Program_Undefined) echo " selected"?>>All programs</option>
<?php 
				while ($countRowsP < $rowCountP) {
					echo '<option value="';
					echo $programResults[$countRowsP]["id"];
					echo '"';
					if ($programid == $programResults[$countRowsP]["id"]) echo " selected";
					echo ">";
					echo $programResults[$countRowsP]["name"];
					echo "</option>\n";
					$countRowsP ++;
				} ?>
</select>				
</td></tr>
</table>
</form>
<?php 
			} else {
				echo '<p>There are no programs defined for the team ' . $teamname . '. <a href="/1team/manage-programs-form.php?' . returnRequiredParams($session) . '&teamid=' . $teamid .'">Define programs</a>.';
			}  
		} ?>
<script type="text/javascript">
function updateRecentlyExpired() {
	document.selectprogramform.recentlyexpired.value = document.recentlyexpiredform.recentlyexpired.value;
	document.forms['recentlyexpiredform'].submit();
}
</script>
<form name="recentlyexpiredform" action="/1team/payment-history.php" method="post">
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<input type="hidden" name="whomode" value="<?php echo $whomode ?>"/>
<input type="hidden" name="id" value="<?php echo $id ?>"/>
<input type="hidden" name="programid" value="<?php echo $programid?>"/>
<?php buildRequiredPostFields($session) ?>
<table class="noborders">
<tr><td class="bold">Show orderitems expired in past</td><td>
<select name="recentlyexpired" onchange="updateRecentlyExpired();">
<option value="1 day" <?php if ($recentlyexpired == "1 day") echo " selected"?>>1 day</option>
<option value="1 week" <?php if ($recentlyexpired == "1 week") echo " selected"?>>1 week</option>
<option value="1 mon" <?php if ($recentlyexpired == "1 mon") echo " selected"?>>1 month</option>
<option value="3 mon" <?php if ($recentlyexpired == "3 mon") echo " selected"?>>3 months</option>
<option value="1 year" <?php if ($recentlyexpired == "1 year") echo " selected"?>>1 year</option>
</select>				
</td></tr>
</table>
</form>
</div>
<?php 
		
		// Display the payment history
		$pageMode = "standalone";
		include('include-payment-history.php'); 
	}
} 

if ($bError) { ?>
<h4 class="usererror">Error: <?php echo $errStr?></h4>
<p><a href="javascript:void(0);" onclick="history.go(-1)">Back</a></p>
<?php 
}
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "The payment was not saved successfully: " . $_GET["err"], "");
} else if (isset($_GET["done"])){
	showMessage("Success", "The payment was changed successfully.");
} 

// Start footer section
include('footer.php'); ?>
</body>
</html>