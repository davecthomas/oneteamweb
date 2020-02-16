<?php
include_once ('globals.php');
function is_url($str) {
	return ( ! preg_match('/^(http|https):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $str)) ? FALSE : TRUE;
}

function findURLInString($text, &$matches, $offset){
	$SCHEMES = array('http', 'https', 'ftp', 'mailto', 'news',
    'gopher', 'nntp', 'telnet', 'wais', 'prospero', 'aim', 'webcal');
	// Note: fragment id is uchar | reserved, see rfc 1738 page 19
	// %% for % because of string formating
	// puncuation = ? , ; . : !
	// if punctuation is at the end, then don't include it
	$URL_FORMAT = '~(?<!\w)((?:'.implode('|',
	    $SCHEMES).'):' # protocol + :
	.   '/*(?!/)(?:' # get any starting /'s
	.   '[\w$\+\*@&=\-/]' # reserved | unreserved
	.   '|%%[a-fA-F0-9]{2}' # escape
	.   '|[\?\.:\(\),;!\'](?!(?:\s|$))' # punctuation
	.   '|(?:(?<=[^/:]{2})#)' # fragment id
	.   '){2,}' # at least two characters in the main url part
	.   ')~';
	return preg_match_all($URL_FORMAT, $text, $matches, PREG_OFFSET_CAPTURE, $offset = 0);
}
function getSession(){
	// Assure we have the input we need, else send them to default.php
	if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid = getUserID()) == RC_RequiredInputMissing)){
		redirect("/1team/default.php?rc=" . RC_RequiredInputMissing);
	}
	// Get $session array initialized
	$session = startSession($sessionkey, $userid);
	if (! isValidSession($session )){
		redirectToLogin();
	}
	return $session;
}

function isAdminLoggedIn($session){
	return (isUser( $session, Role_ApplicationAdmin));
}

// Assure the admin is logged in
function isTeamAdminLoggedIn($session){
	return (isUser( $session, Role_TeamAdmin));
}

// Assure the user is of the required role
function isUser($session, $roleid){
	if ((isset($session['roleid'])) && (doesRoleContain($session['roleid'], $roleid))) {
		return true;
	} else {
		return false;
	}
}

// Does this role contain that role. In other words, does one role contain another.
// For example, if a user's role is 5, that contains role 4 and 1
function doesRoleContain( $role, $roleTest){
	return ($role & $roleTest);
}

// Many features require a team or application admin
function isAnyAdminLoggedIn($session) {
	if (isAdminLoggedIn($session) || isTeamAdminLoggedIn($session)) return true;
	else return false;
}

// Many features require a coach, team or application admin
function isAnyAdminOrCoachLoggedIn($session) {
	if (isAdminLoggedIn($session) || isTeamAdminLoggedIn($session) || (isUser( $session, Role_Coach))) return true;
	else return false;
}

// Members and coaches
function isNonAdmin($session) {
	if (!isAdminLoggedIn($session) && isTeamAdminLoggedIn($session)) return true;
	else return false;
}

function isRoleNonAdmin($roleid) {
	if ((doesRoleContain($roleid, Role_Coach)) || (doesRoleContain($roleid, Role_Member))) return true;
	else return false;
}

// Redirect to login if not app or team admin
function redirectToLoginIfNotAdminOrCoach($session){
	if ((!isUser( $session, Role_TeamAdmin)) && (!isUser( $session, Role_ApplicationAdmin)) && (!isUser( $session, Role_Coach))){
		redirectToLogin();
	}
}


// Redirect to login if not app or team admin
function redirectToLoginIfNotAdmin($session){
	if ((!isUser( $session, Role_TeamAdmin)) && (!isUser( $session, Role_ApplicationAdmin))){
		redirectToLogin();
	}
}

// if (the currently logged in user isn//t the designated role, go to login
function redirectToLoginIfNot($session, $roleid){
	if (! isUser( $session, $roleid)) {
		redirectToLogin();
	}
}

// Save current page and redirect to login. Current page is saved to attempt redirect after successful login
function redirectToLogin(){
	Header("Location: /1team/login-form.php?login-redirect=" . $_SERVER["SCRIPT_NAME"]);
}

// assure the session key is known to our DB
function isSessionKeyValid( $sessionkey ) {
	if ((strlen($sessionkey) < SESSIONKEY_LENGTH) || (!is_string($sessionkey))) {
		return false;
	}
	$strSQL = "SELECT * FROM sessions WHERE sessionkey = '" . getCleanInput($sessionkey) . "';";

	$dbconn = getConnection();
  $results = executeQuery($dbconn, $strSQL, $bError);

	if (count($results)>0) {
		return true;
	} else {
		return false;
	}
}

// redirect to url
function redirect( $url) {
	Header("Location: http://" . $url);
}

