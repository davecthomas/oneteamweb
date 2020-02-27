<?php
include_once ('globals.php');
include_once ('obj/Objects.php');
include_once ('utilsbase.php');
/*
** check a date
** dd.mm.yyyy || mm/dd/yyyy || dd-mm-yyyy || yyyy-mm-dd
*/
function check_date($date) {
    if(strlen($date) == 10) {
        $pattern = '/\.|\/|-/i';    // . or / or -
        preg_match($pattern, $date, $char);

        $array = preg_split($pattern, $date, -1, PREG_SPLIT_NO_EMPTY);

        if(strlen($array[2]) == 4) {
            // dd.mm.yyyy || dd-mm-yyyy
            if($char[0] == "."|| $char[0] == "-") {
                $month = $array[1];
                $day = $array[0];
                $year = $array[2];
//				echo "month = " . $month . " day = " . $day . " year = " . $year;
            }
            // mm/dd/yyyy    # Common U.S. writing
            if($char[0] == "/") {
                $month = $array[0];
                $day = $array[1];
                $year = $array[2];
            }
        }
        // yyyy-mm-dd    # iso 8601
        if(strlen($array[0]) == 4 && $char[0] == "-") {
            $month = $array[1];
            $day = $array[2];
            $year = $array[0];
        }
        if(checkdate($month, $day, $year)) {    //Validate Gregorian date
            return TRUE;

        } else {
            return FALSE;
        }
    }else {
        return FALSE;    // more or less 10 chars
    }
}

// Date Difference
function datediff($interval, $datefrom, $dateto, $using_timestamps = false)
{
	/*
	$interval can be:
	yyyy - Number of full years
	q - Number of full quarters
	m - Number of full months
	y - Difference between day numbers
	(eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33".
                 The datediff is "-32".)
	d - Number of full days
	w - Number of full weekdays
	ww - Number of full weeks
	h - Number of full hours
	n - Number of full minutes
	s - Number of full seconds (default)
	*/

	if (!$using_timestamps) {
		$datefrom = strtotime($datefrom, 0);
		$dateto = strtotime($dateto, 0);
	}
	$difference = $dateto - $datefrom; // Difference in seconds

	switch($interval) {
		case 'yyyy': // Number of full years
			$years_difference = floor($difference / 31536000);
			if (mktime(date("H", $datefrom),
								  date("i", $datefrom),
								  date("s", $datefrom),
								  date("n", $datefrom),
								  date("j", $datefrom),
								  date("Y", $datefrom)+$years_difference) > $dateto) {

			$years_difference--;
			}
			if (mktime(date("H", $dateto),
								  date("i", $dateto),
								  date("s", $dateto),
								  date("n", $dateto),
								  date("j", $dateto),
								  date("Y", $dateto)-($years_difference+1)) > $datefrom) {

			$years_difference++;
			}
			$datediff = $years_difference;
		break;

		case "q": // Number of full quarters
			$quarters_difference = floor($difference / 8035200);
			while (mktime(date("H", $datefrom),
									   date("i", $datefrom),
									   date("s", $datefrom),
									   date("n", $datefrom)+($quarters_difference*3),
									   date("j", $dateto),
									   date("Y", $datefrom)) < $dateto) {

			$months_difference++;
			}
			$quarters_difference--;
			$datediff = $quarters_difference;
		break;

		case "m": // Number of full months
			$months_difference = floor($difference / 2678400);
			while (mktime(
					date("H", $datefrom), date("i", $datefrom),
					date("s", $datefrom), date("n", $datefrom)+($months_difference),
					date("j", $dateto), date("Y", $datefrom)) < $dateto) {
				$months_difference++;
			}
			$months_difference--;
			$datediff = (int) $months_difference;
		break;

		case 'y': // Difference between day numbers
			$datediff = date("z", $dateto) - date("z", $datefrom);
		break;

		case "d": // Number of full days
			$datediff = floor($difference / 86400);
		break;

		case "w": // Number of full weekdays

			$days_difference = floor($difference / 86400);
			$weeks_difference = floor($days_difference / 7); // Complete weeks
			$first_day = date("w", $datefrom);
			$days_remainder = floor($days_difference % 7);
			$odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
			if ($odd_days > 7) { // Sunday
 				$days_remainder--;
			}
			if ($odd_days > 6) { // Saturday
				$days_remainder--;
			}
			$datediff = ($weeks_difference * 5) + $days_remainder;
		break;




		case "ww": // Number of full weeks
			$datediff = floor($difference / 604800);
		break;

		case "h": // Number of full hours
			$datediff = floor($difference / 3600);
		break;

		case "n": // Number of full minutes
			$datediff = floor($difference / 60);
		break;

		default: // Number of full seconds (default)
			$datediff = $difference;
		break;
	}

	return $datediff;
}

