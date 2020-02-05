<?php
// The title is set here and rendered in header.php
$title= " Administer " ;
include('header.php');
$bError = false;

if (!isUser( $session, Role_ApplicationAdmin)){
	$bError = true;
	$err = "a";
	redirect($_SERVER['HTTP_REFERER']."&err=".$err);
}
if (isset($_GET["cmd"])) {
	$cmd = $_GET["cmd"];
} else {
	$bError = true;
}

if ($bError != true) {
	switch ($cmd){
		case "sessclean":
			if (($rc = Session::deleteStaleSessions($session)) != RC_Success){
				$bError = true;
				$err = $rc;
			}
			break;
		default:
			$bError = true;
			break;
	}
}
if ($bError) {
	redirect($_SERVER['HTTP_REFERER']."&err=".$err);
} else {
	echo "Success: ". $cmd.".";
}
?>
