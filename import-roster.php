<?php  
ob_start(); 	// This caches non-header output, allowing us to redirect
include ('utils.php');
// Roster import column names
define("usercol_offset_undefined", -1);
define("usercol_firstname", "firstname");
define("usercol_lastname", "lastname");
define("usercol_login", "login");
define("usercol_email", "email");
define("usercol_address", "address");
define("usercol_address2", "address2");
define("usercol_city", "city");
define("usercol_state", "state");
define("usercol_postalcode", "postalcode");
define("usercol_phone1", "smsphone");
define("usercol_smscarrier", "smscarrier");
define("usercol_phone2", "phone2");
define("usercol_birthdate", "birthdate");
define("usercol_referredby", "referredby");
define("usercol_notes", "notes");
define("usercol_emergencycontact", "emergencycontact");
define("usercol_ecphone1", "ecphone1");
define("usercol_ecphone2", "ecphone2");
define("usercol_gender", "gender");
define("usercol_isbillable", "isbillable");
define("usercol_startdate", "startdate");
define("usercol_level", "level");
define("usercol_promotiondate", "promotiondate");
// Assure we have the input we need, else send them to default.php
if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid = getUserID()) == RC_RequiredInputMissing)) {
	redirect("default.php?rc=" . RC_RequiredInputMissing);
}
// Get $session array initialized
$session = startSession($sessionkey, $userid);
if (! isValidSession($session )) {
	redirect("default.php?rc=" . $session);
}
// Only admins can execute this script
redirectToLoginIfNotAdmin( $session);

$bError = false;
$errno = "";

// teamid depends on who is calling 
if (!isUser($session, Role_ApplicationAdmin)) {
	if (isset($session["teamid"])) {
		$teamid = $session["teamid"];
	} else {
		$bError = true;
		$errno = "teamid";
	}
} else {
	if ((isset($_POST["teamid"]))&& (is_numeric($_POST["teamid"]))) {
		$teamid = $_POST["teamid"];
	} else {
		$bError = true;
		$errno = "teamid";
	}
}

if (!$bError) {
	$bAllowAdd = canAddUsersToTeam($session, $teamid, $memberCount);
	if (!$bAllowAdd) {
		$errno = "Team plan is full at " . $memberCount;
		$bError = true;
	}
	if (getTeam($session, $teamid, $teamResults) != RC_Success) {
		$bError = true;
	}
}
// If a checkbox is set in post, that means it was checked
if (isset($_POST["sendemail"])) {
	$sendemail = true;
} else {
	$sendemail = false;
}