// Get the date the next payment is due
function getNextPaymentDueDate2($userid, $payid, $expires, $dbconn = null){
	// $strSQL = "select age(((select paymentdate from orderitems where userid = ? and id = ?) + '" . $expires . "'::interval), current_date);";
  $strSQL = "select (select paymentdate from orderitems where userid = ? and id = ?) + " . $expires . ";";
	if ($dbconn == null) $dbconn = getConnection();
  return executeQueryFetchColumn($dbconn, $strSQL, $bError, array($userid, $payid, $expires));
}

function dateDiffString($date1 , $date2, $dbconn = null){
  $diff_str = "";
  // MySQL variant 	$strSQL = "select TIMESTAMPDIFF(DAY,?,?);";
	// PostgreSQL $strSQL = "select age(?, ?)";
  $strSQL = "select TIMESTAMPDIFF(DAY,?,?);";
	if ($dbconn == null) $dbconn = getConnection();
	$diff_days = executeQueryFetchColumn($dbconn, $strSQL, $bError, array($date2, $date1));
	if (! $bError) $diff_str = "{$diff_days} days";
	return $diff_str;
}

function dateDiffNumDays($date1 , $date2, $dbconn = null){
  $diff_days = null;
	// PostgreSQL $strSQL = "select ?::date - ?::date;";
  // MySQL   $strSQL = "select TIMESTAMPDIFF(DAY,?,?);";
  $strSQL = "select TIMESTAMPDIFF(DAY,?,?);";
  if ($dbconn == null) $dbconn = getConnection();
	$diff = executeQueryFetchColumn($dbconn, $strSQL, $bError, array($date2, $date1));
  if (! $bError) $diff_days = $diff;
	return $diff_days;
}

// Requires this sql installed:
/* CREATE OR REPLACE FUNCTION every_what( start_date date,end_date date,incr integer, unit text)
	RETURNS SETOF date AS
	$$
	DECLARE
	   N  integer=0;
	   next_date date = start_date;
	   int_type interval = '1 ' || unit;
	BEGIN
	   WHILE end_date > next_date LOOP
	      RETURN NEXT next_date;
	      next_date = start_date + (N + incr) * int_type;
	      N = N + incr;
	   END LOOP;
	   RETURN;
	END;
	$$
	LANGUAGE 'plpgsql';
 */
function dateDiffNumMonths($date1 , $date2, $dbconn= null){
	$strSQL = "select count(*) from every_what( ?, ?::date, 1, 'months' );";
  if ($dbconn==null) $dbconn = getConnection();
	return executeQueryFetchColumn($dbconn, $strSQL, $bError, array($date1, $date2));
}

function getMembershipDurationInMonths( $id, $dbconn= null, &$bError) {
  // PostgreSQL variant $strSQL = "select age(current_date, (select startdate from users where id = ?))";
  // MySQL variant 	$strSQL = "select TIMESTAMPDIFF(MONTH,(select startdate from users where id = 2), CURRENT_TIMESTAMP);";
	$strSQL = "select TIMESTAMPDIFF(MONTH,(select startdate from users where id = ?), CURRENT_TIMESTAMP);";
  if ($dbconn==null) $dbconn = getConnection();
	return executeQueryFetchColumn($dbconn, $strSQL, $bError, array($id));
}

function getMembershipDurationString($id, $dbconn= null, &$bError){
  $timeIn = getMembershipDurationInMonths($id, $dbconn, $bError);

  $time_in_str = "";
  if (! $bError){
    if ($timeIn>11){
      $time_in_years = intdiv($timeIn, 12);
      $months_remaining = $timeIn%12;
      $time_in_str = "{$time_in_years} years, ";
    } else {
      $months_remaining = $timeIn;
    }
    if ($months_remaining != 1){
      $time_in_str .= "{$months_remaining} months";
    } else {
      $time_in_str .= "{$months_remaining} month";
    }
  }
  return $time_in_str;
}