// Build the page title for the current page
function getTitle($session, $title){
	if (isValidSession($session)) {
//		return appname . ": " . $session['fullname'] . ": " . $title;
		return appname . ": " . $title;
	} else {
		if (! isset($title)) {
			$title = "";
		}
		return getTitleNotLoggedIn($title);
	}
}

function getTitleNotLoggedIn($title){
	return appname . ": " . $title;
}

// Determine if (the give user id is the user ID of the logged in user
function isThisMe($session, $userid){
	if ($session['userid'] == $userid) return true;
	else return false;
}

// Determine if (the given user is belongs to the current logged in user's coach
function isThisMyCoach($session, $coachid){
	if ((!isset($session["userid"])) || (!isset($session["teamid"]))) return false;

	$strSQL = "SELECT coachid FROM users WHERE id = " . $session["userid"] . " and teamid = " . $session["teamid"] . ";";
	$dbconn = getConnection();
  $result_id = executeQueryFetchColumn($dbconn, $strSQL, $bError);
	if ($coachid == $result_id) return true;
	else return false;
}

// Can I administer this user
function canIAdministerThisUser( $session, $id){
	$bError = false;
	if (!isValidUserID($id)) {
		return false;
	}
	if (isThisMe($session, $id)) {
		return true;
	} else if (isAdminLoggedIn($session)) {
		return true;
	} else if (isTeamAdminLoggedIn($session)) {
		// Special case - guest users aren't on team roster, so allow "admin" of these no matter what
		if ($id == User::UserID_Guest){
			return true;
		} else {

			// See if this user is on my team
			$strSQL = "SELECT id FROM users WHERE id = ? and teamid = ?;";
			$dbconn = getConnection();
		  $results = executeQuery($dbconn, $strSQL, $bError, array($id, $session["teamid"]));

			if (count($results) == 1) return true;
			else return false;

		}
	} else {
		return false;
	}
}

// Can I view this user
function canIViewThisUser( $session, $id){
	$bError = false;
	if (!isValidUserID($id)) {
		return false;
	}
	if (isThisMe($session, $id)) {
		return true;
	} else if (isAdminLoggedIn($session)) {
		return true;
	} else if (isTeamAdminLoggedIn($session)) {

		// See if this user is on my team
		$strSQL = "SELECT id FROM users WHERE id = ? and teamid = ?;";
		$results = executeQuery( getConnectionFromSession($session), $strSQL, $bError, array($id, $session["teamid"]));
		if (count($results) == 1) return true;
		else return false;

	} else if (isThisMyCoach($session, $id)){
		return true;
	} else {
		return false;
	}
}


function getCurrentUserName($session){
	if (isset($session['fullname'])){
		return $session['fullname'];
	} else {
		return Error;
	}
}

function getUserLogin($session){
	if (isset($session['login'])){
		return $session['login'];
	} else {
		return Error;
	}
}

function getSessionUserID($session){
	if (isset($session['userid'])){
		return $session['userid'];
	} else {
		return User::UserID_Undefined;
	}
}

function getUserEmail($session, $dbconn = null){
	$strSQL = "SELECT email FROM useraccountinfo WHERE useraccountinfo.userid = ?;";
	$bError = false;
	if ($dbconn = null) $dbconn = getConnection();
	$result = executeQueryFetchColumn($dbconn, $strSQL, $bError, array($session["userid"]));
	return $result;
}

function getTeamID($session){
	if (isset($session['teamid'])){
		return $session['teamid'];
	} else {
		return TeamID_Undefined;
	}
}

// On fail return array(RC_PDO_Error)
// Updates return count of update rows
function executeQuery($dbconn, $sql, &$bError = false, $array_params = array()){
	try {
		$statement = $dbconn->prepare($sql);
	  $bError = ! $statement->execute($array_params);
		if (!$bError) {
			if (strcasecmp(explode(' ',trim($sql))[0], "UPDATE") != 0){
				$items = $statement->fetchAll();
			}
			else {
				$items = $statement->rowCount();
			}
		}
		else {
			$items = null;
		}
	} catch (Exception $e) {
		$bError = true;
		print($e->getMessage());
	}
	return $items;
}

// On fail return array(RC_PDO_Error)
function executeQueryFetchColumn($dbconn, $sql, &$bError = false, $array_params = array()){
	try {
		$statement = $dbconn->prepare($sql);
	  $bError = ! $statement->execute($array_params);
	  if (!$bError) $item = $statement->fetchColumn();
		else $items = null;
	} catch (Exception $e) {
		$bError = true;
		print($e->getMessage());
	}
	return $item;
}

function getConnectionFromSession($session = 0){
	if ($session == 0){
		return getConnection();
	} else {
		if (in_array("dbh", $session)) {
			return $session["dbh"];
		}
	}
}

