<?php
// The title is set here and rendered in header.php
$title= " Change Password " ;
include('header.php');

$bError = false;
$dbh = getDBH($session);  
$formPasswordOld = cleanSQL(trim($_POST["current-password"]));
$formPasswordNew = cleanSQL(trim($_POST["new-password"]));

$strSQL = "SELECT * FROM users WHERE id = ?;";
$pdostatement = $dbh->prepare($strSQL);
$pdostatement->execute(array($session["userid"]));

foreach ($pdostatement as $row) { 
	// Get the salt
	$salt = $row["salt"];
	// Hash the old password with old salt
	$passwordencsalted_form = generateHash($formPasswordOld, $salt);
	// Remove salt 
	$passwordenc_nosalt_form = substr($passwordencsalted_form, SALT_LENGTH);
	// Chop down to PASSWORD_LENGTH
	$passwordenc_form = substr($passwordenc_nosalt_form, 0, PASSWORD_LENGTH);

	// Get the current password
	$passwordenc_db = $row["passwd"];
	
	// Compare to what's in the db 
	if (strcmp($passwordenc_db, $passwordenc_form) != 0 ) { ?>
<h3 class="usererror"><?php echo $title ?></h3>
<div class="indented-group-noborder">
<p class="usererror">The credentials you entered are not recognized<br><br>
<a class="action" href="reset-password.php?id=<?php echo $session["userid"]?>">Reset my password</a><br>
<a class="action" href="change-password-form.php?id=<?php echo $session["userid"]?>">Try again</a></p>
</div>
<?php
	} else { 
		// Old password checks out, now change the password
		// hash the new password, with the old salt
		$passwd = generateHash( $formPasswordNew, $salt);
		// Pull the salt off 
		$passwd = trimSalt( $passwd);
		// Trim the password to desired storage length
		$passwd = trimPassword( $passwd );
		// Store the new passwd
		$strSQL = "update users set passwd = ? where id = ?;";
		$pdostatement = $dbh->prepare($strSQL);
		if ($pdostatement->execute(array($passwd, $session["userid"]))) {
?>
<h3>Password changed</h3>
<p>Password for <a href="home.php<?php buildRequiredParams($session)?>"><?php echo $session["fullname"] ?></a> changed.</p>
<?php
		} else {
			echo "Error";
		}
	}
} 	

// Start footer section
include('footer.php'); ?>
</body>
</html>