// Get the custom value from the recordset
function getCustomValue($rs, $datatype) {

	$customDataValue = 0;
	switch ($datatype){
		case CustomDataType_Text:
			$customDataValue = odbc_result($rs, "valuetext");
			break;
		case CustomDataType_Num:
			$customDataValue = odbc_result($rs, "valueint");
			break;
		case CustomDataType_Float:
			$customDataValue = odbc_result($rs, "valuefloat");
			break;
		case CustomDataType_Bool:
			$customDataValue = odbc_result($rs, "valuebool");
			break;
		case CustomDataType_Date:
			$customDataValue = odbc_result($rs, "valuedate");
			break;
		case CustomDataType_List:
			$customDataValue = odbc_result($rs, "valuelist");
			break;
	}

	return $customDataValue;
}

// Get a team name given the id
function getTeamName( $id, $dbconn = null) {
	if ((!isset($id)) || ($id == 0)) {
		$teamname = TeamNameError;
	} else {
		$strSQL = "SELECT * FROM teams WHERE id = ?;";

    $bError = false;
    if ($dbconn == null) $dbconn = getConnection();
  	$results = executeQuery($dbconn, $strSQL, $bError, array($id));
		$teamname = TeamNameError;
		if (count($results)>0) {
			$teamname = $results[0]["name"];
		}
	}
	return $teamname;
}

// Takes m/d/y and adds one to the number
// Doesn't reliably work for days on leap year
function DateAdd($interval, $number, $date) {

    $datearray = explode("/", $date);
    $month = $datearray[0];
    $day = $datearray[1];
    $year = $datearray[2];

    switch ($interval) {

        case "y":
            $year+=$number;
            break;
        case "m":
            $month+=$number;
			if ($month > 12) {
				$month = 1;
				$year++;
			}
			if ($month < 1 ) {
				$month = 12;
				$year--;
			}
            break;
        case "d":
            $day+=$number;
			switch ($month){
				case 1:
				case 3:
				case 5:
				case 7:
				case 8:
				case 10:
				case 13:
					if ($day > 31) $day = 1;
				break;

				case 4:
				case 6:
				case 9:
				case 11:
					if ($day > 30) $day = 1;
				break;
				case 2:
					if ($day > 28) $day = 1;
				break;

				if ($day < 1 ) $day = 1;

			}
	        break;
    }
//    $timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
    return $month . "/" . $day . "/" . $year;
}

// Decide if we need to do a reverse sort or not
function sortModifier( $linkSort, $currentSort) {
	if (strcmp($linkSort, $currentSort) == 0) {
		$sortModifier = $linkSort . " DESC";
	} else {
		$sortModifier = $linkSort;
	}
	return urlencode($sortModifier);
}

// Get email for the user.
function getEmail( $id ){
	if (!isValidUserID($id)){
		return "";
	} else {
		$dbconn = getConnection();
		$strSQL = "SELECT email FROM useraccountinfo WHERE userid = " . $id . ";";
		$results = executeQuery($dbconn, $strSQL, $bError);
		if (count($results) > 0) {
			return $results[0]["email"];
		} else {
			return "";
		}
	}
}

function getUserName( $id, $dbconn = null){
	if (!isValidUserID($id)){
		return "";
	} else if ($id == User::UserID_Guest){
		return User::Username_Guest;
	} else {
    $username = UserNotFound;
    $strSQL = "SELECT firstname, lastname FROM users WHERE id = ?;";
    if ($dbconn == null) $dbconn = getConnection();
    $dbconn = getConnection();
    $results = executeQuery($dbconn, $strSQL, $bError, array($id));
		foreach ($results as $row) {
			$username = $row["firstname"] . " " . $row["lastname"];
		}
		return $username;
	}
}

// Ultra simple error page
function displayErrorPage( $title, $text, $gotopage){
	echo "
	<h3 class=\"usererror\">" . $title . "</h3>
	<div class=\"indented-group-noborder\">
	<p class=\"usererror\">" . $text . "<br><br>
	<a class=\"action\" href=\"" . $gotopage . "\">Home</a></p>
	</div>";
	include('footer.php');
	echo "
	</body>
	</html>";
}

