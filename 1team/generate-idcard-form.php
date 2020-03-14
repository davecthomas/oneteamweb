<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Generate ID Card";
include('header.php');
	echo("1");
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
if (isset($_GET["id"])){
	$userid = trim($_GET["id"]);
} else {
	// This setting will force select list to be uninitialized
	$userid = User::UserID_Undefined;
}

if (!$bError) {
	$dbconn = getConnectionFromSession($session);
	$teamname = getTeamName($teamid, $dbconn);

	// Conditionally include user name in title
	if ( $userid != User::UserID_Undefined ) {
		$bDisplayUserSelector = false;?>
<h3><?php echo $title?>&nbsp;for&nbsp;<a href="user-props-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $userid?>"><?php echo getUserName( $userid, $dbconn)?></a></h3>
<?php
	} else {
		$bDisplayUserSelector = true;
		echo '<h3>' . $title . ' for ' . $teamname . ' ' . $teamterms["termmember"] . '</h3>';
	}
	// If invalid userID, Build a select list of all billable and active students to allow selection
	if ($bDisplayUserSelector) {
		// Team admin must get team id from session
		if (!isUser( $session, Role_ApplicationAdmin)) {
			$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid FROM users, useraccountinfo WHERE status = " . UserAccountStatus_Active . " and users.teamid = ? and users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
			$userResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));
		// App Admin query isn't team specific
		} else {
			$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid FROM users, useraccountinfo WHERE status = " . UserAccountStatus_Active . " and users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
			$$userResults = executeQuery($dbconn, $strSQL, $bError);
		}
		$countRows = 0;
		$numRows = count($userResults);

		// If no members, tell them so and don't display the form
		if ($numRows == 0) {
			echo "<p>No " . $teamterms["termmember"]. "s exist in the team " .getTeamName($teamid, $dbconn) . "<br>\n";
			echo '<a href="/1team/new-user-form.php?' . returnRequiredParams($session) . '">Create a team member</a></p>';
			echo "\n";
			$bOkForm = false;
		}

	} ?>
<h4><a class="linkopacity" href="javascript:togglevis('instructions')">Instructions for Membership Cards<img src="img/a_expand.gif" alt="expand section" id="instructions_img" border="0"></a></h4>
<div class="hideit" id="instructions">
<div class="indented-group-noborder">
<ol>
<li>Print the generated ID Cards page duplex (both sides of the paper). The printouts work best with Firefox browser. Set the left margin to .7 (default is .5).</li>
<li>Heat laminate the sheet (FedexKinkos does it for about $4)</li>
<li>Cut the laminated cards and round the sharp corners (again, FedexKinkos has simple tools for this)</li>
<li>Hand out the cards to your members</li>
<li>Get a <a href="http://www.amazon.com/Contact-Barcode-Scanner-Rugged-Design/dp/B000GGTTC8/ref=pd_bbs_sr_1?ie=UTF8&s=electronics&qid=1210964398&sr=8-1" target="_blank">USB Bar Code Reader</a> and plug it into your attendance console's USB port</li>
<li>Log member attendance here: <a href="scan-form.php<?php buildRequiredParams($session) ?>">Start Attendance Scanning</a>, where members pass their ID card under the bar code reader.
</ol>
</div>
</div>
<form action="/1team/generate-idcard.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<p class="attention">
Select an option to generate one or more member identification cards in bar code</p>
<?php
	if ( $bDisplayUserSelector ) { ?>
<div class="indented-group-noborder">
<select multiple="multiple" name="id[]">
<?php
		echo('<option value="'.User::UserID_Undefined.'" selected>Select user...</option>');
		echo('<option value="'.GenerateAllMembers.'">All ' . $teamterms["termmember"] . 's of ' . $teamname . '</option>');
		$datetime = new DateTime();
		$lastmonthdatetime = new DateTime($datetime->format("d-m-Y") . " -1 month");
		echo('<option value="'.GenerateLatestMembers.'">' . $teamterms["termmember"] . 's of ' . $teamname . ' joined since ' . $lastmonthdatetime->format("F d, Y"). '</option>');
		while ($countRows < $numRows) {
			echo "<option value=\"";
			echo $userResults[$countRows]["id"];
			echo "\"";
			if ( $userid == $userResults[$countRows]["id"] ) {
				echo("selected");
			}
			echo ">";
			echo $userResults[$countRows]["firstname"];
			echo " ";
			echo $userResults[$countRows]["lastname"];
			echo " " . roleToStr($userResults[$countRows]["roleid"], $teamterms) ;
			echo "</option>\n";
			$countRows ++;
		}  ?>
</select><br/>
<?php
	} ?>
<input type="submit" value="Generate ID Card" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'">
</form>
</div>
<?php
}
if ( $bError ) { ?>
<h4 class="usererror">Error: <?php echo $err?></h4>
<?php
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
