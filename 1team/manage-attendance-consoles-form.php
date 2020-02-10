<?php  
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title = "Manage Attendance Consoles";
include('header.php');
 
echo "<h3>" . getTitle($session, $title) . "</h3>";

$teamid = NotFound;
$bError = false;
// teamid depends on who is calling
if (isUser($session, Role_TeamAdmin)){
	$teamid = getTeamID($session);
} else {
	if (isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	} else{
		$bError = true;
	}
}
?>
<h4><?php echo $title?> for <?php echo getTeamName($teamid, $dbconn);?></h4>
<div class="helpbox">
<div class="helpboxtitle"><h5><a class="linkopacity" href="javascript:togglevis('overview')">What are Attendance Consoles?<img src="/1team/img/a_expand.gif" id="overview_img" border="0" alt="expand region"></a></h5></div>
<div class="hideit" id="overview">
<div class="helpboxtext">
<p><?php echo AttendanceConsole::getHelp();?><br/>If you wish to add the computer or mobile device as an authorized attendance console, add a name below.
The IP address of your system has been entered for convenience. If your IP address changes frequently, or you are using a public computer that you do not control
access to, it is recommended you do <i>not</i> use this system for attendance logging.</p>
</div></div></div>
<?php
	$acs = new AttendanceConsoles($session, $teamid);
	$rowCount = 0;
	$attendanceconsoles = $acs->getAttendanceConsoles();
	$loopMax = $acs->getNumAttendanceConsoles();?>
<table width="65%">
<thead>
<tr>
<th style="text-align:left" width="60%">Name</th>
<th style="text-align:left" width="30%">IP Address</th>
<th style="text-align:left" width="10%">Actions</th>
</tr>
</thead>
</table>

<?php
	while ($rowCount < $loopMax) { 
		$attendanceconsole = $attendanceconsoles[$rowCount]; ?>
				
<span id="attendanceconsole<?php echo $rowCount?>" style="display:none" class="attendanceconsoleorderitem"><?php echo $attendanceconsole->getID()?></span>
<table width="65%"><tr class="even">
<td width="60%"><?php echo $attendanceconsole->getName()?></td>
<td width="30%"><?php echo $attendanceconsole->getIP()?></td>
<td width="10%">
<a href="delete-attendance-console.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $attendanceconsole->getID()?>" title="Delete"><img src="img/delete.gif" alt="Delete" border="0"></a>&nbsp;
<a href="edit-attendance-console-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&id=<?php echo $attendanceconsole->getID()?>" title="Edit"><img src="img/edit.gif" alt="Edit" border="0"></a>
</td>
</tr>
</table>
<?php 
			$rowCount ++;
	}	?>
<table width="65%">
<form name="newattendanceconsole" action="/1team/new-attendanceconsole.php" method="post">
<?php buildRequiredPostFields($session) ?>
<tr class="odd"><td colspan="3"><span class="bigstrong">Add new attendance console</span></td></tr>
<tr class="even">
<td width="60%"><input type="text" name="name" size="80" maxlength="80" value="New attendance console name"></td>
<td width="30%"><input type="text" name="ip" size="<?php echo AttendanceConsole::MaxLenIPAddress;?>" maxlength="<?php echo AttendanceConsole::MaxLenIPAddress;?>" value="<?php echo $_SERVER["REMOTE_ADDR"];?>"></td>
<td width="10%"><a href="#" title="Add attendance console" onClick="newattendanceconsole.submit()"><img src="img/add.gif" alt="Add attendanceconsole" border="0"></a></td>
</tr>
</table>
</form>
<?php 
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "The attendance console was not saved successfully.", "");
} else if (isset($_GET["done"])){
	showMessage("Success", "The attendance console was saved successfully.");
} 
// Start footer section
include('footer.php'); ?>
</body>
</html>