function subdueInactive( $accountStatus){
	if ($accountStatus == UserAccountStatus_Inactive) {
		return 'class="subdued"';
	} else {
		return "";
	}
}

// Figure out when the next payment is due
function getLastTeamPaymentDate( $teamid, $isbillable, $dbconn = null){
  if ($dbconn == null) $dbconn = getConnection();

	if (!$isbillable) {
		return "Not Billable";
	}

	$lastPaymentDate = NotFound;

	// Figure out if (their payment is late
	$strSQL = "select max(paymentdate) from teampayments where teamid = ? ;";
  $results = executeQuery($dbconn, $strSQL, $bError, array($teamid));
  foreach($results as $row) {
		$lastPaymentDate = $row["max"];
	}
	return $lastPaymentDate;
}

// Get next team payment date
// Get the date the next payment is due
// TO DO: this has never been tested on a paying customer!!!
function getNextTeamPaymentDate($teamid, $planduration, $isbillable, $dbconn = null){
	if (!$isbillable) {
		return "never - not billable";
	}
  if ($dbconn == null) $dbconn = getConnection();

	if (! is_numeric( $planduration)) {
		return "Error";
	}

	$strSQL = "select ((select max(paymentdate) from teampayments where teamid = ?) + cast('" . $planduration . " months' as interval)) as duedate;";
  $dueDate =executeQueryFetchColumn($dbconn, $strSQL, $bError, array($teamid));
	if (strlen($dueDate) < 1) {
		$dueDate = "in " . $planduration . " month";
		if ($planduration != 1) $dueDate += "s";
	}
	return $dueDate;
}

function getUserStatus( $userid, $dbconn = null){
  if ($dbconn == null) $dbconn = getConnection();
	$strSQL = "SELECT status FROM useraccountinfo WHERE userid = ?";
  return executeQueryFetchColumn($dbconn, $strSQL, $bError, array($userid));
}

function getProgramName($programid, $dbconn = null) {
  if ($dbconn == null) $dbconn = getConnection();
	$strSQL = "SELECT name FROM programs WHERE id = ?";
  return executeQueryFetchColumn($dbconn, $strSQL, $bError, array($programid));
}

function getCustomListName($customlistid, $dbconn = null) {
  if ($dbconn == null) $dbconn = getConnection();
	$strSQL = "SELECT name FROM customlists WHERE id = ?";
  return executeQueryFetchColumn($dbconn, $strSQL, $bError, array($customlistid));
}

function normalizeGender( $gender){
	if (($gender == "M") || ($gender == "m") || ($gender == Gender_Male)) return Gender_Male;
	if (($gender == "F") || ($gender == "f") || ($gender == Gender_Female)) return Gender_Female;
//	else return 0;
//return Gender_Female;
}

function isTeamUsingPrograms($session, $teamid, $dbconn = null) {
	// We cache this in the session
	if (isset($session["isteamusingprograms"])) return $session["isteamusingprograms"];

  if ($dbconn == null) $dbconn = getConnection();

	// Ignore teamid except for App Admin
	if (!isUser($session, Role_ApplicationAdmin)){
		$teamid = $session["teamid"];
	} else {
		if (!isset($teamid)) return false;
	}

	$strSQL = "SELECT id FROM programs WHERE teamid = ?";

  $results = executeQuery($dbconn, $strSQL, $bError, array($teamid));
	if (count($results) > 0) $session["isteamusingprograms"] = true;
	else $session["isteamusingprograms"] = false;

	return $session["isteamusingprograms"];
}

function isTeamUsingLevels($session, $teamid) {
	// We cache this in the session
	if (isset($session["isteamusinglevels"])) return $session["isteamusinglevels"];

	// Ignore teamid except for App Admin
	if (!isUser($session, Role_ApplicationAdmin)){
		$teamid = $session["teamid"];
	} else {
		if (!isset($teamid)) return false;
	}

	$strSQL = "SELECT id FROM levels WHERE teamid = ?";

  $dbconn = getConnection();
  $results = executeQuery($dbconn, $strSQL, $bError, array($teamid));
	if (count($results) > 0) $session["isteamusinglevels"] = true;
	else $session["isteamusinglevels"] = false;

	return $session["isteamusinglevels"];
}

