<?php
include_once("utils.php");
$bError = false;
$err = "";
// Manage who mode: user or team
$whomode = "user";
if (isset($_REQUEST["whomode"])) {
	$whomode = $_REQUEST["whomode"];
}

if (isset($_REQUEST["programid"])) {
	$programid = $_REQUEST["programid"];
	if ($programid == Program_Undefined) $sqlprogram = "";
	else $sqlprogram = " AND programid = " .$programid;
} else {
	// Default triggers all programs
	$programid = Program_Undefined;
	$sqlprogram = "";
}

// Manage page mode: embedded or standalone
if (isset($_REQUEST["pagemode"])) {
	$pagemode = getCleanInput($_REQUEST["pagemode"]);
} else {
	$pagemode = "standalone";
}

// Standalone: init the session
if ($pagemode == "standalone") {
	ob_start();
	$title= " Attendance History " ;
	include('header.php');
	$expandimg = "collapse";
	$expandclass = "showit";
	// id is required and you must be able to admin this id
	if (isset($_REQUEST["id"])){
		if ($whomode == "user")	{
			$userid = $_REQUEST["id"];
			$id = $userid;
			if (!canIAdministerThisUser( $session, $userid)) redirectToLogin();
		}
		else {
			if (isUser($session, Role_TeamAdmin)) $teamid = $session["teamid"];
			else $teamid = $_REQUEST["id"];
			$id = $teamid;
		}
	} else {
		redirect( $_SERVER['HTTP_REFERER'] . "&err=1");
	}
	// teamid depends on who is calling
	if (!isUser($session, Role_ApplicationAdmin)) {
		if (isset($session["teamid"])) {
			$teamid = $session["teamid"];
		} else {
			$bError = true;
		}
	} else {
		if (isset($_REQUEST["teamid"])) {
			$teamid = $_REQUEST["teamid"];
		} else {
			$bError = true;
		}
	}
?>

<h4>Filter Attendance Results</h4>
<div class="indented-group-noborder">
<script type="text/javascript">
function updateProgramID() {
	document.forms['selectprogramform'].submit();
}
</script>
<form name="selectprogramform" action="/1team/include-attendance-table.php" method="post">
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<input type="hidden" name="whomode" value="<?php echo $whomode ?>"/>
<input type="hidden" name="id" value="<?php echo $id ?>"/>
<?php buildRequiredPostFields($session) ?>
<?php

	// GEt payment methods for this team
	$strSQL = "SELECT * FROM programs WHERE teamid = ?";
	$dbconn = getConnectionFromSession($session);
	$programResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));
	$rowCountP = count( $programResults);

	// Display programs for this team
	if ($rowCountP > 0) {
		$countRowsP = 0; ?>
<table class="noborders">
<tr><td class="bold">Display attendance for program</td><td>
<select name="programid" onchange="updateProgramID();">
<option value="<?php echo Program_Undefined?>" <?php if ($programid == Program_Undefined) echo " selected"?>>All programs</option>
<?php
		while ($countRowsP < $rowCountP) {
			echo '<option value="';
			echo $programResults[$countRowsP]["id"];
			echo '"';
			if ($programid == $programResults[$countRowsP]["id"]) echo " selected";
			echo ">";
			echo $programResults[$countRowsP]["name"];
			echo "</option>\n";
			$countRowsP ++;
		} ?>
</select>
</td></tr>
</table>
</form>
<?php
	} else {
		echo '<p>There are no programs defined for the team ' . $teaminfo["teamname"] . '. <a href="/1team/manage-programs-form.php?' . returnRequiredParams($session) . '&teamid=' . $teamid .'">Define programs</a>.';
	}





