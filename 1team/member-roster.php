<?php
// Only admins and coaches can execute this script. Header.php enforces this.
$isadminorcoachrequired = true;

$title = "Member Roster";
include('header.php');
$dbh = getDBH($session);  

$rowCount = 0;
// Set up teamid from session or input
$bError = FALSE;

// teamid depends on who is calling 
if ( !isUser($session, Role_ApplicationAdmin)){
	if ( !isset($session["teamid"])){
		$bError = true;
		$err = "A team must be selected.";
	} else {
		$teamid = $session["teamid"];
	}
} else {
	if ( isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	} else {
		$bError = true;
		$err = "A team must be selected, Admin!";
	}
}

if (!$bError) {
	// set up sort order
	$sortRequest = "firstname";
	if (isset($_GET["sort"])) {
		$sortRequest = trim($_GET["sort"]) . "";
		$sortRequest = cleanSQL($sortRequest);
	} 
	
	// $isBillable triggers special features on this page for next payment due
	$isBillable = 0;
	if (isset($_GET["isbillable"]) && is_numeric($_GET["isbillable"])) {
		$isBillable = $_GET["isbillable"];
	}
	
	// Figure out how the roster should be filtered
	$filterRequest = "";
	if (isset($_GET["filter"])) {
		$filterRequest = trim($_GET["filter"]);
		$filterRequest = cleanSQL($filterRequest);
		if ((strlen($filterRequest) > 0) && (! is_numeric($filterRequest)) ) {
			$filterRequestSQL = " AND " . $filterRequest; 
		} else {
			$filterRequestSQL = "";
		}
	} else {
		$filterRequestSQL = "";
	}
	
	$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, users.roleid, users.imageid, useraccountinfo.status, useraccountinfo.isbillable, images.* FROM useraccountinfo, teams RIGHT OUTER JOIN images RIGHT OUTER JOIN users ON users.imageid = images.id ON images.teamid = teams.id WHERE users.useraccountinfo = useraccountinfo.id AND users.teamid = ? " . $filterRequestSQL . " ORDER BY " . $sortRequest . ";";
		
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array($teamid));
	$results = $pdostatement->fetchAll();
	
	// Now that we've done the query, we need to strip the secondary sort column off. 
	$sortRequest = substr($sortRequest, 0, strpos($sortRequest, ",")); ?>
<div id="bodycontent">
<?php 
	// Allow app admin to browse all teams
	if (isUser( $session, Role_ApplicationAdmin)){ 
	
		// While it may seem inefficient to query the count of teams every time this page is rendered, this only affects the app admin (me), so tough noogies, Dave.
		$strSQL2 = "SELECT COUNT(*) AS ROW_COUNT FROM teams;";
		$pdostatement2 = $dbh->prepare($strSQL2);
		$pdostatement2->execute();
		$numteams = $pdostatement2->fetchColumn();?>
			
<div class="navtop">
<ul id="nav">
<li><div align="left"><a id="prev" class="linkopacity" 
<?php
		if ($teamid > 1) {?>
href="member-roster.php<?php buildRequiredParams($session) ?>&teamid=<?php echo (int)($teamid-1)?>"
<?php
		}
?>
><img src="img/a_previous.gif" border="0" alt="previous">Previous team</a></div></li>	
<li><div align="right"><a id="next" class="linkopacity" 
<?php
		if (($teamid+1) <= $numteams) { ?>
href="member-roster.php<?php buildRequiredParams($session) ?>&teamid=<?php echo (int)($teamid+1)?>"
<?php
		}?>
>Next team<img src="img/a_next.gif" border="0" alt="next"></a></div></li>
</ul>
</div>

<?php
	} 
	
	// If none found
	if (count($results) == 0) { ?>
<h3><?php echo $title?></h3>
<?php
		echo "<p>No members found.<br>\n";
// If only one result, go to props page for that user
	} else { ?>
<div id="bodycontent">
<h3><?php echo $title?> for <?php echo getTeamName2($teamid, $dbh)?></h3>
<?php
		// More than one found, include roster
		$pagemode = pagemodeRoster;
		$referrer = "member-roster.php";
		include('include-member-roster.php'); 
	}
// Error
} else {
	echo '<p class="error">' . $err . '</p>';
}
// Start footer section
include('footer.php'); ?>
</body>
</html>