function getMemberCount( $session, $teamid, $dbconn = null) {
	// We cache this in the session
	if (isset($session["membercount"])) return $session["membercount"];

  if ($dbconn == null) $dbconn = getConnection();

	// Count the number of teams. Only used in one place, but handy to have around
	$strSQL = "SELECT COUNT(*) AS ROW_COUNT FROM users, useraccountinfo WHERE users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status = ? AND users.teamid = ?";
  $session["membercount"] =executeQueryFetchColumn($dbconn, $strSQL, $bError, array(UserAccountStatus_Active, $teamid));

	return $session["membercount"];
}

function getUserProgram( $session, $userid, $dbconn = null) {

	// Count the number of teams. Only used in one place, but handy to have around
	$strSQL = "select programid from users where id = ?";

  if ($dbconn == null) $dbconn = getConnection();
  return executeQueryFetchColumn($dbconn, $strSQL, $bError, array($userid));
}

// Convert SQL results to interval constant
function getIntervalUnits( $sqlinterval){
	if (strstr($sqlinterval, skuSQL_Days)) return skuExpirationUnits_Days;
	else if (strstr($sqlinterval, skuSQL_Weeks)) return skuExpirationUnits_Weeks;
	else if (strstr($sqlinterval, skuSQL_Months)) return skuExpirationUnits_Months;
	else if (strstr($sqlinterval, skuSQL_Years)) return skuExpirationUnits_Years;
	else return skuExpiration_Undefined;
}

function getInterval( $sqlinterval){
	$val = explode(" ", $sqlinterval);
	if (is_numeric($val[0])) return $val[0];
	else return Interval_Undefined;
}

function getMoneyClass($amount){
	if ($amount >= 0) return "money";
	else return "debit";
}

function getMoneyTotalClass($amount){
	if ($amount >= 0) return "moneytotal";
	else return "moneytotaldebit";
}