// Embedded mode: userid is required for user mode. Teamid is required for team mode
} else { ?>
<link rel="stylesheet" type="text/css" href="1team.css"/>
<?php
	$session = getSession();

	$expandimg = "expand";
	$expandclass = "hideit";

	// id is required and you must be able to admin this id
	if ($pagemode == "standalone") {
		if (isset($_REQUEST["id"])){
			if ($whomode == "user")	{
				$userid = $_REQUEST["id"];
				if (!canIAdministerThisUser( $session, $userid)) redirectToLogin();
			}
			else {
				if (isUser($session, Role_TeamAdmin)) $teamid = $session["teamid"];
				else $teamid = $_REQUEST["id"];
			}
		} else {
			redirectToLogin();
		}
	} else {
		if (isset($userid)){
			if ($whomode == "user")	{
				if (!canIAdministerThisUser( $session, $userid)) redirectToLogin();
			}
			else {
				if (isUser($session, Role_TeamAdmin)) $teamid = $session["teamid"];
			}
		} else {
			$userid = getSessionUserID($session);
		}

	}
}

// Team mode must be admin
if ( $whomode == "team") {
	redirectToLoginIfNotAdmin( $session);
} else {
	if (! isset($userid)){
		redirectToLogin();
	}
}



// User mode: make sure they can adminster this user
if ($whomode == "user") {
	$objid = $userid;
	$objname = getUserName($userid, $dbconn);
	$sqlwhere = "attendance.memberid = " . $userid;
} else {
	$sqlwhere = "attendance.teamid = " . $teamid;
	$objid = $teamid;
	$objname = getTeamName($teamid, $dbconn);
}
$urlencodedname = urlencode($objname);

// Get Event Date
$EventDate = "";
if (isset($_REQUEST["EventDate"])) {
	$EventDate = new DateTime(getCleanInput($_REQUEST["EventDate"]));
} else {
	// Default event date
	$EventDate = new DateTime(date("01-m-Y"));
}
$CurMonth = $EventDate->format("m");
$CurMonthName =$EventDate->format("F");
$CurYear = $EventDate->format("Y");
$FirstDayDate = new DateTime($EventDate->format("01-m-Y"));
$CurDay = $FirstDayDate->format("d-m-Y");

$lastdaydatereq = "";
if (isset($_REQUEST["EventDateEnd"])) {
	$lastdaydatereq = getCleanInput($_REQUEST["EventDateEnd"]);
	if ($lastdaydatereq == "today") {
		$lastdaydate = new DateTime(date("d-m-Y"));
	} else {
		$lastdaydate = $lastdaydatereq;
	}
} else {
	$lastdaydate = new DateTime($EventDate->format("d-m-Y"));
	$lastdaydate->modify("+1 month");;
	$lastdaydate->modify("-1 day");;
}

