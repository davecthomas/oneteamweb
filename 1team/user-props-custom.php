<?php
include ('utils.php');

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
$errno = 0;
if ( isset($_POST["id"])) {
	$userid = (int)(getCleanInput($_POST["id"]));
} else {
	$bError = true;
}

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

if ( $bError) {
	displayErrorPage("Set Custom User Properties", "Error", "home.php");
} else {

	$strSQL = "SELECT * FROM users WHERE id = ? AND teamid = ?;";
	$dbconn = getConnectionFromSession($session);
	$userResults = executeQuery($dbconn, $strSQL, $bError, array($userid, $teamid));
	// Only if we find this user can we update the custom data for the user
	if (count($userResults) > 0) {
		// Custom fields support
		$strSQL = "SELECT customfields.id as customfieldsid, customfields.name as customfieldname, * FROM customfields LEFT OUTER JOIN customdata ON (customdata.customfieldid = customfields.id and customdata.memberid = ? AND customfields.teamid = ?) ;";
		$customdataResults = executeQuery($dbconn, $strSQL, $bError, array($userid, $teamid));
		$loopMax = count( $customdataResults);
		if ($loopMax > 0) {
			$rowCountCustomData = 0;

			while ($rowCountCustomData < $loopMax) {
				// Get this custom field name and value
				$customfieldid = $customdataResults[$rowCountCustomData]["customfieldsid"];
				$customDataFieldName = "customfield".$customfieldid."value".$rowCountCustomData;

				// Need to know which fields to skip. After all, if it wasn't diplayed, we shouldn't go creating rows in the DB for 'em
				if ($customdataResults[$rowCountCustomData]["hasdisplaycondition"]) {
					$dcObject = $customdataResults[$rowCountCustomData]["displayconditionobject"];
					$dcField = $customdataResults[$rowCountCustomData]["displayconditionfield"];
					$dcOperator = $customdataResults[$rowCountCustomData]["displayconditionoperator"];
					$dcValue = $customdataResults[$rowCountCustomData]["displayconditionvalue"];

					if ($dcObject == DisplayConditionObject_User) {
						$strSQL = "SELECT " . $dcField . " FROM " . $dcObject . " WHERE id = ?;";
					} else {
						$strSQL = "SELECT " . $dcField . " FROM " . $dcObject . " WHERE userid = ?;";
					}
					$dcResult = executeQueryFetchColumn($dbconn, $strSQL, $bError, array($userid));
					// Assume we can't until condition proves we can
					$displayCustomField = false;

					switch ($dcOperator) {
						case DisplayConditionOperator_EQ:
							if ($dcResult == $dcValue) $displayCustomField = true;
							break;
						case DisplayConditionOperator_LT:
							if ($dcResult< $dcValue) $displayCustomField = true;
							break;
						case DisplayConditionOperator_GT:
							if ($dcResult > $dcValue) $displayCustomField = true;
							break;
						case DisplayConditionOperator_NE:
							if ($dcResult != $dcValue) $displayCustomField = true;
							break;
					}

//						echo "<tr><td>" . $strSQL . " does " . $dcObject . "." . $dcField . " " . $dcOperator . " " . $dcValue . " ? " . boolToStr($displayCustomField) . "</td></tr>\n";

					// Skip the rest of this loop iteration if we don't display field
					if (!$displayCustomField) {
						$rowCountCustomData++;
						continue;
					}
				}

				// Depending on the data type, we have different columns to set, but also have to handle pesky checkboxes (bool) values specially
				$datatype = $customdataResults[$rowCountCustomData]["customdatatypeid"];
				switch ($datatype) {
					case CustomDataType_Text:
						$customdatatypefield = "valuetext";
						$customDataValue = $_POST[$customDataFieldName];
						$strSQLInsert = "INSERT INTO customdata VALUES (DEFAULT, ?, ?, NULL, NULL, NULL, ?, NULL, NULL, ? );";

						break;
					case CustomDataType_Num:
						$customdatatypefield = "valueint";
						$customDataValue = $_POST[$customDataFieldName];
						$strSQLInsert = "INSERT INTO customdata VALUES (DEFAULT, ?, ?, NULL, ?, NULL, NULL, NULL, NULL, ? );";
						break;
					case CustomDataType_Float:
						$customdatatypefield = "valuefloat";
						$customDataValue = $_POST[$customDataFieldName];
						$strSQLInsert = "INSERT INTO customdata VALUES (DEFAULT, ?, ?, NULL, NULL, NULL, NULL, NULL, ?, ? );";
						break;
					case CustomDataType_Bool:
						$customdatatypefield = "valuebool";
						if (!isset($_POST[$customDataFieldName])) {
							$customDataValue = false;
						} else {
							$customDataValue = true;
						}
						$strSQLInsert = "INSERT INTO customdata VALUES (DEFAULT, ?, ?, NULL, NULL, ?, NULL, NULL, NULL, ? );";
						break;
					case CustomDataType_Date:
						$customdatatypefield = "valuedate";
						$customDataValue = $_POST[$customDataFieldName];
						$strSQLInsert = "INSERT INTO customdata VALUES (DEFAULT, ?, ?, NULL, NULL, NULL, NULL, ?, NULL, ? );";
						break;
					case CustomDataType_List:
						$customdatatypefield = "valuelist";
						$customDataValue = $_POST[$customDataFieldName];
						$strSQLInsert = "INSERT INTO customdata VALUES (DEFAULT, ?, ?, ?, NULL, NULL, NULL, NULL, NULL, ? );";
						break;
					default:
						$bError = true;
						break;
				}

				if (isset($customDataValue)) {
					// Find out if this is a INSERT or UPDATE of custom data
					// Since we are doing a LEFT OUTER JOIN, memberid may be NULL, meaning, this custom data field has never been set for this user
					if (! isset($customdataResults[$rowCountCustomData]["memberid"]) ) {
						$strSQL = $strSQLInsert;
						executeQuery($dbconn, $strSQL, $bError, array($customfieldid, $userid, $customDataValue, $teamid));
					} else {
						$strSQL = "UPDATE customdata SET " . $customdatatypefield  . " = ? WHERE customfieldid = ? AND memberid = ? AND teamid = ?;";
						executeQuery($dbconn, $strSQL, $bError, array($customDataValue, $customfieldid, $userid, $teamid));
					}
				}

				$rowCountCustomData++;
			}
		}
	}

	if ( ! isUser( $session, Role_Member )) {
		redirect("user-props-form.php?" . returnRequiredParams($session) . "&id=" . $userid . "&done=1");
	} else {
		redirect("home.php?" . returnRequiredParams($session));
	}
}
