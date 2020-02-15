<?php
include('utils.php');

// Assure we have the input we need, else send them to default.php
if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid = getUserID()) == RC_RequiredInputMissing)){
	redirect("default.php?rc=" . RC_RequiredInputMissing);
}
// Get $session array initialized
$session = startSession($sessionkey, $userid);
if (! isValidSession($session )){
	redirect("default.php?rc=" . $session);
}

$bError = false;

if (isset($_GET["teamid"])) {
	$teamid = (int)(getCleanInput($_GET["teamid"]));
} else {
	$teamid = 0;
}
if (isset($_GET["isactive"])) {
	if ((int)(getCleanInput($_GET["isactive"])) == 1){
		$isactive = 1;
	}
} else {
	$isactive = 0;
}

redirectToLoginIfNotAdmin( $session);

$dbconn = getConnectionFromSession($session);

// Team admin must get team id from session
if ( isUser( $session, Role_TeamAdmin)) {
	$teamid = $session["teamid"];
	if ($isactive == 1) {
		$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid, users.teamid, useraccountinfo.status, useraccountinfo.isbillable, useraccountinfo.email FROM users, useraccountinfo WHERE users.teamid = ? AND status > 0 and users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
		$users = executeQuery($dbconn, $strSQL, $bError, array($teamid));
	} else {
		$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid, users.teamid, useraccountinfo.status, useraccountinfo.isbillable, useraccountinfo.email FROM users, useraccountinfo WHERE users.teamid = ? and users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
		$users = executeQuery($dbconn, $strSQL, $bError, array($teamid));
	}
} else {
	$strSQL = "SELECT users.firstname, users.lastname, users.id, users.roleid, useraccountinfo.status, useraccountinfo.isbillable, useraccountinfo.email FROM users, useraccountinfo WHERE users.useraccountinfo = useraccountinfo.id ORDER BY firstname;";
	$users = executeQuery($dbconn, $strSQL, $bError, array($teamid));
}


@ob_start();
include "config.php";
$content_file = "";
include "open_db.php";
$isAppAdmin = isUser($session, Role_ApplicationAdmin);
$content_file .= "First, Last, Role, Account Status, Email";
if ($isAppAdmin) $content_file .=  ", Team";
$content_file .= "\r\n";
foreach ($users as $row) {
	$content_file .= $row["firstname"] . ',' . $row["lastname"] . ',' . roleToStr( $row[ "roleid"], $teamterms) . ',' . $aStatus[(int)$row[ "status"]+UserAccountStatus_ArrayOffset] . ',' . $row["email"];
	if ($isAppAdmin) $content_file .=  ',' . $row["teamid"];
	$content_file .=  "\r\n";
}
if ( isUser( $session, Role_TeamAdmin)) {
	$output_file = getTeamName($teamid, $dbconn) . ".csv";
} else {
	$output_file = appname_nowhitespace . ".csv";
}
@ob_end_clean();
@ini_set('zlib.output_compression', 'Off');
header('Pragma: public');
header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: pre-check=0, post-check=0, max-age=0');
header('Content-Transfer-Encoding: none');
//This should work for IE & Opera
header('Content-Type: application/octetstream; name="' . $output_file . '"');
//This should work for the rest
header('Content-Type: application/octet-stream; name="' . $output_file . '"');
header('Content-Disposition: inline; filename="' . $output_file . '"');
echo $content_file;
exit();
?>