// Display previous and next links at the top of the table
// Only display prev/next for app admin, since this data spans teams
if ((isUser( $session, Role_ApplicationAdmin)) && ($whomode == "user")) { ?>
<p></p>
<div class="navtop">
<ul id="nav">
<li><a href="<?php if ($userid > 1 ) echo "user-props-form.php?id=" . ($userid-1) . buildRequiredParamsConcat($session)?>"><img src="img/a_previous.gif" border="0" alt="previous">Previous member</a></li>
<li><a class="linkopacity" href="user-props-form.php?id=<?php echo($userid+1) . buildRequiredParamsConcat($session)?>">Next member<img src="img/a_next.gif" border="0" alt="next"></a></li>
</ul>
</div><p></p>
<?php
}?>
<h5>Attendance for
<?php
if ($whomode == "user") { ?>
<a target="_top" href="user-props-form.php<?php buildRequiredParams($session)?>&id=<?php echo $objid ?>" target="_top"><?php echo $objname?></a>
<?php } else { ?>
<a target="_top" href="team-props-form.php<?php buildRequiredParams($session)?>&id=<?php echo $teamid ?>" target="_top"><?php echo $objname?></a>
<?php } ?>
from <?php echo $EventDate->format("F j, Y") ?> to <?php echo $lastdaydate->format("F j, Y") ?></h5>
<table width="65%">
<thead class="head">
<?php
	// Prep for prev/next links
	$EventDateNextYear = new DateTime($EventDate->format("d-m-Y"));
	$EventDatePrevYear = new DateTime($EventDate->format("d-m-Y"));
	$EventDateNextMonth = new DateTime($EventDate->format("d-m-Y"));
	$EventDatePrevMonth = new DateTime($EventDate->format("d-m-Y"));
	$EventDateNextYear->modify("+1 year");
	$EventDatePrevYear->modify("-1 year");
	$EventDateNextMonth->modify("+1 month");
	$EventDatePrevMonth->modify("-1 month");

	// Don't display the month/year if input has specified start or end point
	if (strlen($lastdaydatereq) == 0) { ?>
<tr>
<th align="left"><a target="_top" title="previous year" href="include-attendance-table.php<?php buildRequiredParams($session) ?>&EventDate=<?php echo urlencode($EventDatePrevYear->format("d-m-Y"))?>&id=<?php echo $objid?>&name=<?php echo $urlencodedname?>&whomode=<?php echo $whomode?>&pagemode=standalone"><img align="left" src="img/a_previous.gif"></a></th>
<?php
		if ($whomode == "team") {
?>
<th colspan="2" align="center">
<?php
		} else {
?>
<th align="center" colspan="1">
<?php
		}?>
<?php echo $CurYear?></th>
<th align="right"><a target="_top" title="next year" href="include-attendance-table.php<?php buildRequiredParams($session) ?>&EventDate=<?php echo urlencode($EventDateNextYear->format("d-m-Y"))?>&id=<?php echo $objid?>&name=<?php echo $urlencodedname?>&whomode=<?php echo $whomode?>&pagemode=standalone"><img align="right" src="img/a_next.gif"></a></th>
<?php
		if (isAnyAdminLoggedIn($session)) { ?>
<th rowspan="3" width="30" valign="bottom">Actions</th></tr>
<?php
		}  ?>
</tr>
<tr>
<th align="left"><a target="_top" title="previous month" href="include-attendance-table.php<?php buildRequiredParams($session) ?>&EventDate=<?php echo urlencode($EventDatePrevMonth->format("d-m-Y"))?>&id=<?php echo $objid?>&name=<?php echo $urlencodedname?>&whomode=<?php echo $whomode?>&pagemode=standalone"><img align="left" src="img/a_previous.gif"></a></th>
<?php
		if ($whomode == "team") {
?>
<th colspan="2" style="text-align:center" >
<?php
		} else {
?>
<th style="text-align:center;"  colspan="1" >
<?php
		}
?>
<?php echo $CurMonthName?></th>
<th style="text-align:right" ><a target="_top" title="next month" href="include-attendance-table.php<?php buildRequiredParams($session) ?>&EventDate=<?php echo urlencode($EventDateNextMonth->format("d-m-Y"))?>&id=<?php echo $objid?>&name=<?php echo $urlencodedname?>&whomode=<?php echo $whomode?>&pagemode=standalone"><img align="right" src="img/a_next.gif"></a></th>
</tr>
<?php
	// end if event dates not entered
	}
	// Prepare to count rows for totals at the bottom of table
	$rowCountAttendance = 0;
	// See if there are any events for this date range
	$strSQL = "SELECT COUNT(*) FROM (events INNER JOIN (attendance INNER JOIN users ON attendance.memberid = users.id) on events.id = attendance.eventid) WHERE " . $sqlwhere . " AND (attendancedate >= ? and attendancedate < ?)" . $sqlprogram . " ;";

	$rowCount = executeQueryFetchColumn($dbconn, $strSQL, $bError, array($EventDate->format("Y-m-d"), $lastdaydate->format("Y-m-d")));
	if ($rowCount > 0) { ?>
<tr>
<th style="text-align:left">Date</th>
<th style="text-align:left" >Event</th>
<th style="text-align:left" >Location</th>
<?php
		if ($whomode == "team") {
?>
<th>Member</th>
<?php
		}

		// Display the action column header here since we may have skipped it above
		if ((isset($_REQUEST["EventDateEnd"])) && ($_REQUEST["EventDateEnd"] != "")) {
			if (isAnyAdminLoggedIn($session)) { ?>
<th width="30">Actions</th></tr>
<?php
			}
		}
	}
	$strSQL = "SELECT attendance.id as attendanceid, attendance.memberid as attendancememberid, attendance.teamid as attendanceteamid, attendance.*, events.*, users.* FROM (events INNER JOIN (attendance INNER JOIN users ON attendance.memberid = users.id) on events.id = attendance.eventid) WHERE " . $sqlwhere . " AND (attendancedate >= ? and attendancedate < ?) " . $sqlprogram . " ORDER BY attendance.attendancedate DESC;";
	$attendance_records = executeQuery($dbconn, $strSQL, $bError, array($EventDate->format("Y-m-d"), $lastdaydate->format("Y-m-d")));

	foreach ($attendance_records as $row) {
?>
</thead>
<tbody>
<tr class="<?php if ((bool)( $rowCountAttendance % 2 )) echo("even"); else echo("odd") ?>">
<?php
		$rowCountAttendance ++;
		$attendancedate = new DateTime($row["attendancedate"]); ?>
<td width="18%">
<?php	// If admin, make the date a link so you can look at team attendance on that date
		if (isAnyAdminLoggedIn($session)) { ?>
<a title="View attendance detail on this date" href="attendance-on-date.php<?php buildRequiredParams($session)?>&date=<?php echo urlencode($attendancedate->format("Y-m-d"))?>&teamid=<?php echo $row["teamid"]?>">
		<?php
		}
		echo $attendancedate->format("m-d-Y");
		if (isAnyAdminLoggedIn($session)) { ?>
</a>
		<?php
		} ?>
</td>
<td><?php echo $row["name"]?></td>
<td><?php echo $row["location"]?></td>
<?php	if ($whomode == "team") {
			echo '<td><a target="_top" href="user-props-form.php?' . buildRequiredParamsConcat($session) . '&id=' . $row["attendancememberid"] . '">' . $row["firstname"] . ' ' . $row["lastname"] . '</a></td>';
		}
		if (isAnyAdminLoggedIn($session)) { ?>
<td>
<a href="delete-attendance.php<?php buildRequiredParams($session)?>&whomode=<?php echo $whomode?>&id=<?php echo $row["attendancememberid"]?>&attendanceid=<?php echo $row["attendanceid"]?>&date=<?php echo urlencode($attendancedate->format("d-m-Y"))?>" title="Delete"><img src="img/delete.gif" alt="Delete" border="0"></a>&nbsp;
<a href="edit-attendance-form.php<?php buildRequiredParams($session)?>&id=<?php echo $row["attendancememberid"]?>&amp;teamid=<?php echo $row["attendanceteamid"]?>&amp;attendanceid=<?php echo $row["attendanceid"]?>&amp;eventid=<?php echo $row["eventid"]?>&amp;date=<?php echo urlencode($attendancedate->format("d-m-Y"))?>" title="Edit" target="_top"><img src="img/edit.gif" alt="Edit" border="0"></a>
</td>
<?php 	} ?>
</tr>
<?php
	}	?>
