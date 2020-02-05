<?php 
include('utils.php');
include_once('obj/Objects.php');
?>
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

		$acs = new AttendanceConsoles($session, 1);
		$ac = $acs->find("192.168.0.4");
		if (($ac instanceof AttendanceConsole) && ($ac->isValid())) echo $ac->getName();
	}
}?>
</pre>
</body>
</html>
