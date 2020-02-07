<?php 
include('utils.php'); ?>
<html>
<body>
<pre>
<?php
// Assure we have the input we need, else send them to default.php
if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid = getUserID()) == RC_RequiredInputMissing)){
	echo "RC_RequiredInputMissing";
} else {
	// Get $session array initialized
	$session = startSession($sessionkey, $userid);
	if ( isValidSession($session )){
		echo "Logged in<br>\n";
		print_r(array_values($session));	
	} else {
		echo "NOT logged in";
	} 
}?>
</pre>
</body>
</html>
