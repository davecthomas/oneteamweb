<?php
$title= " Reset Password" ;
include('header.php');

$bError = false;
if (isset($_GET["id"])) {
	$userid = (int)(getCleanInput($_GET["id"]));
} else {
	$userid = 0;
}
if (isset($_GET["teamid"])) {
	$teamid = (int)(getCleanInput($_GET["teamid"]));
} else {
	$teamid = 0;
}

$dbconn = getConnection();

// Team admin must get team id from session
if ( isUser($session, Role_TeamAdmin)) {
	$teamid = $session["teamid"];
	$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid FROM users, useraccountinfo WHERE status = " . UserAccountStatus_Active . " and users.teamid = " . $teamid . " and users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
} else if ( isUser($session, Role_ApplicationAdmin)){
	$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid FROM users, useraccountinfo WHERE status = " . UserAccountStatus_Active . " and users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
} ?>
<?php
if (isValidUserID( $userid)) {
?>
<h3><?php echo $title . " for " . getUserName($userid)?></h3>
<?php
} else { ?>
<h3><?php echo $title?></h3>
<?php
} ?>
<div class="indented-group-noborder">
<form action="/1team/reset-password.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<?php
	// If no valid user ID, build a list to allow selection of user
	if ( isValidUserID( $userid)) { ?>
<input type="hidden" name="id" value="<?php echo $userid ?>"/>
<?php
	} ?>
<table class="noborders">
<?php
	// Conditionally generate a user select list
	if (( !isValidUserID( $userid))  && (isAnyAdminLoggedIn($session))){ ?>
<tr>
<td class="bold"><?php echo $teamterms["termuser"] ?> name:</td>
<td><select name="id">
<?php
		$dbconn = getConnection();
		$results = executeQuery($dbconn, $strSQL);
		echo("<option value=\"0\" selected>Select member...</option>");
		foreach($results as $row) {
			$id = $row["id"];
			echo "<option value=\"";
			echo $row["id"];
			echo "\"";
			if ( $userid == $id ) {
				echo("selected");
			}
			echo ">";
			echo trim($row["firstname"]);
			echo " ";
			echo $row["lastname"]);
			echo " " . roleToStr($row["roleid"], $teamterms) ;
			echo "</option>\n";
		} ?>
</select></td>
</tr>
<?php
	} ?>
<tr>
<td class="bold">Email new password to&nbsp;<?php echo $teamterms["termuser"] ?>?</td>
<td><input type="checkbox" name="sendemail" value="1" checked="checked"></td>
</tr>
</table>
</div>
<input type="submit" value="Reset Password" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'">
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'home.php?<?php echo returnRequiredParams($session)?>'"/>
</form>
<?php
// Start footer section
include('footer.php'); ?>
</body>
</html>
