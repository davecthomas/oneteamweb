<?php 
$attendanceDate = date("m/d/Y");
$title = "Log Attendance on " . $attendanceDate; 

include('header.php');

$teamid = $session["teamid"];
$memberID = $session["userid"];
if (isset($_POST["eventid"])) {
	$eventid = $_POST["eventid"];
} else {
	$bError = true;
} 

// This include must have attendanceDate, eventid, memberid and teamid defined and initialized first 
include('include-log-attendance.php');

