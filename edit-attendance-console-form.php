<?php  
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Edit Attendance Console";
include('header.php');
$dbh = getDBH($session);
$bError = false;
$teamid = NotFound;
$err = "";
// teamid depends on who is calling
if (isUser($session, Role_TeamAdmin)){
	$teamid = getTeamID($session);
} else {
	if (isset($_REQUEST["teamid"])){
		$teamid = $_REQUEST["teamid"];
	} else{
		$bError = true;
		$err = "t";
	}
}

if (isset($_GET["id"])) {
	$attendanceconsoleid = $_GET["id"];
} else {
	$bError = true;
	$err = "i";
}

if (!$bError){
	$ac = new AttendanceConsole($session, $attendanceconsoleid);
	if ($ac->isValid()) { ?>
<h3><?php echo $title?> for <?php echo getTeamName2($teamid, $dbh)?></h3>
<div class="indented-group-noborder">
<form action="/1team/edit-attendance-console.php" method="post">
<input type="hidden" name="id" value="<?php echo $attendanceconsoleid ?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<?php buildRequiredPostFields($session) ?>
<table class="noborders">
<td class="strong">Attendance Console name</td><td><input type="text" name="name" size="60" maxlength="80" value="<?php echo $ac->getName()?>"></td></tr>
<td class="strong">Attendance Console IP Address</td><td><input type="text" name="ip" size="<?php echo AttendanceConsole::MaxLenIPAddress;?>" maxlength="<?php echo AttendanceConsole::MaxLenIPAddress;?>" value="<?php echo $ac->getIP();?>"></td></tr>
</table>
</div>
<input type="submit" value="Modify attendance console" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/></form>
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'manage-attendance-consoles-form.php?<?php echo returnRequiredParams($session) . "&teamid=" . $teamid?>'"/>
<?php 
	} else $bError = true;
}
if ($bError) {
	redirect("manage-attendance-consoles-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&err=1");
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
