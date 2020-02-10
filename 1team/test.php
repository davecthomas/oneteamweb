<?php
$title = "test";
include_once('header-minimal.php'); ?>
<body class="<?php echo dojostyle?>">
<div id="wrapper">
<h3><?php echo $title?></h3>
<?php
$bError = FALSE;
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


if (isStagingServer() || isDevelopmentServer())
	echo "YEP.";
else echo $_SERVER['HTTP_HOST'];

include('footer.php'); ?>
</body>
</html>
