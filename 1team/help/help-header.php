<?php 
include_once('../utils.php');
// Login isn't required, but check to see anyway
$sessionkey = getSessionKey();
$userid = getUserID();
// Get $session array initialized
if ((isset($_GET["sessionkey"])) && (isset($_GET["userid"]))) {
	$session = startSession($sessionkey, $userid);
	if (! isValidSession($session )){
		redirectToLogin();
	}
	$title = getTitle($session, $title);
	$teamterms = getTeamTerms(getTeamID($session), getConnectionFromSession($session));
} else {
	$session = RC_SessionKey_Invalid;
	$title=getTitleNotLoggedIn($title);
} ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><?php echo $title?></title>
<link rel="stylesheet" type="text/css" href="/1team/1team.css"/>
<script type="text/javascript" src="/1team/utils.js"></script>
</head>
<body>
<?php if ($session != RC_SessionKey_Invalid) include('../include-user-header.php'); ?>
<div id="wrapper">
<?php 
include ("help-nav.php"); ?>
<div class="push"></div>
<h3><?php echo $title?></h3>
<div class="push"></div>