// Returns a userid or User::UserID_Undefined on failure
function 	createUser($session, $teamid, $roleid, $startdate, $firstname, $lastname, $login, $email, $address1, $address2, $city, $state, $postalcode,
$smsphone, $smsphonecarrier, $phone2, $birthdate, $referredby, $notes, $coachid, $emergencycontact, $ecphone1, $ecphone2, $gender, $isbillable, $status, $bgenpass, $bIntro, &$err ){

	$bError = false;
	$userid= User::UserID_Undefined;

	// Make sure this is a unique login on this team
	$strSQL = "SELECT id FROM users WHERE login=? AND teamid = ?;";
  $dbconn = getConnection();
  $results = executeQuery($dbconn, $strSQL, $bError, array($login, $teamid));
	if ( count($results) > 0) {
		$bError = true;
		$err = "Login unavailable.";

	// Create the user
	/*
	id integer NOT NULL DEFAULT nextval('students_id_seq'::regclass),
	firstname character varying(50),
	lastname character varying(50),
	startdate date,
	address character varying(254),
	city character varying(50),
	state character varying(20),
	postalcode character varying(20),
	smsphone character varying(30),
	phone2 character varying(30),
	"login" character varying(50) NOT NULL,
	birthdate date,
	referredby character varying(50) DEFAULT 'David Thomas'::character varying,
	notes text,
	coachid integer,
	emergencycontact character varying(50),
	ecphone1 character varying(50),
	ecphone2 character varying(30),
	gender character(1),
	stopdate date,
	stopreason character varying(80),
	teamid integer,
	roleid integer,
	address2 character varying(80),
	useraccountinfo integer,
	salt character(9),
	passwd character varying(64),
	imageid - if the user has an image
	smsphonecarrier - for team admins only
	ipaddr - of last login
	timelockoutexpires - for accounts locked for time period
	*/
	} else {
		// Non members have a bunch of null fields
		if (!doesRoleContain($roleid, Role_Member)) {
			$strSQL = "INSERT INTO users VALUES (DEFAULT, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, NULL, NULL, NULL, NULL, NULL, NULL, NULL, ?, ?, ?, NULL, NULL, NULL, NULL, ?, NULL, NULL);" ;
      $results = executeQuery($dbconn, $strSQL, $bError, array($firstname, $lastname, $address1, $city, $state, $postalcode, $smsphone, $phone2, $login, $referredby, $notes, $teamid, $roleid, $address2, $smsphonecarrier));
		} else {
			$strSQL = "INSERT INTO users VALUES (DEFAULT, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, ?, ?, ?, NULL, NULL, NULL, NULL, ?, NULL, NULL);" ;
			$results = executeQuery($dbconn, $strSQL, $bError, array($firstname, $lastname, $address1, $city, $state, $postalcode, $smsphone, $phone2, $login, $referredby, $notes, $coachid, $emergencycontact, $ecphone1, $ecphone2, $gender, $teamid, $roleid, $address2, $smsphonecarrier));
		}

		if ($bError) {
			$err = "User creation error.";
		} else {
			// Get the new userid
			$strSQL = "SELECT id FROM users WHERE login = ? AND teamid = ?;";
			$userResults = executeQuery($dbconn, $strSQL, $bError, array($login, $teamid));
			// user not created
			if ( count($userResults) < 1) {
				$bError = true;
				$err = "User '" . $login . "' on team " . $teamid . " not created.";

			// User created, keep going
			} else {
				$userid = $userResults[0]["id"];
				// Set the dates separately, since SQL is picky about values for dates, can't just throw in an empty string or 0 for NULL
				if (isValidDate($startdate)){
				  $strSQL = "UPDATE users SET startdate = ? WHERE teamid = ? and id = ?";
					$results = executeQuery($dbconn, $strSQL, $bError, array($startdate, $teamid, $userid));
				}
				// Set the dates separately, since SQL is picky about values for dates, can't just throw in an empty string or 0 for NULL
				if (isValidDate($birthdate)){
				  $strSQL = "UPDATE users SET birthdate = ? WHERE teamid = ? and id = ?";
					$results = executeQuery($dbconn, $strSQL, $bError, array($birthdate, $teamid, $userid));
				}    // Yes, I'm ignoring errors for setting dates, since I don't think they are crucial and can be set later

				// Create account info. Every user has one, even if they are non-billable
				if ($roleid == Role_Member) {
					if ($isbillable) $strSQL = "INSERT INTO useraccountinfo VALUES (DEFAULT, ?, ?, ?, ?, TRUE);";
					else $strSQL = "INSERT INTO useraccountinfo VALUES (DEFAULT, ?, ?, ?, ?, FALSE);";
					$results = executeQuery($dbconn, $strSQL, $bError, array($email, $status, $userid, $teamid));
				} else {
					$strSQL = "INSERT INTO useraccountinfo VALUES (DEFAULT, ?, ?, ?, ?, FALSE);";
					$results = executeQuery($dbconn, $strSQL, $bError, array($email, UserAccountStatus_Active, $userid, $teamid));
				}
				if ($bError) {
					$err = "User account info.";
				} else {
					// Get the new useraccountinfo id
					$strSQL = "SELECT id FROM useraccountinfo WHERE userid = ? AND teamid = ?;";
					$useracctResults = executeQuery($dbconn, $strSQL, $bError, array($userid, $teamid));

					if (count($useracctResults) == 0) {
						$bError = true;
						$err = "Account info.";
					} else {
						// Update the user record with the useraccountinfo
						$strSQL = "UPDATE users SET useraccountinfo = ? WHERE id = ? AND teamid = ?;";
						$results = executeQuery(array($useracctResults[0]["id"], $userid, $teamid));
						if ($bError) {
							$err = "User to user account.";
						} else {
						     // reset password for this user
							if ($bgenpass){
							   resetPassword($session, $teamid, $userid, $bgenpass, $bIntro) ;
							}
						}
					}
				}
			}
		}
	}
	if ($bError)                  // failure
		return User::UserID_Undefined;
	else
		return $userid;          // Success

}

