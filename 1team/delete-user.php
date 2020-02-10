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
// Only admins can execute this script
redirectToLoginIfNotAdmin( $session);

if (isset($_POST["id"])) {
	$delete_id= (int)(getCleanInput($_POST["id"]));
} else {
	redirect("delete-user-form.php?".returnRequiredParams($session)."&err=i");
}
$bError = false;
$dbconn = getConnection();

$username = getUserName( $delete_id);
$rc = 1; // assume success
$strSQL = "DELETE FROM feedback WHERE userid= ?;";
$result = executeQuery($dbconn, $strSQL, $bError, array($delete_id));

$strSQL = "DELETE FROM attendance WHERE memberid = ?;";
$result = executeQuery($dbconn, $strSQL, $bError, array($delete_id));

$strSQL = "DELETE FROM promotions WHERE memberid = ?;";
$result = executeQuery($dbconn, $strSQL, $bError, array($delete_id));

$strSQL = "DELETE FROM users WHERE id = ?;";
$result = executeQuery($dbconn, $strSQL, $bError, array($delete_id));

$strSQL = "DELETE FROM useraccountinfo WHERE userid= ?;";
$result = executeQuery($dbconn, $strSQL, $bError, array($delete_id));

$strSQL = "DELETE FROM orderitems WHERE userid= ?;";
$result = executeQuery($dbconn, $strSQL, $bError, array($delete_id));

$strSQL = "DELETE FROM orders WHERE userid= ?;";
$result = executeQuery($dbconn, $strSQL, $bError, array($delete_id));

$strSQL = "DELETE FROM customdata WHERE memberid= ?;";
$result = executeQuery($dbconn, $strSQL, $bError, array($delete_id));

// Send them back to delete form
redirect("delete-user-form.php?deleteduser=" . urlencode($username) . "&".returnRequiredParams($session)."&done=1");
?>
