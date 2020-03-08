<?php
include_once('utils.php');

// before we worry about session, worry about SSL on production system only
if(! isStagingServer() && $_SERVER['SERVER_PORT'] != 443) {
   header("HTTP/1.1 301 Moved Permanently");
   header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
}

// Assure we have the input we need, else send them to default.php
if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid_login = getUserID()) == RC_RequiredInputMissing)){
	redirect("default.php?rc=" . RC_RequiredInputMissing);
}
// Get $session array initialized
$session = startSession($sessionkey, $userid_login);
if (! isValidSession($session )){
	redirectToLogin();
}
// Scripts that include header can optionally set isappadminrequired to force redirection to login if not an app admin
if (((isset($isappadminrequired)) && ($isappadminrequired))){
	redirectToLoginIfNot( $session, Role_ApplicationAdmin);
}

// Scripts that include header can optionally set isadminrequired to force redirection to login if not an admin
if (((isset($isadminrequired)) && ($isadminrequired))){
	redirectToLoginIfNotAdmin( $session);
}

// Scripts that include header can optionally set isadminrequired to force redirection to login if not an admin
if (((isset($isadminorcoachrequired)) && ($isadminorcoachrequired))){
	redirectToLoginIfNotAdminOrCoach( $session);
}
// Get team terms gracefully handles the case where app admin has no team
$teamterms = getTeamTerms(getTeamID($session), getConnectionFromSession($session));?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta name="keywords" content="1TeamWeb Team Management Sports Membership Clubs Site"/>
<meta name="description" content="1 Team Web: Focus On Your Team"/>
<title><?php  echo getTitle($session, $title)?></title>
<link rel="stylesheet" type="text/css" href="/1team/1team.css"/>
<?php includeDojoStyle();?>
<script type="text/javascript" src="/1team/utils.js"></script>
<link rel="icon" type="image/png" href="/1team/img/1teamweb-logo-200.png" />
</head>
