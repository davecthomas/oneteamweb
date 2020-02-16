<?php
include_once('header-minimal.php'); ?>
<body class="<?php echo dojostyle?>">
<?php include('include-user-header.php'); ?>
<?php includeDojo();?>
<div id="wrapper">
<?php
// This is a friendly reminder that if (we are running on the staging server that we should be cautious
// with any writes to the DB.
if (isStagingServer() ) {?>
<div id="staging">
Beta Test:<br/><?php echo appname . " version " . appversion?><br /><a href="http://<?php echo stagingserveraddr?>/1team">Staging server</a><br /> using WWW production DB.
</div>
<?php
} ?>
<div class="navtop">
<ul id="nav">
<?php
	$userid_nav = 0;

	if (((isset($_REQUEST['id'])) && ($_REQUEST["id"] > User::UserID_Undefined )) && ((!isset($_REQUEST["whomode"])) || ((isset($_REQUEST["whomode"])) && ($_REQUEST["whomode"] == "user")))) {
		$userid_nav = $_REQUEST["id"];
	}

	// Try to get the teaminfo
	if (!isUser($session, Role_ApplicationAdmin ))  {
		$teaminfo = getTeamInfo( $session["teamid"]);
	} elseif ((isset($_REQUEST['teamid'])) && ($_REQUEST["teamid"] != TeamID_Undefined )){
		$teaminfo = getTeamInfo( $session["teamid"]);
	} else {
		$teaminfo = RC_TeamID_Invalid;
	}

	// App Admin Menus
	if (isUser($session, Role_ApplicationAdmin )) {
		if (isset($_REQUEST['teamid'])) $teamid = $_REQUEST["teamid"];?>
<li><a href="home.php<?php buildRequiredParams($session) ?>">Home</a> </li>
<li><a href="#"><?php echo $teamterms["termmember"]?></a>
<ul>
<li><a href="change-password-form.php<?php buildRequiredParams($session) ?>">Change your password...</a></li>
<li><a href="reset-password-form.php<?php buildRequiredParams($session) ?>">Reset <?php echo $teamterms["termmember"]?>&nbsp;password</a></li>
<li><a class="action" href="new-user-form.php<?php buildRequiredParams($session) ?>&roleid=<?php echo Role_Member?><?php if (isset($teamid)) echo "&teamid=" . $teamid?>">New</a></li>
</ul>
<li><a href="#"><?php echo $teamterms["termcoach"]?></a>
<ul>
  <li><a class="action" href="new-user-form.php<?php buildRequiredParams($session) ?>&roleid=<?php echo Role_Coach?><?php if (isset($teamid)) echo "&teamid=" . $teamid?>">New</a></li>
</ul>
</li>
<li><a href="#"><?php echo $teamterms["termteam"]?></a>
<ul>
  <li><a href="new-team-form.php<?php buildRequiredParams($session) ?>">New <?php echo $teamterms["termteam"]?>... </a></li>
  <li><a href="teams-roster.php<?php buildRequiredParams($session) ?>"><?php echo $teamterms["termteam"]?>s Roster</a></li>
  <li><a href="email-team-form.php<?php buildRequiredParams($session) ?>">Email <?php echo $teamterms["termteam"]?></a></li>
  <li><a href="new-team-payment-form.php<?php buildRequiredParams($session) ?>">New <?php echo $teamterms["termteam"]?> payment</a></li>
  <li><a href="export-roster-csv.php<?php buildRequiredParams($session) ?>">Export Roster</a></li>
<li><a href="delete-team-form.php<?php buildRequiredParams($session) ?>">Delete team...</a></li>
</ul>
</li>
<li><a href="#">Connect</a>
<ul>
<li><a href="email-team-form.php<?php buildRequiredParams($session) ?>">Email <?php echo $teamterms["termteam"]?></a></li>
<li><a href="email-team-form.php<?php buildRequiredParams($session) ?>&sms=1">Text message <?php echo $teamterms["termteam"]?></a></li>
</ul>
<li><a href="#">Administer</a>
<ul>
<li><a href="admin/admin.php<?php buildRequiredParams($session) ?>&cmd=sessclean">Clean up stale sessions</a></li>
</ul>
<?php






	// Team Admin Menus
	} elseif (isUser($session, Role_TeamAdmin )) {
		$isTeamAttendanceConsole = (bool) (AttendanceConsole::isAttendanceConsole($session));?>
<li><a href="home.php<?php buildRequiredParams($session) ?>">Home</a> </li>
<li><a href="#"><?php echo $teamterms["termmember"]?></a>
<ul>
<li><a href="change-password-form.php<?php buildRequiredParams($session) ?>">Change your password...</a></li>
<?php
		if (isTeamUsingLevels($session, $session["teamid"])) { ?>
<li><a href="promote-member-form.php<?php buildRequiredParams($session) ?>&teamid=<?php
			echo $session["teamid"];
			if ((isset($userid_nav)) && ($userid_nav > 0 ))  {
				echo "&id=" . $userid_nav;
			}?>">Promote...</a></li>
<?php
			if ((isset($userid_nav)) && ($userid_nav > 0 ))  {?>
<li><a href="include-promotions.php<?php buildRequiredParams($session) ?>&whomode=user&id=<?php echo $userid_nav?>&teamid=<?php echo $session["teamid"]?>&pagemode=standalone">Promotions history</a></li>
<?php
			}
		}

		if ((isset($userid_nav)) && ($userid_nav > 0 ))  {?>
<li><a href="reset-password-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>&amp;id=<?php echo $userid_nav?>">Reset <?php echo $teamterms["termmember"]?>&nbsp;password</a></li>
<?php
		} else {?>
<li><a href="reset-password-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>">Reset <?php echo $teamterms["termmember"]?>&nbsp;password...</a></li>
<?php 	} ?>
<li><a class="action" href="new-user-form.php<?php buildRequiredParams($session) ?>&roleid=<?php echo Role_Member?>">New <?php echo $teamterms["termmember"]?>...</a></li>
<li><a href="delete-user-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"];
		if ((isset($userid_nav)) && ($userid_nav > 0 ))  {
			echo "&id=" . $userid_nav;
		}?>">Delete <?php echo $teamterms["termmember"]?>...</a></li>
