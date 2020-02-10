<?php
ob_start(); 	// This caches non-header output, allowing us to redirect after header.php
$title= " Reset Password " ;
include('header.php');

$dbconn = getConnection();
// Make sure we have an ID
$id = User::UserID_Undefined;
if (isset($_GET["id"])) {
	$id = $_GET["id"];
// if not in request parameters, see if it was passed in from form (as from reset-password-form)
} else if (isset($_POST["id"])) {
	$id = $_POST["id"];
// If not in the request, see if it's in the session
} else if ((isset($session["userid"])) && (isValidUserID($session["userid"]))) {
	$id = $session["userid"];
}

// teamid depends on who is calling
if (!isUser($session, Role_ApplicationAdmin)){
	if (isset($session["teamid"])){
		$teamid = $session["teamid"];
	} else {
	     $bError = true;
		$err = "t";
	}
} else {
	if (isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	} else {
	     $bError = true;
		$err = "t";
	}
}
// If no ID, give ambiguous error
if (($id == User::UserID_Undefined) || (!isValidUserID($id))) { ?>
<h3 class="usererror"><?php echo $title ?></h3>
<div class="indented-group-noborder">
<p class="usererror"><?php echo Error ?><br><br>
<a class="action" href="home.php">Home</a></p>
</div>
<?php 
} else {
	// Assure current user is allowed to work on this user
	if (!canIAdministerThisUser( $session, $id)){ ?>
<h3 class="usererror"><?php echo $title ?></h3>
<div class="indented-group-noborder">
<p class="usererror"><?php echo NotAuthorized ?><br><br>
<a class="action" href="home.php">Home</a></p>
</div>
<?php	
	} else {
	     $intro = FALSE;
		if (isset($_GET["intro"])) {
			$intro = $_GET["intro"];
		}
		// If a checkbox is set in post, that means it was checked
		if (isset($_POST["sendemail"])) {
			$sendemail = true;
		} else {
			$sendemail = false;
		}

		$bError = resetPassword($session, $teamid, $id, $sendemail, $intro);
		
		if (!$bError){
		  ?>
<h3>Password Reset</h3>
<div class="indented-group-noborder">
<p>Password for <a href="user-props-form.php?id=<?php echo $id . "&" . returnRequiredParams($session) . "&teamid=".$teamid?>"><?php echo getUserName2($id, $dbh)?></a> has been reset
<?php
//				if (isAnyAdminLoggedIn($session)){
//					echo "to \"" . $passwd_cleartext . "\".";
//				}
				if ($sendemail) echo " and emailed to " . getEmail($id) . ".\n"; ?>
</p>
</div>
<?php	}

	}
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
<?php
ob_end_flush();?>