<tbody>
</table>
<?php
// Display user total
if ($whomode == "user") {
?>
<p><a title="View <?php echo $teamterms["termuser"]?> details" target="_top" href="user-props-form.php<?php buildRequiredParams($session)?>&id=<?php echo $objid?>" target="_top"><?php echo $objname?></a>  attended <?php echo $rowCount?> event<?php
	if ($rowCount != 1) {
		echo("s");
	}
?>
&nbsp;from <?php echo $FirstDayDate->format("F j, Y")?> to <?php echo $lastdaydate->format("F j, Y") ?>.</p>
<?php
} else {
// Display team total
?>
<p><a title="View <?php echo $teamterms["termteam"]?> details" target="_top" href="team-props-form.php<?php buildRequiredParams($session)?>&teamid=<?php echo $objid?>" target="_top"><?php echo $objname?></a> had <?php echo $rowCount?> event<?php
	if ($rowCount != 1) {
		echo("s");
	} ?>
&nbsp;from <?php echo $FirstDayDate->format("F j, Y")?> to <?php echo $lastdaydate->format("F j, Y") ?>.</p>
<?php
}

if ($pagemode == "standalone" ) {
	// Start footer section
	include('footer.php'); ?>
</body>
</html>
<?php
	ob_end_flush();

} ?>