</ul>
</li>
<li><a href="#">Orders</a>
<ul>
<li><a href="new-order-form.php<?php buildRequiredParams($session) ?>">New Order...</a></li>
<li><a href="manage-orders-form.php<?php buildRequiredParams($session) ?>&whomode=team">Manage Orders</a></li>
<?php
		if ((isset($userid_nav)) && ($userid_nav > 0 ))  {
?>
<li><a href="payment-history.php<?php buildRequiredParams($session) ?>&pagemode=standalone&teamid=<?php echo $session["teamid"]?>&id=<?php echo $userid_nav?>">Payment history</a></li>
<?php
		}
?>
<li><a href="payment-history.php<?php buildRequiredParams($session) ?>&id=<?php echo $session["teamid"]?>&whomode=team"><?php echo $teamterms["termteam"]?> Payment history</a></li>
<li><a href="list-late-members-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $session["teamid"]?>">List Who Hasn't Purchased</a></li>
<li><a href="epayment-reconcile-form.php<?php buildRequiredParams($session) ?>&whomode=team">Payment Reconciler</a></li>
<li><a href="edit-redemptioncard-form.php<?php buildRequiredParams($session) ?>">New Redemption Card...</a></li>
<li><a href="manage-redemptioncards-form.php<?php buildRequiredParams($session) ?>&whomode=team">Manage Redemption Cards</a></li>
</ul>
<li><a href="#">Attendance</a>
<ul>
<li><a href="log-attendance-form.php<?php buildRequiredParams($session) ?>&teamid=<?php
		echo $session["teamid"];
		if ((isset($userid_nav)) && ($userid_nav > 0 ))  {
			echo "&id=" . $userid_nav;
		}?>">Log attendance...</a></li>
<?php
		if ((isset($userid_nav)) && ($userid_nav > 0 ))  { ?>
<li><a href="include-attendance-calendar.php<?php buildRequiredParams($session) ?>&pagemode=standalone&whomode=user&id=<?php echo $userid_nav?>"><?php echo $teamterms["termmember"]?> Attendance calendar</a></li>
<li><a href="include-attendance-table.php<?php buildRequiredParams($session) ?>&whomode=user&pagemode=standalone&id=<?php echo $userid_nav?>"><?php echo $teamterms["termmember"]?> Attendance report</a></li>
<li><a href="include-attendance-trend.php<?php buildRequiredParams($session) ?>&whomode=user&id=<?php echo $userid_nav?>&pagemode=standalone"><?php echo $teamterms["termmember"]?> Attendance trendline</a></li>
<?php
		}?>

