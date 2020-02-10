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
	} else {
		$bError = true;
		$errno = "teamid";
	}
}

if (isset($_POST["id"])) {
	$fieldid = $_POST["id"];
} else {
	$bError = true;
	$errno = "id";
}

if (isset($_POST["name"])) {
	$name = $_POST["name"];
} else {
	$bError = true;
	$errno = "name";
}

//datatype
if (isset($_POST["datatype"])) {
	$datatype = $_POST["datatype"];

	$customlistid = CustomList_Undefined;

	if ($datatype == CustomDataType_List) {
		if (isset($_POST["customlistid"])) {
			$customlistid = $_POST["customlistid"];
		} else {
			$bError = true;
			$errno = "customidlist";
		}
	}
} else {
	$bError = true;
	$errno = "datatype";
}

if (isset($_POST["hasdisplaycondition"])) {
	$hasdisplaycondition = 'TRUE';
	$bhasdisplaycondition = true;
	// displayconditionobject
	if (isset($_POST["displayconditionobject"])) {
		$displayconditionobject = $_POST["displayconditionobject"];

		// to do - grab data for users or useraccountinfo
		if ($displayconditionobject == DisplayConditionObject_User){
			if (isset($_POST["displayconditionuser"])) {
				$displayconditionfield = $_POST["displayconditionuser"];
			} else {
				$bError = true;
				$errno = "displayconditionuser";
			}

		} else {
			$displayconditionobject = DisplayConditionObject_UserAccount;
			if (isset($_POST["displayconditionuseraccount"])) {
				$displayconditionfield = $_POST["displayconditionuseraccount"];
			} else {
				$bError = true;
				$errno = "displayconditionuseraccount";
			}
		}
		// displayconditionoperator
		if (isset($_POST["displayconditionoperator"])) {
			$displayconditionoperator = $_POST["displayconditionoperator"];
		} else {
			$bError = true;
			$errno = "displayconditionoperator";
		}

		// displayconditionvalue
		if (isset($_POST["displayconditionvalue"])) {
			$displayconditionvalue = $_POST["displayconditionvalue"];
		} else {
			$bError = true;
			$errno = "displayconditionvalue";
		}
	} else {
		$bError = true;
		$errno = "displayconditionobject";
	}
} else {
	$hasdisplaycondition = 'FALSE';
	$bhasdisplaycondition = false;
}

if (!$bError) {
	$dbconn = getConnection();

	if ($bhasdisplaycondition) {
		$strSQL = "UPDATE customfields SET name = ?, customdatatypeid = ?, hasdisplaycondition = " . $hasdisplaycondition . ", displayconditionobject = ?, displayconditionfield = ?, displayconditionoperator = ?, displayconditionvalue = ?, customlistid = ? WHERE id = ? AND teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($name, $datatype, $displayconditionobject, $displayconditionfield, $displayconditionoperator, $displayconditionvalue, $customlistid, $fieldid, $teamid));
	} else {
		// No display condition, so NULLify display related columns
		$strSQL = "UPDATE customfields SET name = ?, customdatatypeid = ?, hasdisplaycondition = " . $hasdisplaycondition . ", displayconditionobject = NULL, displayconditionfield = NULL, displayconditionoperator = NULL, displayconditionvalue = NULL, customlistid = ? WHERE id = ? AND teamid = ?;";
		executeQuery($dbconn, $strSQL, $bError, array($name, $datatype, $customlistid, $fieldid, $teamid));
	}

	if (!$bError) redirect("manage-custom-fields.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&done=1");

	$errno = "sql";
}

if ($bError == true) {
//	redirect("manage-custom-fields.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1");
echo " error at . " . $errno;
}
