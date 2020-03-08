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

$bError = false;

// teamid depends on who is calling
if (isUser($session, Role_TeamAdmin)){
	if (isset($session["teamid"])){
		$teamid = $session["teamid"];
	}
} else {
	if (isset($_POST["teamid"])){
		$teamid = $_POST["teamid"];
	}
}

if (isset($_POST["name"])) {
	$name = $_POST["name"];
} else {
	$bError = true;
}

if (isset($_POST["datatype"])) {
	$datatype = $_POST["datatype"];
} else {
	$bError = true;
}

if (isset($_POST["hasdisplaycondition"])) {
	$hasdisplaycondition = 'TRUE';
} else {
	$hasdisplaycondition = 'FALSE';
}

if (!$bError) {
	// describe customfields;
	// +--------------------------+-------------+------+-----+---------+----------------+
	// | Field                    | Type        | Null | Key | Default | Extra          |
	// +--------------------------+-------------+------+-----+---------+----------------+
	// | id                       | int         | NO   | PRI | NULL    | auto_increment |
	// | customdatatypeid         | int         | NO   |     | NULL    |                |
	// | name                     | varchar(80) | YES  |     | NULL    |                |
	// | teamid                   | int         | YES  |     | NULL    |                |
	// | displayconditionobject   | varchar(80) | YES  |     | NULL    |                |
	// | displayconditionfield    | varchar(80) | YES  |     | NULL    |                |
	// | displayconditionoperator | varchar(2)  | YES  |     | NULL    |                |
	// | displayconditionvalue    | varchar(80) | YES  |     | NULL    |                |
	// | hasdisplaycondition      | tinyint(1)  | YES  |     | NULL    |                |
	// | listorder                | int         | YES  |     | NULL    |                |
	// | customlistid             | int         | YES  |     | NULL    |                |
	// +--------------------------+-------------+------+-----+---------+----------------+
	$strSQL = "INSERT INTO customfields VALUES (DEFAULT, ?, ?, ?, NULL, NULL, NULL, NULL, " . $hasdisplaycondition . ", NULL, NULL);";
	$dbconn = getConnectionFromSession($session);
	executeQuery($dbconn, $strSQL, $bError, array($datatype, $name, $teamid, $hasdisplaycondition));
	if (! $bError) {
		$customfieldid = 0;
		$strSQL = "SELECT LAST_INSERT_ID();";
		$customfieldid = executeQueryFetchColumn($dbconn, $strSQL, $bError);

		if ($customfieldid > 0) {
			redirect("edit-custom-field-form.php?" . returnRequiredParams($session) . "&id=" . $customfieldsResults[0]["id"] . "&teamid=" . $teamid . "&done=1");
		} else {
			$bError = true;
		}
	}
}
if ($bError == true) {
	redirect("manage-custom-fields.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1");
}
