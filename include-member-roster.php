<?php // requires a lot of variables defined to get here ok... ?>
<div class="showit" id="rosterinfo">
<div class="indented-group-noborder">
<?php		
// Build special get parm including the search string
$searchparm = "";
if ((isset($pagemode)) && ($pagemode == pagemodeSearch)){
	$searchparm = "&str=" . urlencode($strSearch);
}
$isAdmin = isAnyAdminLoggedIn($session);
?>
<table  class="memberlist">  
<thead class="head"><tr>
<?php
if (isUser( $session, Role_ApplicationAdmin)){ ?>
<th valign="top"><a href="<?php echo $referrer?><?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&sort=<?php echo sortModifier("firstname",$sortRequest)?><?php echo urlencode(", teamid")?>&filter=<?php echo $filterRequest?><?php echo $searchparm?>">Team</a></th>
<?php
} ?>
<th valign="top"><a href="<?php echo $referrer?><?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&sort=<?php echo sortModifier("firstname",$sortRequest)?><?php echo urlencode(", lastname")?>&filter=<?php echo $filterRequest?><?php echo $searchparm?>">First Name</a></th>
<th valign="top"><a href="<?php echo $referrer?><?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&sort=<?php echo sortModifier("lastname",$sortRequest)?><?php echo urlencode(", firstname")?>&filter=<?php echo $filterRequest?><?php echo $searchparm?>">Last Name</a></th>
<th valign="top"><a href="<?php echo $referrer?><?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&sort=<?php echo sortModifier("roleid",$sortRequest)?><?php echo urlencode(", firstname")?>&filter=<?php echo $filterRequest?><?php echo $searchparm?>">Role</a></th>
<?php
if ($isAdmin){ ?>
<th valign="top"><a href="<?php echo $referrer?><?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&sort=<?php echo sortModifier("status",$sortRequest)?><?php echo urlencode(", status")?>&filter=<?php echo $filterRequest?><?php echo $searchparm?>">Status</a></th>
<th valign="top"><a href="<?php echo $referrer?><?php buildRequiredParams($session) ?>&teamid=<?php echo $teamid?>&sort=<?php echo sortModifier("isbillable",$sortRequest)?><?php echo urlencode(", firstname")?>&filter=<?php echo $filterRequest?><?php echo $searchparm?>">Billable</a></th>
<?php
}
if (isset($pagemode)) {
	if ($pagemode == pagemodeAttendanceOnDate) { ?>
<th align="left">Event</th>
<th align="left">Location</th>
<?php	
	}
}
if ($isAdmin){ ?>
<th valign="top">Actions</th></tr>
<?php 
}?>
</thead>	
<tbody>
<?php
$rowCount = 0;
$numRows = count($results);
while ($rowCount < $numRows) { 
	$accountStatus = $results[$rowCount]["status"]; ?>
<tr class="<?php if ((bool)( $rowCount % 2 )) echo("even"); else echo("odd") ?>">
<?php
if (isUser( $session, Role_ApplicationAdmin)){ ?>
<td><?$results[$rowCount]["teamname"]?></td>
<?php
}?>
<td>
<?php
	if (is_url($results[$rowCount]["url"])){ 
		$bhasurl = true; 
?>
<span id="<?php echo 'u'.$rowCount?>" onmouseout="document.getElementById('dynamicimage').className = 'hideit'" onmouseover="setDynamicImage('<?php echo $results[$rowCount]["url"]?>', document.getElementById('<?php echo 'u'.$rowCount?>'), <?php echo dynamicimagediv_Height/2?>)">
<?php 
	} else { 
		$bhasurl = false; 
	} 	
	if ($isAdmin){ ?>
<a href="user-props-form.php?<?php 
		echo returnRequiredParams($session) . "&id=" . $results[$rowCount][ "userid"] . "&teamid=" . $teamid?>">
<?php
	} ?>
<span <?php echo subdueInactive($accountStatus)?>><?php echo $results[$rowCount][ "firstname"];?></span>
<?php
	if ($bhasurl){ ?>&nbsp;<img src="img/pix.gif" border="0"></span>
<?php 
	} 
	if ($isAdmin){ ?>
</a><?php
	} ?>
</td>
<td <?php echo subdueInactive($accountStatus)?>><?php echo $results[$rowCount][ "lastname"] ?></td>
<td <?php echo subdueInactive($accountStatus)?>><?php echo roleToStr($results[$rowCount][ "roleid"], $teamterms); ?></td>
<?php 
	if ($isAdmin){ ?>
<td <?php echo subdueInactive($accountStatus)?>><?php echo $aStatus[(int)$results[$rowCount][ "status"]+UserAccountStatus_ArrayOffset] ?></td>
<?php 
		$isUserBillable = BoolToStr((bool) $results[$rowCount][ "isbillable"]); ?>
<td <?php echo subdueInactive($accountStatus)?>><?php 	echo (string)($isUserBillable); ?></td>
<?php
	}
	if (isset($pagemode)) {
		if ($pagemode == pagemodeAttendanceOnDate) {
			echo "<td>" . $results[$rowCount]["name"] . "</td>";
			echo "<td>" . $results[$rowCount]["location"] . "</td>";
		}
	} 
	if ($isAdmin){ ?>
<td>
<?php 
		if ($pagemode == pagemodeAttendanceOnDate) { ?>
<a href="delete-attendance.php<?php buildRequiredParams($session)?>&id=<?php echo $results[$rowCount][ "userid"]?>&teamid=<?php echo $teamid ?>&attendanceid=<?php echo $results[$rowCount]["attendanceid"]?>" title="Delete attendance"><img src="img/delete.gif" alt="Delete attendance?>" border="0"></a>&nbsp;
<a href="edit-attendance-form.php<?php buildRequiredParams($session)?>&id=<?php echo $results[$rowCount][ "userid"]?>&amp;teamid=<?php echo $teamid ?>&attendanceid=<?php echo $results[$rowCount]["attendanceid"]?>&eventid=<?php echo $results[$rowCount]["eventid"]?>" title="Edit attendance">
<img src="img/edit.gif" alt="Edit attendance" border="0"></a>
<?php
		} else { ?>
<a href="delete-user-form.php?teamid=<?php echo $teamid . "&id=" . $results[$rowCount][ "id"] . buildRequiredParamsConcat($session) ?>" title="Delete <?php echo $results[$rowCount][ "firstname"]?>"><img src="img/delete.gif" alt="Delete <?php echo $results[$rowCount][ "firstname"]?>" border="0"></a>&nbsp;
<a href="user-props-form.php?id=<?php echo $results[$rowCount][ "userid"] . buildRequiredParamsConcat($session) . "&teamid=" . $teamid?>" title="Edit <?php echo $results[$rowCount][ "firstname"]?>">
<img src="img/edit.gif" alt="Edit <?php echo $results[$rowCount][ "firstname"]?>" border="0"></a>
<?php
		} 
	}?>
</td>
</tr>
<?php            
	$rowCount ++;
}
// Set up team name from session or query
if (!isUser( $session, Role_ApplicationAdmin)) {
	$teamname = getTeamName2($session["teamid"], $dbh);
} else {
	$teamname = "";
} 
?>
</tbody>
</table>
</div>
<p><?php echo $rowCount?> member<?php if ($rowCount != 1 )  echo("s") ?> of <?php echo $teamname ?> are listed in this view.</p>
</div>	
<div id="dynamicimage" class="hideit">
<img name="dynimg" src="" height="<?php echo dynamicimagediv_Height?>">
</div>