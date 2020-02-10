<?php
include('utils.php');

// Assure we have the input we need, else send them to default.php
if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid = getUserID()) == RC_RequiredInputMissing)){
	redirect("default.php?rc=" . RC_RequiredInputMissing);
} else {
	// Get $session array initialized
	$session = startSession($sessionkey, $userid);
	if (! isValidSession($session )){
		redirect("default.php?rc=" . $session);
	}
	
	deleteSession($session);
	redirect("default.php?logout=1");
}?>
