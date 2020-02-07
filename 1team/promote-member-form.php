<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title= " Promote " ;
include('header.php'); ?>
<script type="text/javascript"> 
dojo.require("dijit.form.DateTextBox");
</script>

<?php
$bError = FALSE;
if (!isset($_GET["teamid"])) {
	if (isset($session["teamid"])) {
		$teamid = $session["teamid"];
	} else {
		redirect($_SERVER['HTTP_REFERER'] . "&err=t");
	}
} else {
	$teamid = trim($_GET["teamid"]);
}
if ((strlen($teamid) > 0) && (is_numeric($teamid)) ) {
	$teamid = (int)($teamid);

// if ( there was no passed in teamID, 
} else {
	if (!isAdminLoggedIn($session) ) {
		$teamid = $session["teamid"];
	} else { 
		$bError = true;
	}
}

if (isset($_GET["id"])){
	$userid = trim($_GET["id"]);
} else {
	// This setting will force select list to be uninitialized
	$userid = 0;
} 
$dbh = getDBH($session);  
?>
<h3><?php echo $title?> <?php echo getTeamName2($teamid, $dbh)?>&nbsp;<?php echo $teamterms ["termmember"]?></h3>
<?php 
if (isset($_GET["badpromo"])){
	showError("Error Promoting", "There was an error promoting the " . $teamterms["termmember"], "");
}
?>
<?php
if ( ! $bError ) { ?>
<form action="/1team/promote-member.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<p>
<table class="noborders">
<?php
	$strSQL = "SELECT users.firstname, users.lastname, users.id, useraccountinfo.status, useraccountinfo FROM users, useraccountinfo WHERE roleid & " . Role_Member . " = " . Role_Member . " AND users.teamid = ? AND users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status <> ? ORDER BY firstname;";
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array( $teamid, UserAccountStatus_Inactive));		
	$promolistorder = 0; 
?>
<tr>
<td class="bold">Member name:</td>
<td><select name="id">
	<?php 
	if ( $userid == 0 ) {
		echo('<option value="0" selected>Select user...</option>');
	}
	foreach ($pdostatement as $row) { 
		echo '<option value="';
		echo $row["id"];
		echo '"';
		if ( $userid == $row["id"] ) {
			echo(" selected");
			$promolistorder = $row["id"]+1; 
			$strSQL = "select listorder from levels, promotions where promotions.newlevel = levels.id and memberid = ? and levels.teamid = ? and promotiondate = (select max(promotiondate) from promotions where memberid = ? and promotions.teamid = ?)";
			$pdostatementlistorder = $dbh->prepare($strSQL);
			if ($pdostatementlistorder->execute(array($userid, $teamid, $userid, $teamid)))		
				$promolistorder = $pdostatementlistorder->fetchColumn() + 1;
		}
		echo ">";
		echo trim($row["firstname"]);
		echo " ";
		echo trim($row["lastname"]);
		echo "</option>";
	} ?>
</select></td>
</tr>
<tr>
<td class="bold">Date</td>
<td><input type="text" name="date" id="date" value="<?php echo date("Y-m-d")?>" dojoType="dijit.form.DateTextBox" required="true" promptMessage="mm/dd/yyyy"/>
</td>
<td class="bold">Select new level</td>
<td><select name="level"> 
<?php
	$strSQL = "SELECT * FROM levels WHERE teamid = ? ORDER BY programid, listorder;";
	$pdostatementlevel = $dbh->prepare($strSQL);
	$pdostatementlevel->execute(array($teamid));		
		
	foreach ($pdostatementlevel as $rowlevel) { 
		echo "<option value='";
		echo $rowlevel["id"];
		echo "'";
		if ( $promolistorder == $rowlevel["listorder"] ) {
			echo(" selected");
		}
		echo ">";
		echo$rowlevel["name"];
		echo "</option>\n"; 
	} ?>
</select>
</td>
</tr>
</table>		   
<input type="submit" class="btn" value="Promote" name="promote" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'include-promotions.php<?php buildRequiredParams($session) ?>&whomode=user&id=<?php echo $userid?>&teamid=<?php echo $teamid?>&pagemode=standalone'"/>
</form>
<?php
	} 
if ( $bError ) { ?>
<h4 class="usererror">Error: <?php echo errorStr?></h4>
<p><a href="javascript:void(0);" onclick="history.go(-1)">Back</a></p>
<?php
} 
// Start footer section
include('footer.php'); ?>
</body>
</html>