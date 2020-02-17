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

// teamid depends on who is calling
if (!isUser($session, Role_ApplicationAdmin)){
	if (isset($session["teamid"])){
		$teamid = $session["teamid"];
	}
} else {
	if ((isset($_POST["teamid"]))&& (is_numeric($_POST["teamid"]))) {
		$teamid = $_POST["teamid"];
	} else {
		$bError = true;
		$errno = "teamid";
	}
}
// objid is the object identifier that we are associating an image with.
// (teams, promotions, events, users)
if ((isset($_POST["objid"])) && (is_numeric($_POST["objid"]))){
	$objid = $_POST["objid"];

} else {
	$bError = true;
	$errno = "objid";
}
// Type is the type of object this covers.
// See globals.php for ImageType_ constants
if ((isset($_POST["type"]))&& (is_numeric($_POST["type"]))){
	$type = $_POST["type"];
} else {
	$bError = true;
	$errno = "type";
}

$filename = "";
$imageurl = "";
// Optionally grab a url, which overrides any uploaded file
if ((isset($_POST["url"])) && (is_url($_POST["url"]))){
	$imageurl = $_POST["url"];
	$hasUploadImage = false; // url overrides image
} else {
	// No url - see if there is a file to upload
	if ((isset($_FILES["image"]["name"])) && (!$bError)) {
		//Get the file information
		$userfile_name = $_FILES["image"]["name"];
		$userfile_tmp = $_FILES["image"]["tmp_name"];
		$userfile_size = $_FILES["image"]["size"];
		$userfile_base = basename($_FILES["image"]["name"]);
		$file_ext = substr($userfile_base, strrpos($userfile_base, ".") + 1);

		//Only process if the file is a JPG and below the allowed limit  of 1MB
		if((isset($_FILES["image"])) && ($_FILES["image"]["error"] == 0)) {

			if ((($file_ext!="jpg") && ($file_ext!="png") && ($file_ext!="gif")) || ($userfile_size > 1024 * 1000)) {
				$bError = true;
				$errno= "Only jpeg, png, or GIF images under 1MB are accepted for upload";
			}
			$hasUploadImage = true;
		} else{
			$bError = true;
			$errno= "Select an image file";
		}

		// Make sure the temp file exists
		if (!file_exists($userfile_tmp)) {
		     $bError = true;
			$errno = "Not found";
		}
		if (!$bError){
			// Once we're sure we have a good object, generate a filename for it
			// Create a hash of the input object type and object id to create a filename
			// This guarantees that our hash can be recreated given the object ID and type,
			// and that the file will be overwritten if a replacement for the same object is uploaded
			$filename = generateHash($teamid . "-" . $type . "-" .$objid) . "." . $file_ext;
			// Attempt to create a directory for team uploads
			if (!is_dir(uploadsDir."/$teamid/"))
				if (!mkdir(uploadsDir."/$teamid/")) {
				     $bError = true;
					$errno = "md";
				}
			// rename the uploaded file to the hash name.
			if (!move_uploaded_file($userfile_tmp, uploadsDir."/$teamid/$filename") ) {
			     $bError = true;
			     $errno = "move";
			}  else {
			     $session["teamimageurl"] = uploadsDir."/$teamid/$filename";
			}
		}
	} else {
		$bError = true;
		$errno = "FILES";
	}
}

// Time to update the images table
if (!$bError) {
	// Check if this is an INSERT or UPDATE
	$strSQL = "SELECT * FROM images WHERE type = ? and objid = ? and teamid = ?;";
	$dbconn = getConnectionFromSession($session);
	$existingImage = executeQuery($dbconn, $strSQL, $bError, array($type, $objid, $teamid));
	if ($bError) $errno = "Exist";

	if (!$bError){
		// Image for this object exists, do an update
		if (count($existingImage) == 1){
			$imageid = $existingImage[0]["id"];
			// Delete existing image in dir
			if ((isset($existingImage[0]["filename"])) && (strlen($existingImage[0]["filename"])>0) && (file_exists(uploadsDir."/$teamid/".$existingImage[0]["filename"]))) {
			     unlink(uploadsDir."/$teamid/".$existingImage[0]["filename"]);
			}
			$strSQL = "UPDATE images SET url = ?, filename = ?, type = ?, objid = ? WHERE id = ? AND teamid = ?";
			executeQuery($dbconn, $strSQL, $bError, array($imageurl, $filename, $type, $objid, $imageid, $teamid));
			if ($bError) $errno = "Update";
		// Image for this object does not exist, do an insert
		} else {

			$strSQL = "INSERT into images VALUES( DEFAULT, ?, ?, ?, ?, ?)";
			executeQuery($dbconn, $strSQL, $bError, array($imageurl, $filename, $teamid, $type, $objid));
			if ($bError) $errno = "Insert";

			// Get the image id
			$strSQL = "SELECT LAST_INSERT_ID();";
			$imageid = executeQueryFetchColumn($dbconn, $strSQL, $bError, array($type, $objid, $teamid));
			if ($bError)
				$errno = "GetNew";

			// Table for imageid refernce depends on the object type
			switch ($type){
				case ImageType_Team: $tablename = "teams";
					$session["teamimageurl"] = $imageurl;
					break;
				case ImageType_User: $tablename = "users";
					break;
				case ImageType_Promotion: $tablename = "promotions";
					break;
				case ImageType_Event: $tablename = "events";
					break;
				default:
					$bError = true;
					$tablename = "";
					$errno = "type2";
					break;
			}

			if (!$bError){
				// Update the object's table imageid reference
				// Note, I removed the teamid field from this query since it doesn't work for storing team images (team's id is "id" in that table)
				$strSQL = "UPDATE " . $tablename . " SET imageid = ? WHERE id = ?";
				executeQuery($dbconn, $strSQL, $bError, array($imageid, $objid));
				if ($bError) $errno = "ObjUpdate";
			}
		}
		if (!$bError)
			redirectToReferrer( "&done=1");

	}
}

if ($bError) {
	redirectToReferrer( "&err=" . urlencode($errno));
} ?>