function resetPassword($session, $teamid, $userid, $bEmail, $bIntro){
	// Get the current user record

	$strSQL = "SELECT * FROM users WHERE id = ? and teamid=?;";
  $dbconn = getConnectionFromSession($session);
  $userprops = executeQuery($dbconn, $strSQL, $bError, array($userid, $teamid));
	// var_dump(array($strSQL, $userprops, $teamid, $userid, $bEmail ));
	if (count($userprops) == 1) {
		// If intro is set, add into text from team settings to the email you send the user.
		if ($bIntro){
			$strSQL = "SELECT introtext, name FROM teams WHERE id = ?;";
			$teamResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));

		 	$emailsubject = "Welcome to " . $teamResults[0]["name"] . ", " . $userprops[0]["firstname"] . "!";

			$roleid = $userprops[0]["roleid"];
			// If resetting the team admin password, and this is intro, give special text
			if (doesRoleContain($roleid, Role_TeamAdmin)){
                    $introtext = teamadmin_introtext . "\n\n";
				$introtext .= $teamResults[0]["introtext"]. "\n\n";
			} else {
				$introtext = $teamResults[0]["introtext"]. "\n\n";
			}
		} else {
			$emailsubject = appname . " Password Reset";
			$introtext = "";
		}
		echo("1");
		// Generage a new password
		$passwd_cleartext = generatePassword();
		echo("2");

		// Get the salt
		$salt = $userprops[0]["salt"];

		// If this is a new user, the salt may be empty
		if (strlen($salt) < SALT_LENGTH) {
			// Generate a new password and save the new salt
			$passwd = generateHash( $passwd_cleartext);
			$salt = getSalt( $passwd);
		} else {
			// hash the new password, with the old salt
			$passwd = generateHash( $passwd_cleartext, $salt);
		}
		// Pull the salt off
		$passwd = trimSalt( $passwd);
		// Trim the password to desired storage length
		$passwd = trimPassword( $passwd );
		var_dump($passwd_cleartext);
		// Store the new passwd and the salt
		$strSQL = "update users set passwd = ?, salt = ? where id = ?;";
		$mailok = 0;
    $results = executeQuery($dbconn, $strSQL, $bError, array($passwd, $salt, $userid));
		// var_dump(array($results, $bError));
		// Email the password to the user
		$mailok = 0;
		if ($bEmail) {
			if (strlen( $email = getEmail($userid)) >= MinLenEmail) {
			     if ($bIntro) {
			          $introtext .= "Sign on to your account at " . siteurl . " .\n\n";
			          $introtext .= "Your new account sign on ID is \"" . $userprops[0]["login"] . "\".\n";
			     }
			     $introtext .= "Your new password is \"" . $passwd_cleartext . "\".\n";
			     $introtext .= "You can change your password to your preference after you sign on.\n\n";
			     $introtext .= "This message was sent automatically from " . appname_nowhitespace . ".\n";
				$introtext .= "Please do not reply to this email, as it was sent from an automated system. Thank you.";
				$mailer = new Mail();
				$mailok = $mailer->mail($email, $emailsubject, $introtext);
			} else {
				$mailok = 0;
			}
		}
  }
}


// get team and accountinfo
function getTeam($session, $teamid, &$teamResults){

	$strSQL = "SELECT teams.id as id_team, teams.*, teamaccountinfo.* FROM teams, teamaccountinfo WHERE teamaccountinfo.teamid = teams.id AND teams.id = ?;";
  $dbconn = getConnection();
  $teamResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));
  return RC_Success;
}

// Determine if we can add more users to this team. Pass back the current member count.
function canAddUsersToTeam($session, $teamid, &$memberCount){
     $bAllowAdd = false;
	if (getTeam($session, $teamid, $teamResults) != 0){
		// Must be a real team with an assigned plan, and active
		if ((isset($teamResults)) && ($teamResults["plan"] != TeamAccountPlan_Undefined) && ($teamResults["status"] == TeamAccountStatus_Active)) {
			// They are on a "count members" plan, count Active members they have
			$memberCount = getMemberCount( $session, $teamid, getConnection());
			// if they are in an unlimited plan, pass them thru
			if ($teamResults["plan"] == TeamAccountPlan_Unlimited) {
				$bAllowAdd = true;
			} else {
				// If the number of active members they have is less than the currently allowed amount, let 'em add 'nuther
				if ($memberCount < $teamResults["plan"] ) {
					$bAllowAdd = true;
				}
			}
		}
	}
	return $bAllowAdd;
}

