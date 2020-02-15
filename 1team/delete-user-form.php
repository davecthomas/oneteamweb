<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title= " Delete User" ;
include('header.php');

$dbconn = getConnectionFromSession($session);
$bError = false;
if (isset($_GET["id"])) {
	$userid = (int)(getCleanInput($_GET["id"]));
} else {
	$userid = 0;
}
// teamid depends on who is calling
if (isUser($session, Role_TeamAdmin)){
	if (isset($session["teamid"])){
		$teamid = $session["teamid"];
	} else {
		$teamid = TeamID_Undefined;
	}
	$title .= " for " . getTeamName($teamid );

	$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid FROM users WHERE (roleid & " . Role_TeamAdmin . ") <> " . Role_TeamAdmin . " AND users.teamid = ? ORDER BY firstname;";
	$userResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));
} else {
	if (isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	} else {
		$teamid = 0; // This should trigger a select list for app admin
	}
	$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid FROM users WHERE (roleid & " . Role_TeamAdmin . ") <> " . Role_TeamAdmin . " ORDER BY firstname;";
	$userResults = executeQuery($dbconn, $strSQL, $bError);
}

$rowCount = 0;
$numRows = count($userResults);
?>
<h3><?php echo $title?></h3>
<p>Typically, deleting <?php echo $teamterms["termuser"]?>s is not required. Consider changing their status instead so you can maintain a record of your customers.
Deletion will include all previous orders, attendance at events, and promotions related to the <?php echo $teamterms["termuser"]?>.</p>
<?php
// If we were directed here from a previously deleted user, show completion status text
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "The " . $teamterms["termmember"] . " was not deleted successfully.", "");
} else if (isset($_GET["done"])){
	if (isset($_GET["deleteduser"])) {
		$deleteduser = $_GET["deleteduser"];
	} else {
		$deleteduser = "";
	}

	showMessage("Success", "The " . $teamterms["termmember"] . " $deleteduser was deleted successfully.");
}

// If no members, tell them so and don't display the form
if ($numRows == 0) {
	echo "<p>No " . $teamterms["termmember"]. "s exist in the team " .getTeamName($teamid, $dbconn) . "<br>\n";
	echo '<a href="/1team/new-user-form.php?' . returnRequiredParams($session) . '">Create a team member</a></p>';
	echo "\n";
	$bOkForm = false;
} else {
	$bOkForm = true;
}
if ($bOkForm ){ ?>
<form name="deleteform" action="/1team/delete-user.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<p>
<table class="noborders">
<tr>
<td class="bold"><?php echo $teamterms["termuser"] ?> name:</td>
<td><select name="id">
<?php
	if ( $userid == 0 ) {
		echo("<option value=\"0\" selected>Select member...</option>");
	}
	while($rowCount < $numRows) {
		echo "<option value=\"";
		echo $userResults[$rowCount]["id"];
		echo "\"";
		if ( $userid == $userResults[$rowCount]["id"] ) {
			echo("selected");
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
<tr><td><input type="button" value="Delete" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onClick="confSubmit(this.form);"></td>
</tr>
</table>
</form>
<script type="text/javascript">
function confSubmit(form) {
	if (confirm("Are you sure you want to delete this user?")) {
		form.submit();
	}
}
</script>
<?php
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
