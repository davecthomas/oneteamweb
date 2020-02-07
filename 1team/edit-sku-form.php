<?php  
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Edit SKU"; 
include('header.php');
?>
<script type="text/javascript"> 
dojo.require("dijit.form.NumberSpinner");
dojo.require("dijit.form.DateTextBox");
dojo.require("dijit.form.CurrencyTextBox");
</script>

<?php
$dbh = getDBH($session); 
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

if (isset($_GET["id"])) {
	$levelid = $_GET["id"];
} else {
	$levelid = NotFound;
}

$strSQL = "SELECT * FROM skus WHERE id = ?;";
$pdostatement = $dbh->prepare($strSQL);
$pdostatement->execute(array($levelid));

$skuResults = $pdostatement->fetchAll();

if (count($skuResults) > 0) { ?>
<h4>Modify a SKU for <?php echo getTeamName2($teamid, $dbh)?></h4>	
<div class="indented-group-noborder">
<form name="modifysku" action="/1team/edit-sku.php" method="post"/>
<input type="hidden" name="id" value="<?php echo $levelid ?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<?php buildRequiredPostFields($session) ?>
<table class="noborders">
<tr><td class="strong">SKU name</td><td><input type="text" name="name" size="60" maxlength="80" value="<?php echo $skuResults[0]["name"]?>"></td></tr>
<tr><td class="strong">SKU price ($US)</td><td><input type="text" name="price" size="10" maxlength="10" value="<?php echo $skuResults[0]["price"]?>" dojoType="dijit.form.CurrencyTextBox" required="true" constraints="{fractional:true}" currency="USD"></td></tr>
<tr><td class="strong">Description</td><td><textarea rows="5" cols="120" value="" name="description" wrap="hard"><?php echo $skuResults[0]["description"]?></textarea></td></tr>
<tr><td class="strong">Program</td>
<td>
<?php
	$strSQL = "SELECT * FROM programs WHERE teamid = ?;";
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array($teamid));
	
	$programResults = $pdostatement->fetchAll();

	$rowCount = 0;
	$loopMax = count($programResults);

	if (count($loopMax ) > 0) { ?>

<select name="programid" onchange="if (this.selectedIndex.value != <?php echo Program_Undefined?>) document.all.programnamelabel.innerText = this.options[this.selectedIndex].text">
<?php 
		if ((empty($skuResults[0]["programid"])) || ($skuResults[0]["programid"] == Program_Undefined)) {?>
<option value="<?php echo Program_Undefined?>" selected>Select Program...</option>
<?php
		}
		while ($rowCount < $loopMax) { 
			echo  "<option "; 
			echo  'value="' . $programResults[$rowCount]["id"] . '"';
			if ($programResults[$rowCount]["id"] == $skuResults[0]["programid"]) {
				echo " selected ";
				$programname = $programResults[$rowCount]["name"]; 
			}
			echo  ">";
			echo $programResults[$rowCount]["name"];
			echo  "</option>";
			$rowCount++;
		}?>
</select>
<?php 
	}  else {
		echo 'No programs are defined for ' . $teamname . '. <a href="/1team/manage-programs-form.php?teamid=' . $teamid . buildRequiredParamsConcat($session) . '">Define Programs</a>.';
	} ?>
</td></tr>
<tr><td class="strong">Number of <div id="programnamelabel"><?php echo $programname?></div> events</td>
<td>
<div class="
<?php 
	if ($skuResults[0]["numevents"] == Expiration_Never) { 
		echo "hideit";
	} else {
		echo "showit";
	} ?>
" id="numeventsdiv">
<input dojoType="dijit.form.NumberSpinner"
				value="<?php 
	if ((empty($skuResults[0]["numevents"])) || ($skuResults[0]["numevents"] == Sku::NumEventsUndefined)) {
		echo "1";
	} else {
		echo $skuResults[0]["numevents"];
	}?>"
				constraints="{min:1,max:1000,places:0}"
				name="numevents"
				id="numevents"
				promptMessage="Enter the number of events this SKU contains"
				maxlength="4"
				style= "width:70px">
</div>
<input type="checkbox" name="unlimitednumevents" <?php if ($skuResults[0]["numevents"] == skuExpiration_Never) echo "checked='checked'";?> onchange="getUnlimitedSetting()"/>Unlimited
</td></tr>
<tr><td class="strong">This SKU expires</td>
<td><input dojoType="dijit.form.NumberSpinner"
				value="<?php 
	if ((empty($skuResults[0]["expires"])) || ($skuResults[0]["expires"] == skuExpiration_Undefined)) {
		echo "1";
	} else {
		echo getInterval($skuResults[0]["expires"]);
	}?>"
				constraints="{min:1,max:1000,places:0}"
				name="expirationnum"
				id="expirationnum"
				promptMessage="Enter the time interval for the SKU to expire"
				maxlength="4"
				style= "width:70px">
<select name="expirationunits" style="">
<option value="<?php echo skuExpirationUnits_Days?>" <?php if ((empty($skuResults[0]["expires"])) || ($skuResults[0]["expires"] == skuExpirationUnits_Undefined) || (getIntervalUnits($skuResults[0]["expires"]) == skuExpirationUnits_Days)) echo ' selected';?>>Days</option>
<option value="<?php echo skuExpirationUnits_Weeks?>" <?php if (getIntervalUnits($skuResults[0]["expires"]) == skuExpirationUnits_Weeks) echo " selected"?>>Weeks</option>
<option value="<?php echo skuExpirationUnits_Months?>"<?php if (getIntervalUnits($skuResults[0]["expires"]) == skuExpirationUnits_Months) echo " selected"?>>Months</option>
<option value="<?php echo skuExpirationUnits_Years?>" <?php if (getIntervalUnits($skuResults[0]["expires"]) == skuExpirationUnits_Years) echo " selected"?>>Years</option>
</select>&nbsp;after purchase.</td></tr>
<tr><td class="strong">SKU list order</td><td><?php if (is_null($skuResults[0]["listorder"]) ) echo "Not defined"; else echo $skuResults[0]["listorder"]?></td></tr>
</table>
</div>
<input type="submit" value="Modify SKU" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'manage-skus-form.php<?php buildRequiredParams($session) ?>'"/>
</form>
<?php 
} 
// Start footer section
include('footer.php'); ?>
<script type="text/javascript">

function getUnlimitedSetting(){
	if (document.modifysku.unlimitednumevents.value != "on"){
		showit('numeventsdiv');
	} else {
		hideit('numeventsdiv');
	}
}

</script>
</body>
</html>