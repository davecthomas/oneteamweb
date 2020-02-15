<?php
include_once('utils.php');
// Assure we have the input we need, else send them to default.php
if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid = getUserID()) == RC_RequiredInputMissing)){
	redirect("default.php?rc=" . RC_RequiredInputMissing);
}
// Get $session array initialized
$session = startSession($sessionkey, $userid);
if (! isValidSession($session )){
	redirectToLogin();
}

redirectToLoginIfNotAdmin( $session);

$whomode = "user";
if (isset($_GET["whomode"])){
	$whomode = $_GET["whomode"];
}

if (isset($_GET["teamid"])){
	$teamid = $_GET["teamid"];
}

if (isset($_GET["date"])){
	$eventdate = new DateTime( $_GET["date"]);
} else {
	$eventdate = new DateTime( );
}

// Override teamid with session setting id is required and you must be able to admin this id
if (isUser($session, Role_TeamAdmin)) {
	$teamid = $session["teamid"];
}

if (($whomode == "user" ) && (isset($_GET["id"]))){
	$userid = $_GET["id"];
	if (!canIAdministerThisUser( $session, $userid)) redirectToLogin();
}

if (isset($_GET["attendanceid"])){
	$attendanceid = $_GET["attendanceid"];
} else {
	redirectToLogin();
}

$strSQL = "DELETE FROM attendance WHERE id = ?;";
$dbconn = getConnectionFromSession($session);
$results = executeQuery($dbconn, $strSQL, $attendanceid));

if ($whomode == "team") {
	redirect("include-attendance-table.php?" . returnRequiredParams($session) . "&whomode=" . $whomode . "&id=" . $teamid . "&pagemode=standalone&EventDate=01-" . $eventdate->format("m") . "-" . $eventdate->format("Y"));
} else {
	redirect("include-attendance-table.php?" . returnRequiredParams($session) . "&whomode=" . $whomode . "&id=" . $userid . "&pagemode=standalone&EventDate=01-" . $eventdate->format("m") . "-" . $eventdate->format("Y") );
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
