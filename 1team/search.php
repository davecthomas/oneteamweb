<?php
ob_start(); 	// This caches non-header output, allowing us to redirect after header.php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;

$title = "Search: ";
include('header.php');
// This accept str for search as either post or get, since the sortable version called from the included include-member-roster needs to pass search thru again
if (isset($_REQUEST["str"])) {
	$strSearch = $_REQUEST["str"];
} else {
 	redirect( $_SERVER['HTTP_REFERER'] . "&err=1");
}
$title .= "'" . $strSearch . "'";

$dbh = getDBH($session);  

$rowCount = 0;
// Set up teamid from session or input
$bError = FALSE;
// TeamID is passed in for application admin,  else { it's in the session
if ((!isUser( $session, Role_ApplicationAdmin)) && (isset($session["teamid"]))) {
	$teamid = $session["teamid"];
} else {
 	if (isset($_REQUEST["teamid"])) $teamid= $_REQUEST["teamid"];
	else $teamid=TeamID_Undefined;
}

// set up sort order
$sortRequest = "firstname";
if (isset($_REQUEST["sort"])) {
	$sortRequest = trim($_REQUEST["sort"]) . "";
	$sortRequest = cleanSQL($sortRequest);
} else if (isset($_REQUEST["sort"])) {
	$sortRequest = trim($_REQUEST["sort"]) . "";
	$sortRequest = cleanSQL($sortRequest);
}
// Figure out how the roster should be filtered
$filterRequest = "";
if (isset($_REQUEST["filter"])) {
	$filterRequest = trim($_REQUEST["filter"]) . "";
	$filterRequest = cleanSQL($filterRequest);
	if (strlen($filterRequest) > 0 && ! isnumeric($filterRequest) ) {
		$filterRequestSQL = " AND ?"; 
	} else {
		$filterRequestSQL = "";
	}
} else {
	$filterRequestSQL = "";
}


if (isUser( $session, Role_ApplicationAdmin) ) {
	$strSQL = "SELECT teams.name as teamname, users.firstname, users.lastname, users.id as userid, users.roleid, users.imageid, useraccountinfo.status, useraccountinfo.isbillable, images.* FROM useraccountinfo, teams RIGHT OUTER JOIN images RIGHT OUTER JOIN users ON users.imageid = images.id ON images.teamid = teams.id WHERE users.useraccountinfo = useraccountinfo.id AND (firstname ILIKE '%'||?||'%' or lastname ILIKE '%'||?||'%' or useraccountinfo.email ILIKE '%'||?||'%') ORDER BY " . $sortRequest .";";
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array($strSearch, $strSearch, $strSearch));
} else {
	$strSQL = "SELECT teams.name as teamname, users.firstname, users.lastname, users.id as userid, users.roleid, users.imageid, useraccountinfo.status, useraccountinfo.isbillable, images.* FROM useraccountinfo, teams RIGHT OUTER JOIN images RIGHT OUTER JOIN users ON users.imageid = images.id ON images.teamid = teams.id WHERE users.useraccountinfo = useraccountinfo.id AND users.teamid = ? and (firstname ILIKE '%'||?||'%' or lastname ILIKE '%'||?||'%' or useraccountinfo.email ILIKE '%'||?||'%') " . $filterRequestSQL . " ORDER BY " . $sortRequest .";";
	$pdostatement = $dbh->prepare($strSQL);
	if (strlen($filterRequestSQL) > 0) { 
		$pdostatement->execute(array($teamid, $strSearch, $strSearch, $strSearch, $filterRequest));
	} else {
		$pdostatement->execute(array($teamid, $strSearch, $strSearch, $strSearch, $teamid));
	}
}
$results = $pdostatement->fetchAll();


// Now that we've done the query, we need to strip the secondary sort column off. 
$sortRequest = substr($sortRequest, 0, strpos($sortRequest, ","));

// If none found
if (count($results ) == 0) { ?>
<div id="bodycontent">
<h3><?php echo $title?></h3>
<?php
	echo "<p>No members found.<br>\n";
// If only one result, go to props page for that user
} else if (count($results) == 1){ 
	redirect('user-props-form.php?'.returnRequiredParams($session).'&id='.$results[0]["userid"]);
// If multiple results show roster
} else { ?>
<div id="bodycontent">
<h3><?php echo $title?></h3>
<?php
	// More than one found, include roster
	$pagemode = pagemodeSearch;
	$referrer = "search.php";
	include('include-member-roster.php'); 
}
// Start footer section
include('footer.php'); 
ob_end_flush(); ?>
</body>
</html>