$filename = "";
if ((isset($_FILES["roster"]["name"])) && (!$bError)) {
	//Get the file information
	$userfile_name = $_FILES["roster"]["name"];
	$userfile_tmp = $_FILES["roster"]["tmp_name"];
	$userfile_size = $_FILES["roster"]["size"];
	$userfile_base = basename($_FILES["roster"]["name"]);
	$file_ext = substr($userfile_base, strrpos($userfile_base, ".") + 1);

	//Only process if the file is a JPG and below the allowed limit  of 1MB
	if((isset($_FILES["roster"])) && ($_FILES["roster"]["error"] == 0)) {

		if ($file_ext!="csv")  {
			$bError = true;
			$errno= "csv extension required: ".$file_ext;
		}
		else if ($userfile_size > (1024 * 1000)) {
			$bError = true;
			$errno= "csv files is too large: ".$userfile_size;
		} else {
			$hasUploadImage = true;
		}
	} else {
		$bError = true;
		$errno= "Error: ".$_FILES["roster"]["error"];
	}

	// Make sure the temp file exists
	if (!file_exists($userfile_tmp)) {
		$bError = true;
		$errno = "Not found";
	}
	if (!$bError) {
		$filename = $userfile_name;
		// Attempt to create a directory for team uploads
		if (!is_dir(uploadsDir."/$teamid/"))
			if (!mkdir(uploadsDir."/$teamid/")) {
				$bError = true;
				$errno = "md";
			}
		// rename the uploaded file to the hash name.
		if (!move_uploaded_file($userfile_tmp, uploadsDir."/$teamid/$filename") ) {
			$bError = true;
			$errno = "move: ".uploadsDir."/$teamid/$filename";
		}  else {
			$fullpath = uploadsDir."/$teamid/$filename";
			$rosterfile = fopen(  $fullpath, "r");
			if (FALSE == $rosterfile) {
				$bError = true;
				$errno = "fo";
			} else {
				$numUsers = 0;
				// Read the first row to get the order of fields figured out
				if (($row = fgetcsv($rosterfile)) != FALSE) {
					$numcolumns = count($row);
					$columnNamesCount = 0;
					$columnsUsed = array();
					for ($c=0; $c < $numcolumns; $c++) {
						if (strcasecmp($row[$c], usercol_firstname) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_firstname;
						} else if (strcasecmp($row[$c], usercol_lastname) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_lastname;
						} else if (strcasecmp($row[$c], usercol_login) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_login;
						} else if (strcasecmp($row[$c], usercol_email) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_email;
						} else if (strcasecmp($row[$c], usercol_address) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_address;
						} else if (strcasecmp($row[$c], usercol_address2) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_address2;
						} else if (strcasecmp($row[$c], usercol_city) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_city;
						} else if (strcasecmp($row[$c], usercol_state) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_state;
						} else if (strcasecmp($row[$c], usercol_postalcode) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_postalcode;
						} else if (strcasecmp($row[$c], usercol_phone1) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_phone1;
						} else if (strcasecmp($row[$c], usercol_smscarrier) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_smscarrier;
						} else if (strcasecmp($row[$c], usercol_phone2) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_phone2;
						} else if (strcasecmp($row[$c], usercol_birthdate) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_birthdate;
						} else if (strcasecmp($row[$c], usercol_referredby) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_referredby;
						} else if (strcasecmp($row[$c], usercol_notes) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_notes;
						} else if (strcasecmp($row[$c], usercol_emergencycontact) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_emergencycontact;
						} else if (strcasecmp($row[$c], usercol_ecphone1) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_ecphone1;
						} else if (strcasecmp($row[$c], usercol_ecphone2) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_ecphone2;
						} else if (strcasecmp($row[$c], usercol_gender) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_gender;
						} else if (strcasecmp($row[$c], usercol_isbillable) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_isbillable;
						} else if (strcasecmp($row[$c], usercol_startdate) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_startdate;
						} else if (strcasecmp($row[$c], usercol_birthdate) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_birthdate;
						} else if (strcasecmp($row[$c], usercol_level) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_level;
						} else if (strcasecmp($row[$c], usercol_promotiondate) == 0) {
							$columnsUsed[$columnNamesCount] = $c;
							$column_names[$columnNamesCount++] = usercol_promotiondate;
						} else {
							// ignore unrecognized column
						}

					}
/*					print_r($row);
					echo "<br>";
					print_r($column_names);
					echo "<br>";
					print_r($columnsUsed);
					echo "<br>";
 */
					$numcolumnsUsed = count($columnsUsed);
					// Now that we have the header figured out, get the rest of the rows
					$numRows = 0;
					$user = array();
					while (($row = fgetcsv($rosterfile)) != FALSE) {
						$userid = User::UserID_Undefined;
						$numRows ++;
						$user[$numUsers] = array();
						for ($c=0; $c < $numcolumnsUsed; $c++) {
							$user[$numUsers][$column_names[$c]] = $row[$columnsUsed[$c]];
						}
						// Check for required fields
						if (((!isset($user[$numUsers]["firstname"])) || (strlen($user[$numUsers]["firstname"]) < minlen_name)) ||
							   ((!isset($user[$numUsers]["lastname"])) || (strlen($user[$numUsers]["lastname"]) < minlen_name)) ||
							   ((!isset($user[$numUsers]["login"])) || (strlen($user[$numUsers]["login"]) < minlen_login)) ||
							   (!isValidEmail( $user[$numUsers]["email"]))) {
							$bError = true;   // Not fatal, just not importing this row
							$errno .= $numRows . " not imported. Required fields.<br>";
						} else {
							// Create default values for any unset, unrequired fields.
							if (!isset($user[$numUsers]["startdate"]))          $user[$numUsers]["startdate"]   = date("Y-m-d");
							if (!isset($user[$numUsers]["address"]))            $user[$numUsers]["address"]     = "";
							if (!isset($user[$numUsers]["address2"]))           $user[$numUsers]["address2"]    = "";
							if (!isset($user[$numUsers]["city"]))               $user[$numUsers]["city"]        = "";
							if (!isset($user[$numUsers]["state"]))              $user[$numUsers]["state"]       = "";
							if (!isset($user[$numUsers]["postalcode"]))         $user[$numUsers]["postalcode"]  = "";
							if (!isset($user[$numUsers]["smsphone"]))           $user[$numUsers]["smsphone"]      = "";
							if (!isset($user[$numUsers]["smscarrier"]))         $user[$numUsers]["smscarrier"]      = "";
							if (!isset($user[$numUsers]["phone2"]))             $user[$numUsers]["phone2"]      = "";
							if (!isset($user[$numUsers]["birthdate"]))          $user[$numUsers]["birthdate"]   = 0;
							if (!isset($user[$numUsers]["referredby"]))         $user[$numUsers]["referredby"]  = "";
							if (!isset($user[$numUsers]["notes"]))              $user[$numUsers]["notes"]       = "";
							if (!isset($user[$numUsers]["emergencycontact"]))   $user[$numUsers]["emergencycontact"]  = "";
							if (!isset($user[$numUsers]["ecphone1"]))           $user[$numUsers]["ecphone1"]    = "";
							if (!isset($user[$numUsers]["ecphone2"]))           $user[$numUsers]["ecphone2"]    = "";
							if (!isset($user[$numUsers]["gender"]))             $user[$numUsers]["gender"]      = 0;
							if (!isset($user[$numUsers]["isbillable"]))         $user[$numUsers]["isbillable"]  = TRUE;
							if (!isset($user[$numUsers]["level"]))              $user[$numUsers]["level"]   = "";
							if (!isset($user[$numUsers]["promotiondate"]))      $user[$numUsers]["promotiondate"]   = date("Y-m-d");

							$userid = createUser(
 										$session, $teamid, Role_Member, $user[$numUsers]["startdate"], $user[$numUsers]["firstname"], $user[$numUsers]["lastname"],
										$user[$numUsers]["login"], $user[$numUsers]["email"],
 										$user[$numUsers]["address"], $user[$numUsers]["address2"], $user[$numUsers]["city"], $user[$numUsers]["state"], $user[$numUsers]["postalcode"],
										$user[$numUsers]["smsphone"],
										$user[$numUsers]["smscarrier"],
										$user[$numUsers]["phone2"], $user[$numUsers]["birthdate"], $user[$numUsers]["referredby"],
										$user[$numUsers]["notes"], User::UserID_Undefined, $user[$numUsers]["emergencycontact"],
										$user[$numUsers]["ecphone1"], $user[$numUsers]["ecphone2"], $user[$numUsers]["gender"],
										$user[$numUsers]["isbillable"],     // Billable
										UserAccountStatus_Active,
										$sendemail, // Gen password
										$sendemail, // Introductory email (new user, versus reset)
										$err);     // err is set in call and used in error case below to help build error string
							if ($userid == User::UserID_Undefined) {
								$bError = true;    // Not fatal, just not importing this row
								$errno .=  $numRows . " not imported:" . $user[$numUsers]["login"] . " " .$err ."<br>";
							} else {
								if ((strlen($user[$numUsers]["level"]) > 0) && (isValidDate($user[$numUsers]["promotiondate"]))) {
									if (($levelid = getLevelFromName($session, $teamid, $user[$numUsers]["level"])) != LevelID_Undefined) {
										if (promoteUser($session, $teamid, $userid, $levelid, $user[$numUsers]["promotiondate"] ) != RC_Success){
//											$bError = true;
//											$errno .= $user[$numUsers]["login"]." promo.<br>";
										}
									} // Ignore the promotion if we can't get the level from the name
								}
							}
							$numUsers ++;
							$memberCount ++;
							// If hit max number of users, stop import
							if ($memberCount >= $teamResults["plan"]) {
								$errno .= "Import partially successful. Last row imported: " .$numRows . ". Team plan is full at " . $memberCount."<br>";
								$bError = true;
								break;
							}

						}
 
//print_r($user);
					}
				}

				fclose( $rosterfile);
			}
		}
		// Delete the upload
		if ((isset($fullpath)) && (file_exists($fullpath))) {
			unlink($fullpath);
		}
	}
} else {
	$bError = true;
	$errno = "FILES";
}


if (!$bError) {
	redirect( "member-roster.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&filter=status%3C%3E0" . "&done=1");
} else {
echo htmlspecialchars($errno);
	redirect("import-roster-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=" . urlencode($errno));
}
ob_end_flush();
?>