function getConnection(){
	$pdo = null;

	try {
		$cleardb_url      = parse_url(getenv("CLEARDB_DATABASE_URL"));
		$cleardb_server   = $cleardb_url["host"];
		$cleardb_username = $cleardb_url["user"];
		$cleardb_password = $cleardb_url["pass"];
		$cleardb_db       = substr($cleardb_url["path"],1);
    $pdo = new PDO("mysql:host=".$cleardb_server."; dbname=".$cleardb_db, $cleardb_username, $cleardb_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (PDOException $e) {
	    print( "Connect error!: " . $e->getMessage() . "<br/>");
	}
	return $pdo;
}

function utilIsUserBillable($session){
	return $session["isbillable"];
}

// Start Session - pulls in session data from session table into session array
// It is intended to be called any time a sessionkey is present in request or form (GET or POST) parameters
// The basic idea is I have replaced the quirky $session global array with my own.
// Returns session array or error code. In either case, it is assumed the caller will redirect the user to default.php
function startSession( $sessionkey, $userid ){
	$bError = false;
	if (! isValidUserID( $userid)) {
		return RC_UserID_Invalid;
	}
	if (! isValidSessionKey( $userid, $sessionkey)) {
		return RC_SessionKey_Invalid;
	}
	try{
		// Attempt to get the session from the DB
		$strSQL = "SELECT * FROM sessions WHERE sessionkey = ? AND userid = ?;";
		$dbconn = getConnection();
		$sessionResults = executeQuery($dbconn, $strSQL, $bError, array($sessionkey, $userid));

	} catch( PDOException $Exception ) {
		if (isStagingServer() || isDevelopmentServer())
			echo( $Exception->getMessage( ) ." ". (int)$Exception->getCode( ) );
	}

	if (count($sessionResults) != 1) {
		return RC_SessionNotFound_Error;
	}

	// Before we return the session, run one more query through to see if the session should be expired
	if (isSessionExpired($sessionResults[0]["timeexpires"])) {
		return RC_SessionExpired;
	} else {
		$session = array();

		$session["ipaddr"] = $sessionResults[0]["ipaddr"];
		$session["userid"] = (int) $userid;
		$session["sessionkey"] = $sessionkey;
		$session["timecreated"] = $sessionResults[0]["timecreated"];
		$session["timeexpires"] = $sessionResults[0]["timeexpires"];
		$session["login"] = $sessionResults[0]["login"];
		$session["roleid"] = (int) $sessionResults[0]["roleid"];
		$session["fullname"] = $sessionResults[0]["fullname"];
		if (!is_null($sessionResults[0]["teamid"])){
			$session["teamid"] = (int) $sessionResults[0]["teamid"];
		} else {
			$session["teamid"] = TeamID_Undefined;
		}
		$session["isbillable"] = (bool) $sessionResults[0]["isbillable"];
		$session["status"] = (int) $sessionResults[0]["status"];
		$session["dbh"] = $dbconn;
		// This allows a quick and dirty test on the array
		$session["isvalid"] = true;

		// Get the team image
		if ( $session["teamid"] != TeamID_Undefined){
			$strSQL = "SELECT teams.*, images.* FROM teams LEFT OUTER JOIN images ON (images.teamid = teams.id) WHERE teams.id = ? and images.id = teams.imageid";
			$dbconn = getConnection();
			$teamResults = executeQuery($dbconn, $strSQL, $bError, array($session["teamid"]));

			if (count($teamResults) > 0) {
			     // Set team image
				if ((isset($teamResults[0]["url"])) && (is_url($teamResults[0]["url"])))
					$session["teamimageurl"] = $teamResults[0]["url"];
				else if (isset($teamResults[0]["filename"])){
                         $session["teamimageurl"] = "/1team/".uploadsDir."/".$session["teamid"]."/".$teamResults[0]["filename"];
				} else $session["teamimageurl"] = "";
			}
		} else $session["teamimageurl"] = "";

		return $session;
	}
}

// See if the session is expired
function isSessionExpired2($session){
	$dbconn = getConnection();
	$roleid = $session["roleid"];
	if (doesRoleContain($roleid, Role_Member)) {
		$timeExpireSQL = "1 hours";
	} else if (doesRoleContain( $roleid, Role_Coach)) {
		$timeExpireSQL = "2 hours";
	} else {
		$timeExpireSQL = "1 month";
	}

	$strSQL = "select (select timeexpires from sessions where userid = " . $session["userid"] . " and ipaddr = '" . $session["ipaddr"] . ")  + cast('" . $timeExpireSQL . "' as interval) - current_date as expired;";
	$dbconn = getConnection();
	$teamResults = executeQuery($dbconn, $strSQL, $bError);

	$expired = $results[0]["expired"];
	echo "expires= " . $expired;
	if ($expired<1) return true;
	else return false;
}

// Validate a session key:
// 1. Get IP address and userid and generate a key
// 2. Check the key for a match
// 3. Make sure the session is in the sessions table and not expired
// The assumption is that a false return will have the caller redirect to default.php
function isValidSessionKey( $userid, $sessionkey ){
	// Generate a session key and test for a match
	$ipaddr = (string) $_SERVER["REMOTE_ADDR"];
	if (($sessionkeyTest = createSessionKey( $ipaddr, $userid)) == RC_SessionKey_Invalid) {
		return false;
	}
	if (strcmp($sessionkeyTest, $sessionkey) != 0){
		return false;
	}

	// Make sure the session is in the sessions table
	$strSQL = "SELECT timeexpires FROM sessions WHERE sessionkey = '" . $sessionkey . "' AND userid = " . $userid . ";";
	$dbconn = getConnection();
	$rs = executeQuery($dbconn, $strSQL, $bError);
	// If no results, create a session record
	if (count($rs) == 0) {
		return false;
	}
	// if Session is expired, delete the row and return false
	if (isSessionExpired($rs["timeexpires"])) {
		// delete the session row
		$strSQL = "DELETE FROM sessions WHERE sessionkey = '" . $sessionkey . "' AND userid = " . $userid . ";";
		// ignore the rc of odbc_exec, since we are returning false anyway
		$rs = executeQuery($dbconn, $strSQL, $bError);
		return false;
	}

	return true;
}

// Generate a new session key
// This is a hash of user id and ipaddr
function createSessionKey($ipaddr, $userid){
	$sessionKey = RC_SessionKey_Invalid;

	$sessionKey = trimSessionKey(generateHash($ipaddr, $userid));

	return $sessionKey;
}

// Don't want to store a long string, so we trim it down
function trimSessionKey( $sessionKey){
	return substr($sessionKey, 0, SESSIONKEY_LENGTH);
}

// returns true if session has expired, else false
// This should only be called on startSession. The idea is that a false return will cause logout, deletion of session record, and redirect to login page
function isSessionExpired( $timeexpires){
	$strSQL = "select age(current_timestamp, '" . $timeexpires . "');";
	$dbconn = getConnection();
	$rs = executeQuery($dbconn, $strSQL, $bError);
	if (count($rs)>0) {
		$age = $rs["age"];
		// If the result is positive, the session is expired
		return ((bool) ($age > 0));
	} else {
		return true;
	}
}

// For userheader returns string of how much time is left
function getSessionTimeRemaining( $session){
	$timeexpires = $session["timeexpires"];
	// This should never be null, but I saw some unexplained errors in SQL log
	if (is_null($timeexpires)) {
		return "";
	}
	$strSQL = "select age('" . $timeexpires . "', current_timestamp);";
	$dbconn = getConnection();
	$rs = executeQuery($dbconn, $strSQL, $bError);
	if (count($rs)>0) {
		$age = $rs["age"];
		// If the result is positive, the session is expired
		$aage = explode(":", $age);
		if ($aage[0] == "00") {
			$aage[0] = "";
		} else if ($aage[0] == "01") {
			$aage[0] = "1 hour";
		} else {
			$aage[0] = $aage[0] . " hours";
		}
		return $aage[0] . " " . $aage[1] . " minutes.";
	} else {
		return "";
	}
}

// getSession - returns the $_GET or $_POST sessionkey, if it exists
// returns RC_RequiredInputMissing on failure
function getSessionKey(){
	if (isset($_GET["sessionkey"])){
		return $_GET["sessionkey"];
	} else {
		if (isset($_POST["sessionkey"])){
			return $_POST["sessionkey"];
		} else {
			return RC_RequiredInputMissing;
		}
	}
}

// getUserID - returns the $_GET or $_POST userid, if it exists
// returns RC_RequiredInputMissing on failure
function getUserID(){
	if (isset($_GET["userid"])){
		return $_GET["userid"];
	} else {
		if (isset($_POST["userid"])){
			return $_POST["userid"];
		} else {
			return RC_RequiredInputMissing;
		}
	}
}

// Builds the common request parameters from the session array
function buildRequiredParams($session){
	if ((is_array($session)) && (isset($session["sessionkey"])) && (isset($session["userid"]))){
		echo "?sessionkey=" . $session["sessionkey"] . "&userid=" . $session["userid"];
	}
}

// Special case when building on to an existing url string in PHP
function buildRequiredParamsConcat($session){
	if ((is_array($session)) && (isset($session["sessionkey"])) && (isset($session["userid"]))){
		return "&sessionkey=" . $session["sessionkey"] . "&userid=" . $session["userid"];
	}
}

// Special case when building on to an existing url string in PHP
function returnRequiredParams($session){
	if ((is_array($session)) && (isset($session["sessionkey"])) && (isset($session["userid"]))){
		return "sessionkey=" . $session["sessionkey"] . "&userid=" . $session["userid"];
	}
}

// Builds the required POST hidden form fields
function buildRequiredPostFields($session){
	if ((is_array($session)) && (isset($session["sessionkey"])) && (isset($session["userid"]))){
		echo "
<input type=\"hidden\" name=\"sessionkey\" value=\"" . $session["sessionkey"] . "\"/>
<input type=\"hidden\" name=\"userid\" value=\"" . $session["userid"] . "\"/>";
	}
}

// Hash a string with optional salt. No salt creates random salt.
// Returns one of the RC_Success failure codes on error
function generateHash($plainText, $salt = null){
    if ($salt === null) {
        $salt = substr(md5(uniqid(rand(), true)), 0, SALT_LENGTH);
    } else {
        $salt = substr($salt, 0, SALT_LENGTH);
    }

	if (($strsha1 = sha1($salt . $plainText)) == false){
		return RC_HashFailure;
	}

    return $salt . $strsha1;
}

// Return the leading salt off
function getSalt( $salted ){
	return substr($salted, 0, SALT_LENGTH);
}

// De-salt the string
function trimSalt( $salted){
	return substr($salted, SALT_LENGTH);
}

// The password has a max length
function trimPassword( $passwd) {
	return substr($passwd, 0, PASSWORD_LENGTH);
}

// Generate a random string CleartextPasswordLength long
function generatePassword(){
	return substr(md5(uniqid(rand(), true)), 0, CleartextPasswordLength);
}

// getTeamInfo - Get the team settings from DB and return as array
// This must be called by any script that uses team table values.
function getTeamInfo( $id){
	if (!isValidTeamID( $id)) {
		return RC_TeamID_Invalid;
	}
	$teaminfo = array();

	$strSQL = "SELECT * FROM teams WHERE id = " . $id . ";";

	$dbconn = getConnection();
	$rsTeam = executeQuery($dbconn, $strSQL, $bError);

	if (count($rsTeam)>0) {
		$team = $rsTeam[0];
		$teaminfo["teamname"] = $team["name"];
		$teaminfo["coachid"] = (int) $team["coachid"];
		$teaminfo["activityname"] = $team["activityname"];
		$teaminfo["paymenturl"] = $team["paymenturl"];
		$teaminfo["startdate"] = $team["startdate"];
		$teaminfo["eventidattendance"] = $team["eventidattendance"];
		$teaminfo["website"] = $team["website"];
		return $teaminfo;
	} else {
		return RC_TeamInfoError;
	}
}

// Make sure the User ID is valid
function isValidUserID( $id){
	return ((is_numeric($id)) && ($id != User::UserID_Undefined));
}

// Make sure the User ID is valid
function isValidUserRoleID( $id){
	return ((is_numeric($id)) && ($id >= Role_Undefined));
}


// Make sure the User ID is valid
function isValidTeamID( $id){
	return ((is_numeric($id)) && ($id >= TeamID_Base));
}

// Make sure the session array has the required setting to assure it has been set
function isValidSession( $session){
	if (!is_array($session)){
		return false;
	}
	if ((isset($session["isvalid"])) && ($session["isvalid"] == true)){
		return true;
	} else {
		return false;
	}
}

// Clean the input or return null
function getCleanInput( $input) {
	if (isset($input)){
		return cleanSQL($input);
	} else {
		return NULL;
	}
}
// This removes cr lf characters, which is a common hacker attack for email forms
// Getting rid of these could prevent them from taking over email forms
function cleanCRLF($str){
	$cleanStr = str_replace(chr(13), "", $str);
	$cleanStr = str_replace(chr(10), "", $cleanStr);
	return $cleanStr;
}

// Simple method for cleaning out SQL injection by replacing // with ////
function cleanSQL( $str){
	if (is_numeric( $str)) {
		// prevent loss of precision by seeing if (there is a floating point part of the number
		if (((int)$str) - ((float)$str) != 0) {
			return (float)($str);
		} else {
			return (int)($str) ;
		}
	} else {
		$str = str_replace("'","''", trim($str));
		return str_replace("//", "////", $str);
	}
}

// Extremely simplistic method of determining there is an embedded url
// Great for detecting embedded url attacks. This is not useful for postive tests of the quality of a url.
function isURL( $str){
	if (stristr($str, "http") > 0) {
		return true;
	} else {
		return false;
	}
}


// Return yes or no
function BoolToStr( $boolval ){
	if ($boolval) {
		return "Yes";
	} else {
		return "No";
	}
}

function boolToTFStr( $boolval){
	if ($boolval) {
		return "true";
	} else {
		return "false";
	}
}

//
function getAdminEmail($session, $dbconn = null){
	$strSQL = "SELECT email FROM useraccountinfo, users WHERE useraccountinfo.teamid = ? AND users.useraccountinfo = useraccountinfo.id AND users.roleid = ?;";
	if ($dbconn == null) $dbconn = getConnection();
	return executeQueryFetchColumn($dbconn, $strSQL, $bError, array($session["teamid"], Role_TeamAdmin));
}

// formats money to a whole number or with 2 decimals; includes a dollar sign in front
function formatMoney($number, $cents = 2) { // cents: 0=never, 1=if needed, 2=always
  if (is_numeric($number)) { // a number
    if (!$number) { // zero
      $money = ($cents == 2 ? '0.00' : '0'); // output zero
    } else { // value
      if (floor($number) == $number) { // whole number
        $money = number_format($number, ($cents == 2 ? 2 : 0)); // format
      } else { // cents
        $money = number_format(round($number, 2), ($cents == 0 ? 0 : 2)); // format
      } // integer or decimal
    } // value
    return '$'.$money;
  } // numeric
} // formatMoney

// Returns boolean true if success, false on failure
// Assumes rc is one of the sequence of success/error codes based on RC_Success in globals.php
function isSuccessful( $rc){
	return (($rc >= RC_Success) ? true : false);
}

// Get the team terms array. Must be used by any script needing to display team terms
function getTeamTerms(	$teamid, $dbconnection = null){

	$teamterms = array();

	// Default values
	$termuser = defaultterm_user;
	$termadmin = defaultterm_admin;
	$termcoach = defaultterm_coach;
	$termmember = defaultterm_member;
	$termteam = defaultterm_team;
	$termclass = defaultterm_class;
	if ($teamid >= TeamID_Base) {
		$strSQL = "SELECT * FROM teamterms WHERE teamid = ?;";
		if ($dbconnection == null) $dbconn = getConnection();
		$termsResults = executeQuery($strSQL, $dbconn, $bError, array($teamid));
		if (count($termsResults)>0) {
			$teamTermResult = $termsResults[0];
			$termadmin = $teamTermResult["termadmin"];
			$termcoach = $teamTermResult["termcoach"];
			$termmember = $teamTermResult["termmember"];
			$termteam = $teamTermResult["termteam"];
			$termclass = $teamTermResult["termclass"];
		}
	}

	// Store the terms in the array
	$teamterms["termuser"] = $termuser;
	// The next 3 terms are tied to roles in the app, and are customizable
	$teamterms["termadmin"] = $termadmin;
	$teamterms["termcoach"] = $termcoach;
	$teamterms["termmember"] = $termmember;
	$teamterms["termteam"] = $termteam;
	$teamterms["termclass"] = $termclass;

	return $teamterms;
}

function isProductionServer(){
	if (strcasecmp($_SERVER['HTTP_HOST'], productionserveraddr) == 0) {
		return true;
	} else {
		return false;
	}
}

// Determine if (we are running on the staging server (affects CSS render of background reminder text)
function isStagingServer(){
	if (strcasecmp($_SERVER['HTTP_HOST'], stagingserveraddr) == 0) {
		return true;
	} else {
		return false;
	}
}

function isDevelopmentServer(){
	$serveraddr = $_SERVER['HTTP_HOST'];
	if (strcasecmp($serveraddr, "::1") == 0) {
		return true;
	} else if (strcasecmp($serveraddr, "localhost") == 0) {
		return true;
	} else {
		return false;
	}
}

// Decide where to get our data. On the staging server, we reach over and use the production database
function getDBServer(){
	if (isStagingServer()){
		return productionserveraddr;
	} else {
		return "localhost";
	}
}

// Test 2 addresses for general equivalence.
// The match_mask allows you to select 8, 16, 24, or 32 bits of matching on the IP address
// For example, if you select mask of 24, 123.12.3.1 is equivalent to 123.12.3.24
function isIPAddressEquivalent( $strIP1, $strIP2, $match_mask = 32) {
	// First - clean up the input. This is how PHP recommends we do this.
	$strIP1 = long2ip(ip2long(trim($strIP1)));
	$strIP2 = long2ip(ip2long(trim($strIP2)));
	// Convert the IPs to numbers
	$check_ip = ip2long($strIP1);
	$match_ip = ip2long($strIP2);
	for ($i = 0 ; $i < $match_mask ; $i++) {
		$n = pow(2, 31 - $i) ;
		if (($n & $check_ip) != ($n & $match_ip)){
		    return false ;
		}
	}
	return true ;
}


// Determine if (the user is running the application from Admin system
// This unlocks app admin login
function isAdminSystem( ){
	$result = false;
	// Get the address of the client
	$clientaddr = $_SERVER["REMOTE_ADDR"];
	// Compare it to the stored session value
	if (strcmp($clientaddr, productionserveraddr) == 0) {
		$result = true;
	} else {
		// allow localhost, which returns ::1
		if (strcmp($clientaddr, "::1") == 0) {
			$result = true;
		} else {
			// allow subnet
			$arrayclientaddr = explode(".", $clientaddr);
			$arrayadminip = explode(".",productionserveraddr);
			// if there aren't 4 parts to this address, we're probably running from localhost, so pass
			if ((is_array($arrayclientaddr)) && (count($arrayclientaddr) > 2) &&
				(is_array($arrayadminip)) && (count($arrayadminip) > 2)) {
				if (($arrayclientaddr[0] == $arrayadminip[0]) &&
					($arrayclientaddr[1] == $arrayadminip[1]) && ($arrayclientaddr[2] == $arrayadminip[2])) {
					$result = true;
				}
			}
			// Final attempt - if coming from same subnet, first 3 octets of HTTP_HOST will be same as client
			if ($result == false) {
				$arrayserverhttphost = explode(".", $_SERVER['HTTP_HOST']);
				if (($arrayclientaddr[0] == $arrayserverhttphost[0]) &&
					($arrayclientaddr[1] == $arrayserverhttphost[1]) && ($arrayclientaddr[2] == $arrayserverhttphost[2])) {
					$result = true;
				}
			}
		}
	}

	return $result;
}

// Show an error div. See .css for render details
// javascriptnext - gives us a handy way of assigning form focus after OK button is clicked (such as in a "bad input" error case)
function showError( $errorTitle, $errorText, $javascriptnext){
	echo "<div class=\"errorboxshow\" id=\"errorbox\">
<div class=\"errorboxtitle\">". $errorTitle . "<div class=\"errorboxclose\"><a class=\"linkopacity\" href=\"javascript:togglevis2('errorbox', 'errorboxshow', 'errorboxhide')\"><img src=\"img/x-closebutton.png\" border=\"0\"/></a></div><hr /></div>"
. $errorText .
"<br ><br >
<div class=\"boxbutton\"><input type=\"button\" value=\"Ok\" class=\"btn\" onmouseover=\"this.className='btn btnhover'\" onmouseout=\"this.className='btn'\" onClick=\"javascript:togglevis2('errorbox', 'errorboxshow', 'errorboxhide');" . $javascriptnext . "\"></div>
</div>";

}

// Show an message box. See .css for render details
// javascriptnext - gives us a handy way of assigning form focus after OK button is clicked (such as in a "bad input" error case)
function showMessage( $msgTitle, $msgText){
	echo "<div class=\"msgboxshow\" id=\"msgbox\">
<div class=\"msgboxtitle\">". $msgTitle . "<div class=\"msgboxclose\"><a class=\"linkopacity\" href=\"javascript:togglevis2('msgbox', 'msgboxshow', 'msgboxhide')\"><img src=\"img/x-closebutton.png\" border=\"0\"/></a></div><hr /></div>"
. $msgText .
"<br ><br >
<div class=\"boxbutton\"><input type=\"button\" value=\"Ok\" class=\"btn\" onmouseover=\"this.className='btn btnhover'\" onmouseout=\"this.className='btn'\" onClick=\"javascript:togglevis2('msgbox', 'msgboxshow', 'msgboxhide');\"></div>
</div> <script language=\"javascript\">setTimeout(\"timedfade('msgbox', 1000)\",3000)</script>";

}

// Show a message with a button in div. See .css for render details
// javascriptnext - gives us a handy way of submitting form OK button is clicked
function showConfirmMessage( $msgTitle, $msgText, $javascriptnext){
	echo "<div class=\"confirmboxhide\" id=\"confirmbox\">
<div class=\"confirmboxtitle\">". $msgTitle . "<div class=\"confirmboxclose\"><a class=\"linkopacity\" href=\"javascript:togglevis2('confirmbox', 'confirmboxshow', 'confirmboxhide')\"><img src=\"img/x-closebutton.png\" border=\"0\"/></a></div><hr /></div>"
. $msgText .
"<br ><br >
<div class=\"boxbutton1\"><input type=\"button\" value=\"Ok\" class=\"btn\" onmouseover=\"this.className='btn btnhover'\" onmouseout=\"this.className='btn'\" onClick=\"javascript:togglevis2('confirmbox', 'confirmboxshow', 'confirmboxhide');" . $javascriptnext . "\"></div>
<div class=\"boxbutton2\"><input type=\"button\" value=\"Cancel\" class=\"btn\" onmouseover=\"this.className='btn btnhover'\" onmouseout=\"this.className='btn'\" onClick=\"javascript:togglevis2('confirmbox', 'confirmboxshow', 'confirmboxhide');\"></div>
</div>";

}

function roleToStr( $roleid, $teamterms){
	$rolestr = "";
	$rolesarray = array();
	// Build the string from most important to least important role
	if (doesRoleContain($roleid, Role_ApplicationAdmin)) {
		array_push($rolesarray, defaultterm_appadmin);
	}
	if (doesRoleContain($roleid, Role_TeamAdmin)) {
		array_push($rolesarray, $teamterms["termadmin"]);
	}
	if (doesRoleContain($roleid, Role_Coach)) {
		array_push($rolesarray, $teamterms["termcoach"]);
	}
	if (doesRoleContain($roleid, Role_Member))  {
		array_push($rolesarray, $teamterms["termmember"]);
	}
	$numroles = count($rolesarray);
	for ($i = 0; $i<$numroles;$i++){
		// Make sure the , and stuff reads nice
		if (($i > 0) && ($numroles == 2)) $rolestr .= " and ";
		elseif (($numroles > 2) && ($i == $numroles-1)) $rolestr .= ", and ";
		elseif (($i > 0) && ($numroles > 1)) $rolestr .= ", ";
		$rolestr .= $rolesarray[$i];
	}
	return $rolestr;
}


// Validate an email address
function isValidEmail($email) {
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if  (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }

   }
   return $isValid;
}

function isValidDate($date) {
    if ((strlen($date) >= 8) && (strlen($date) <= 10)) {
        $pattern = '/\.|\/|-/i';    // . or / or -
        preg_match($pattern, $date, $char);

        $array = preg_split($pattern, $date, -1, PREG_SPLIT_NO_EMPTY);

        if(strlen($array[2]) == 4) {
            // dd.mm.yyyy || dd-mm-yyyy
            if($char[0] == "."|| $char[0] == "-") {
                $month = $array[1];
                $day = $array[0];
                $year = $array[2];
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
    } else {
        return FALSE;    // more or less 10 chars
    }
}

// This strips non-numerics and returns only the phone number
// format = false - just return 121345678901
// format = true - return 1(234) 567-8901
function cleanupPhone($phone = '', $format = false)
{
	// If we have not entered a phone number just return empty
	if (empty($phone)) {
		return '';
	}

	// Strip out any extra characters that we do not need only keep letters and numbers
	$phone = preg_replace("/[^0-9A-Za-z]/", "", $phone);

	// If we have a number longer than 11 digits cut the string down to only 11
	// This is also only ran if we want to limit only to 11 characters
	if (strlen($phone)>11) {
		$phone = substr($phone, 0, 11);
	}

	// Perform phone number formatting here
	if ($format){
		if (strlen($phone) == 7) {
			return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1-$2", $phone);
		} elseif (strlen($phone) == 10) {
			return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "($1) $2-$3", $phone);
		} elseif (strlen($phone) == 11) {
			return preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1($2) $3-$4", $phone);
		} else return $phone;
	} else return $phone;
}

// Remove the session. Typically only used on logout.
function deleteSession($session){

	$strSQL = "DELETE FROM sessions WHERE userid = ? AND sessionkey = ?;";
	$dbconn = getConnection();
	$results = executeQuery($strSQL, $dbconn, $bError, array($session["userid"], $session["sessionkey"]));
	$session = array();
}
// returns true if session has expired, else false
// This should only be called on startSession. The idea is that a false return will cause logout, deletion of session record, and redirect to login page
function isLockedOut( $userid, $teamid, $dbconn = null){
	if ($dbconn == null) $dbconn = getConnection();

	$strSQL = "SELECT timelockoutexpires FROM users WHERE id = ? AND teamid = ?";
	$dbconn = getConnection();
	$timelockoutexpires = executeQueryFetchColumn($strSQL, $dbconn, $bError, array($userid, $teamid));
	if (empty($timelockoutexpires)) return false;
	else {
		$strSQL = "select ('".$timelockoutexpires."' > current_timestamp );";
		$isLocked = executeQueryFetchColumn($strSQL, $dbconn, $bError);
		// If the result is positive, the session is expired
		return $isLocked;
	}
}

function includeDojoStyle(){
//	The Google way
//	echo '<link rel="stylesheet" type="text/css" href="https://ajax.googleapis.com/ajax/libs/dojo/1.3/dijit/themes/'.dojostyle.'/'.dojostyle.'.css" />';
?>
<link rel="stylesheet" type="text/css" href="/js/dojo-release/dijit/themes/<?php echo dojostyle.'/'.dojostyle.'.css" />';
}
function includeDojo(){ ?>
<script type="text/javascript" src="/js/dojo-release/dojo/dojo.js" djConfig="isDebug: true, parseOnLoad: true"></script>
<script type="text/javascript">
djConfig = { parseOnLoad: true };
</script>

<script type="text/javascript">
dojo.require("dijit.form.DateTextBox");
dojo.require("dojo.parser");
dojo.require("dojo.dnd.Source");
</script>
<?php
// The Google way
//	echo '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
//	echo "\n";
//	echo '<script type="text/javascript">';
//	echo "\n";
//	echo 'djConfig = { parseOnLoad: true };';
//	echo "\n";
//	echo 'google.load("dojo", "1.4.0");';
//	echo "\n";
//	echo '</script>';
//	echo "\n";

}

function getRoot(){
	if (isProductionServer()){
		return url_sslroot;
	} else if (isStagingServer()){
		return url_sslroot;
	} else if (isDevelopmentServer()){
		return url_root_dev;
	}
}

?>