<li><a href="include-attendance-calendar.php<?php buildRequiredParams($session) ?>&pagemode=standalone&whomode=team&id=<?php echo $session["teamid"]?>"><?php echo $teamterms["termteam"]?> Attendance calendar</a></li>
<li><a href="include-attendance-table.php<?php buildRequiredParams($session) ?>&whomode=team&pagemode=standalone&id=<?php echo $session["teamid"]?>"><?php echo $teamterms["termteam"]?> Attendance report</a></li>
<li><a href="include-attendance-trend.php<?php buildRequiredParams($session) ?>&whomode=team&id=<?php echo $session["teamid"]?>&pagemode=standalone"><?php echo $teamterms["termteam"]?> Attendance trendline</a></li>
<li><a href="attendance-on-date.php<?php buildRequiredParams($session) ?>"><?php echo $teamterms["termteam"]?> Attendance on Date</a></li>
<?php
		// Only enable ID Card Scanning from the Admin IP address stored for this team
		// Only enable ID Card Generation from the Admin IP address stored for this team
		if ($isTeamAttendanceConsole) {
?>
<li><a href="member-attendance-roster-form.php<?php buildRequiredParams($session) ?>">Attendance Roll Call...</a></li>
<li><a href="scan-event-form.php<?php buildRequiredParams($session) ?>">Start Attendance Scanning...</a></li>
<li><a href="generate-idcard-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>">Generate ID Cards...</a></li>
<?php
		}
