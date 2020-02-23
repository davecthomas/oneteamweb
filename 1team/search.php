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
$dbconn = getConnectionFromSession($session);
// Search will not return the Application Admin since it has teamid=0 or NULL
if (isUser( $session, Role_ApplicationAdmin) ) {
	// PostgreSQL: $strSQL = "SELECT teams.name as teamname, users.firstname, users.lastname, users.id as userid, users.roleid, users.imageid, useraccountinfo.status, useraccountinfo.isbillable, images.* FROM useraccountinfo, teams RIGHT OUTER JOIN images RIGHT OUTER JOIN users ON users.imageid = images.id ON images.teamid = teams.id WHERE users.useraccountinfo = useraccountinfo.id AND (firstname ILIKE '%'||?||'%' or lastname ILIKE '%'||?||'%' or useraccountinfo.email ILIKE '%'||?||'%') ORDER BY " . $sortRequest .";";
	// MySQL: 
	$strSQL = <<<EOD
	SELECT teams.name as teamname, teams.id as teamteamid, users.firstname, users.lastname, 
users.id as userid, users.roleid, 
users.imageid, users.teamid as userteamid, 
useraccountinfo.status, useraccountinfo.isbillable, images.url, images.id as imageimageid, images.filename 
FROM useraccountinfo, images 
RIGHT OUTER JOIN users 
RIGHT OUTER JOIN teams 
ON users.teamid = teams.id 
ON users.imageid = images.id
WHERE users.useraccountinfo = useraccountinfo.id 
AND (firstname LIKE CONCAT('%', ?, '%') 
or lastname LIKE CONCAT('%', ?, '%') 
or useraccountinfo.email LIKE CONCAT('%', ?, '%'))
EOD;
	$strSQL .= " ORDER BY ". $sortRequest;
	// SELECT teams.name as teamname, users.firstname, users.lastname, users.id as userid, users.roleid, users.imageid, users.teamid as teamid, useraccountinfo.status, useraccountinfo.isbillable, images.* FROM useraccountinfo, teams RIGHT OUTER JOIN images RIGHT OUTER JOIN users ON users.imageid = images.id ON images.teamid = teams.id WHERE users.useraccountinfo = useraccountinfo.id AND (firstname LIKE CONCAT('%', ?, '%') or lastname LIKE CONCAT('%', ?, '%') or useraccountinfo.email LIKE CONCAT('%', ?, '%')) ORDER BY " . $sortRequest .";";
	$user_results = executeQuery($dbconn, $strSQL, $bError, array($strSearch, $strSearch, $strSearch));
} else { 
	$strSQL =<<<EOD
	SELECT teams.name as teamname, teams.id as teamteamid, users.firstname, users.lastname, 
users.id as userid, users.roleid, 
users.imageid, users.teamid as userteamid, 
useraccountinfo.status, useraccountinfo.isbillable, images.url, images.id as imageimageid, images.filename 
FROM useraccountinfo, images 
RIGHT OUTER JOIN users 
RIGHT OUTER JOIN teams 
ON users.teamid = teams.id 
ON users.imageid = images.id
WHERE users.useraccountinfo = useraccountinfo.id 
AND users.teamid = ?
AND (firstname LIKE CONCAT('%', ?, '%') 
or lastname LIKE CONCAT('%', ?, '%') 
or useraccountinfo.email LIKE CONCAT('%', ?, '%'))
EOD;
	$strSQL .= " " . $filterRequestSQL . " ORDER BY " . $sortRequest .";";
	// SELECT teams.name as teamname, users.firstname, users.lastname, users.id as userid, users.roleid, users.imageid, users.teamid as teamid, useraccountinfo.status, useraccountinfo.isbillable, images.* FROM useraccountinfo, teams RIGHT OUTER JOIN images RIGHT OUTER JOIN users ON users.imageid = images.id ON images.teamid = teams.id WHERE users.useraccountinfo = useraccountinfo.id AND users.teamid = ? and (firstname LIKE CONCAT('%', ?, '%') or lastname LIKE CONCAT('%', ?, '%') or useraccountinfo.email LIKE CONCAT('%', ?, '%')) " . $filterRequestSQL . " ORDER BY " . $sortRequest .";";
	if (strlen($filterRequestSQL) > 0) {
		$user_results = executeQuery($dbconn, $strSQL, $bError, array($teamid, $strSearch, $strSearch, $strSearch, $filterRequest));
	} else {
		$user_results = executeQuery($dbconn, $strSQL, $bError, array($teamid, $strSearch, $strSearch, $strSearch, $teamid));
	}
}

// Now that we've done the query, we need to strip the secondary sort column off.
$sortRequest = substr($sortRequest, 0, strpos($sortRequest, ","));
// If none found
if (count($user_results ) == 0) { ?>
<div id="bodycontent">
<h3><?php echo $title?></h3>
<?php
	echo "<p>No members found.<br>\n";
// If only one result, go to props page for that user
} else if (count($user_results) == 1){
	redirect('user-props-form.php?'.returnRequiredParams($session).'&id='.$user_results[0]["userid"].'&teamid='.$user_results[0]["teamteamid"]);
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
