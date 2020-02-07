<?php
ob_start(); 	// This caches non-header output, allowing us to redirect after header.php
// Only admins can execute this script. Header.php enforces this.
$isadminorcoachrequired = true;
$title= " Log Attendance " ;
include('header.php'); 
$bError = FALSE;
// teamid depends on who is calling 
if ( !isUser($session, Role_ApplicationAdmin)){
	if ( !isset($session["teamid"])){
		$bError = true;
		$err = "at.";
	} else {
		$teamid = $session["teamid"];
	}
} else {
	if ( isset($_REQUEST["teamid"])){
		$teamid = $_REQUEST["teamid"];
	} else {
		$bError = true;
		$err = "t";
	}
}

if (isset($_REQUEST["id"])){
	$userid = trim($_REQUEST["id"]);
} else {
	$bError = true;
	$err = "i";
} 

if (isset($_POST["eventid"])){
	$eventid = $_POST["eventid"];
} else {
	$bError = true;
	$err = "e";
} 
if (isset($_POST["date"])){
	$attendanceDate = $_POST["date"];
} else {
	$bError = true;
} 

if (!$bError) {
	// This include must have attendanceDate, eventid, userid and teamid defined and initialized first 
	$memberID = $userid;
	include("include-log-attendance.php");
} 
if ($bError) {
	redirect("log-attendance-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&id=" . $userid . "&err=" . $err);
} 	
ob_end_flush(); 