?>
</ul></li>
<li><a href="#"><?php echo $teamterms["termcoach"]?></a>
<ul>
<li><a class="action" href="new-user-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>&roleid=<?php echo Role_Coach?>">New <?php echo $teamterms["termcoach"]?>...</a></li>
<li><a href="user-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $teaminfo["coachid"]?>">Properties</a></li>
</ul>
</li>
<li><a href="#"><?php echo $teamterms["termteam"]?></a>
<ul>
<li><a href="team-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $session["teamid"]?>">Properties</a></li>
<li><a href="member-roster.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>&filter=status%3C%3E<?php echo UserAccountStatus_Inactive?>">Active Roster</a></li>
<li><a href="member-roster.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>&sort=firstname">Full Roster</a></li>
<li><a href="include-promotions.php<?php buildRequiredParams($session) ?>&whomode=team&pagemode=standalone&teamid=<?php echo $session["teamid"]?>"><?php echo $teamterms["termteam"]?> Promotion history</a></li>
<li><a href="include-enrollment.php<?php buildRequiredParams($session) ?>&mode=team&teamid=<?php echo $session["teamid"]?>&startdate=<?php echo $teaminfo["startdate"]?>"><?php echo $teamterms["termteam"]?> Enrollment history</a></li>
<li><a href="import-roster-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>">Import Roster</a></li>
<li><a href="export-roster-csv.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>">Export Roster</a></li>
<li><a href="team-payment-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>">Make a payment to 1TeamWeb...</a></li>
</ul>
</li>
<li><a href="#">Connect</a>
<ul>
<li><a href="email-team-form.php<?php buildRequiredParams($session) ?>">Email <?php echo $teamterms["termteam"]?></a></li>
<li><a href="email-team-form.php<?php buildRequiredParams($session) ?>&sms=1">Text message <?php echo $teamterms["termteam"]?></a></li>
</ul>
<li><a href="#">Customize</a>
<ul>
<li><a href="manage-programs-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>">Programs</a></li>
<li><a href="manage-attendance-consoles-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>">Attendance Consoles</a></li>
<li><a href="manage-skus-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>">SKUs</a></li>
<li><a href="manage-events-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>">Meeting Types</a></li>
<li><a href="manage-levels-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>">Levels</a></li>
<li><a href="manage-payment-types-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>">Payment methods</a></li>
<li><a href="manage-custom-fields.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>">Custom fields</a></li>
<li><a href="manage-custom-lists-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>">Lists for custom fields</a></li>
</ul>
</li>
<?php






	//Coach Menus
	} elseif (isUser($session, Role_Coach )) {
		$isTeamAttendanceConsole = (bool) (AttendanceConsole::isAttendanceConsole($session));?>
<li><a href="home.php<?php buildRequiredParams($session) ?>">Home</a> </li>
<li><a href="#"><?php echo $teamterms["termmember"]?></a>
<ul>
<li><a href="change-password-form.php<?php buildRequiredParams($session) ?>">Change your password...</a></li>
</ul>
</li>
<?php
		// Only enable ID Card Scanning from the Admin IP address stored for this team
		if ($isTeamAttendanceConsole) {
?>
<li><a href="#">Attendance</a>
<ul>
<li><a href="log-attendance-form.php<?php buildRequiredParams($session) ?>">Log attendance...</a></li>
<li><a href="scan-event-form.php<?php buildRequiredParams($session) ?>">Start Attendance Scanning...</a></li>
</ul>
</li>
<?php
		}
?>
<li><a href="#"><?php echo $teamterms["termteam"]?></a>
<ul>
<li><a href="team-props-form.php<?php buildRequiredParams($session) ?>">Properties</a></li>
<li><a href="member-roster.php<?php buildRequiredParams($session) ?>&filter=status%3C%3E<?php echo UserAccountStatus_Inactive?>">Active Roster</a></li>
</ul>
</li>
<li><a href="#">Connect</a>
<ul>
<li><a href="email-team-form.php<?php buildRequiredParams($session) ?>">Email <?php echo $teamterms["termteam"]?></a></li>
<li><a href="email-team-form.php<?php buildRequiredParams($session) ?>&sms=1">Text message <?php echo $teamterms["termteam"]?></a></li>
</ul>
<?php








	// Member Menus
	} elseif (isUser($session, Role_Member )) {?>
<li><a href="home.php<?php buildRequiredParams($session) ?>">Home</a> </li>
<li><a href="#"><?php echo $teamterms["termmember"]?></a>
<ul>
<?php
		$teaminfo = getTeamInfo( $session["teamid"]);
		if (utilIsUserBillable($session) ) {?>
<li><a href="<?php echo $teaminfo["paymenturl"]?>" target="_blank">Make a payment</a></li>
<li><a href="payment-history.php<?php buildRequiredParams($session) ?>&pagemode=standalone&teamid=<?php echo $session["teamid"]?>&id=<?php echo $session["userid"]?>">Payment history</a></li>
<?php 	} ?>
<li><a href="include-attendance-table.php<?php buildRequiredParams($session) ?>&mode=user&teamid=<?php echo $session["teamid"]?>&pagemode=standalone&id=<?php echo $session["userid"]?>">Attendance report</a></li>
<li><a href="include-attendance-calendar.php<?php buildRequiredParams($session) ?>&pagemode=standalone&whomode=user&id=<?php echo $session["userid"]?>">Attendance calendar</a></li>
<li><a href="include-attendance-trend.php<?php buildRequiredParams($session) ?>&whomode=user&id=<?php echo $session["userid"]?>&pagemode=standalone">Attendance trendline</a></li>
<?php 	if (isTeamUsingLevels($session, $session["teamid"])) { ?>
<li><a href="include-promotions.php<?php buildRequiredParams($session) ?>&mode=user&id=<?php echo $session["userid"]?>&teamid=<?php echo $session["teamid"]?>&pagemode=standalone">Promotions history</a></li>
<?php 	}?>
<li><a href="change-password-form.php<?php buildRequiredParams($session) ?>">Change your password...</a></li>
</ul>
<li><a href="#"><?php echo $teamterms["termteam"]?></a>
<ul>
<li><a href="team-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $session["teamid"]?>">Properties</a></li>
</ul>
</li>
<li><a href="#"><?php echo $teamterms["termcoach"]?></a>
<ul>
<li><a href="user-props-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $teaminfo["coachid"]?>">Properties</a></li>
</ul>
</li>
<?php
	}
	// All users get the help menu
?>
<li><a href="help/default.php<?php buildRequiredParams($session) ?>">Help Center</a></li>
</ul>
</div>
<div class="push"></div>
