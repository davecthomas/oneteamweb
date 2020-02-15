<?php
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title= " Edit Promotion " ;
include('header.php'); ?>
<script type="text/javascript">
dojo.require("dijit.form.DateTextBox");
</script>

<?php
$bError = false;

if (!isUser( $session, Role_ApplicationAdmin )) {
	$teamid = $session["teamid"];
} else {
	if (isset($_GET["teamid"])) {
		$teamid = $_GET["teamid"];
	} else {
		redirect($_SERVER['HTTP_REFERER'] . "&err=t");
	}
}
// Promotion ID
if ( isset($_GET["id"])) {
	$promotionid = getCleanInput($_GET["id"]);
} else {
	$bError = true;
}

// Member ID of promotion holder
if ( isset($_GET["memberid"])) {
	$memberid = getCleanInput($_GET["memberid"]);
} else {
	$bError = true;
}

// This is so we can go back to the same pagemode we came from
if ( isset($_GET["pagemode"])) {
	$pagemode = getCleanInput($_GET["pagemode"]);
} else {
	$pagemode = "standalone";
}

$teamname = getTeamName($teamid, $dbconn);
$dbconn = getConnectionFromSession($session);
?>
<h3><?php echo $title . " for " . $teamname . " " . $teamterms["termmember"] . " " . getUserName($memberid, $dbconn);?></h3>
<?php
if (!$bError) {

	$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, promotions.id as promotionid, promotions.*, images.*, levels.* FROM levels, users, promotions LEFT OUTER JOIN images ON (images.objid = promotions.id AND images.type = ?) WHERE promotions.newlevel = levels.id AND promotions.memberid = users.id AND memberid = ? AND promotions.id = ? AND promotions.teamid = ?;";
	$promoResults = executeQuery($dbconn, $strSQL, $bError, array(ImageType_Promotion, $memberid, $promotionid, $teamid));

	if (count($promoResults) == 1) { ?>
<form action="/1team/edit-promotion.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="promotionid" value="<?php echo $promotionid ?>"/>
<input type="hidden" name="teamid" value="<?php echo $teamid ?>"/>
<input type="hidden" name="id" value="<?php echo $memberid?>" />
<p>
<table class="noborders">
<tr>
<td class="bold">Date</td>
<td><input type="text" name="date" id="date" value="<?php echo $promoResults[0]["promotiondate"]?>" dojoType="dijit.form.DateTextBox" required="true" promptMessage="mm/dd/yyyy"/>
</td>
<td class="bold">Promotion Level</td>
<td><select name="level">
<?php
	$strSQL = "SELECT * FROM levels WHERE teamid = ? ORDER BY programid, listorder;";
	$levels = executeQuery($dbconn, $strSQL, $bError, array($teamid));

	foreach ($levels as $rowlevel) {
		echo "<option value='";
		echo $rowlevel["id"];
		echo "'";
		if ( $promoResults[0]["newlevel"] == $rowlevel["id"] ) {
			echo(" selected");
		}
		echo ">";
		echo $rowlevel["name"];
		echo "</option>\n";
	} ?>
</select>
</td>
</tr>
</table>
<input type="submit" class="btn" value="Update" name="promote" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'include-promotions.php<?php buildRequiredParams($session) ?>&whomode=user&id=<?php echo $memberid?>&teamid=<?php echo $teamid?>&pagemode=standalone'"/>
</form>
<h4 class="expandable"><a class="linkopacity" href="javascript:togglevis('promoimg')">Picture<img src="img/a_collapse.gif" alt="collapse section" id="promoimg_img" border="0"></a></h4>
<div class="showit" id="promoimg">
<div class="group">
<div class="indented-group-noborder">
<?php
		// promo image
		if ((!is_null($promoResults[0]["filename"])) && ($promoResults[0]["filename"] != ImageID_Undefined)) { ?>
<img src="<?php echo $promoResults[0]["filename"]?>" id="" border=0">
<?php 	}
		// Conditionally display image
		if ((!is_null($promoResults[0]["url"])) && (strlen($promoResults[0]["url"]) > 0)) {?>
<img src="<?php echo $promoResults[0]["url"]?>" id="" border=0">
<?php
		} ?>
<p>Select an existing image by URL</p><form action="image-upload.php" method="post" enctype="multipart/form-data">
<!--<input type="file" name="image" name="image" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>-->
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid?>"/>
<input type="hidden" name="type" value="<?php echo ImageType_Promotion?>"/>
<input type="hidden" name="objid" value="<?php echo  $promotionid ?>"/>
<input type="hidden" name="teamname" value="<?php echo $teamname ?>" />
<p class="strong">Image URL&nbsp;<input type="text" value="<?php echo htmlspecialchars($promoResults[0]["url"] )?>" name="url"></p>
<input type="submit" value="Save Picture" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
</form>
<?php	// End image div ?>
</div></div></div>
<?php
	}
}
if ( $bError ) { ?>
<h4 class="usererror">Error: <?php echo errorStr?></h4>
<p><a href="javascript:void(0);" onclick="history.go(-1)">Back</a></p>
<?php
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