// This is an abstraction layer to allow us to change our barcode ID encoding in the future
// The ID can be anything (a userid, a redemptioncardid, ...)
function getUserBarcodeNumber($teamid, $id){
	return $id;
}

function promoteUser($session, $teamid, $userid, $levelID, $promotionDate){

	$strSQL = "SELECT * FROM users WHERE id = ?";
  $dbconn = getConnection();
  $userprops = executeQuery($dbconn, $strSQL, $bError, array($userid ));

	if (isset($userprops["id"])) {
		// Add a record to the promotions table with the member id and date.
		$strSQL = "INSERT INTO promotions VALUES ( DEFAULT, ?,?,?, ?);";
		$results = executeQuery($dbconn, $strSQL, $bError, array($userid, $promotionDate, $levelID, $teamid));
		return RC_Success;

	// Error
	} else {
		return RC_Promotion_Error;
	}
}

// Return the Level ID given the name (case insensitive)
function getLevelFromName($session, $teamid, $levelname){
	if (strlen($levelname) > 0){

		$strSQL = "SELECT id FROM levels WHERE name ILIKE '%'||?||'%' and teamid = ?";
    $dbconn = getConnection();
    return executeQueryFetchColumn($dbconn, $strSQL, $bError, array($levelname, $teamid ));
	} else return LevelID_Undefined;
}

function getSmsCarrierEmail($smsphonecarrier){
	$carrier_email = "";
	switch ($smsphonecarrier){
		case "alltel": $carrier_email = alltel; break;
		case "tmobile": $carrier_email = tmobile; break;
    case "googlefi": $carrier_email = googlefi; break;
		case "boost": $carrier_email = boost; break;
		case "cellularone": $carrier_email = cellularone; break;
		case "qwest": $carrier_email = qwest; break;
		case "virgin": $carrier_email = virgin; break;
		case "att": $carrier_email = att; break;
		case "verizon": $carrier_email = verizon; break;
		case "sprint": $carrier_email = sprint; break;
		case "nextel": $carrier_email = nextel; break;
		default:
			$bError = true;
			break;
	}
	return $carrier_email;
}

function getSmsPhone( $userid, $teamid, &$smsphonecarrieremail, &$err, $dbconn = null){
	$smsphone = "";
	$strSQL = "SELECT smsphone, smsphonecarrier FROM users WHERE id = ? and teamid = ?;";
  if ($dbconn == null) $dbconn = getConnection();
  $userprops = executeQuery($dbconn, $strSQL, $bError, array($userid, $teamid));
	if (count($userprops)>0) {
		$userprops = $userprops[0];
		$smsphonecarrieremail ="";
		if (isset($userprops["smsphone"])){
			$smsphone = cleanupPhone($userprops["smsphone"]);
			$smsphonecarrier = trim($userprops["smsphonecarrier"]);
			$smsphonecarrieremail = getSmsCarrierEmail($smsphonecarrier);
		} else {
			$bError = true;
			$err = "n";
		}
	} else {
			$bError = true;
			$err = "n";		
	}
		return $smsphone;
}

// Generate an "email" SMS to the current session user's smsphone
function generate2fauthn( $session, &$err, $dbconn = null){

	$bError = false;
	$err = "";
	$carrier_email = "";
  if ($dbconn == null) $dbconn = getConnectionFromSession($session);

	$smsphone = getSmsPhone( $session["userid"], $session["teamid"], $carrier_email, $err, $dbconn);
	if (strlen($err) == 0){
		// Generate an auth code
		$message= strval(rand ( 1000, 9999 ));
		// Save the auth code in the session
		$strSQL = "UPDATE sessions SET authsms = ? WHERE ipaddr = ? AND userid = ? AND teamid = ?;";
    $results = executeQuery($dbconn, $strSQL, $bError, array((int)$message, $session["ipaddr"], $session["userid"], $session["teamid"]));
		$m = new Mail1t($session);
		
		$statuscode = $m->mail("$smsphone@$carrier_email", "", $message, $session["fullname"] );
		if (!$m->statusok($statuscode)){
			$bError = true;
			$err = $statuscode;
	var_dump(array($bError, $err));

		}
	}
	return $bError;
}
?>
