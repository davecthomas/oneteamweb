<?php
include_once("utils.php");

if (isset($_GET["pagemode"])) {
	$pagemode = $_GET["pagemode"];
}
if ($pagemode == "standalone") {
	$title = "Promotions";
	include('header.php');
	$expandimg = "collapse";
	$expandclass = "showit";
	$session = getSession();
} else { ?>
<link rel="stylesheet" type="text/css" href="1team.css"/>
<script type="text/javascript" src="/1team/utils.js"></script>
<?php
	$session = getSession();
	$expandimg = "expand";
	$expandclass = "hideit";
}
$dbconn = getConnectionFromSession($session);
if (isset($_GET["id"])) {
	$userid = $_GET["id"];
	if (!isset($username)) {
		$username = getUserName( $userid, $dbconn);
	}
}
if (!isset($teamid)) {
	$teamid = $_GET["teamid"];
}
if ((isset($_GET["year"])) && (is_numeric($_GET["year"]))) {
	$paymentyear = $_GET["year"];
} else {
	$paymentyear = date("Y");
}
$sortRequest = "promotiondate DESC";
if (isset($_GET["sort"])) {
	$sortRequest = getCleanInput($_GET["sort"]);
	if ($sortRequest == "newlevel") {
		$sortRequest = $sortRequest . ", promotiondate";
	} else {
		$sortRequest = $sortRequest . ", promotiondate DESC";
	}
}

$bError = false;
if (isset($_GET["whomode"])) {
	$whomode = $_GET["whomode"];
}
if (!isset($whomode)) {
	$whomode = "user";
}

if ($whomode == "user"){
	if (canIViewThisUser( $session, $userid)) {
		$canView = true;
	} else {
		$canView = false;
		$bError = true;
		$errStr = Error;
	}
	if (canIAdministerThisUser( $session, $userid)) {
		$canAdmin = true;
	} else {
		$canAdmin = false;
	}
	if (isset($_GET["startdate"])) {
	}
}
if (($whomode == "team") && (!isAnyAdminLoggedIn($session))) {
	$bError = true;
	$errStr = Error;
}
if (!$bError ) {
	$dbconn = getConnectionFromSession($session);
	// User mode: make sure they can adminster this user
	if ($whomode == "user") {
		$objid = $userid;
		$objname = getUserName($userid, $dbconn);
		$sqlwhere = " promotions.memberid = ? and promotions.teamid = ?";
	} else {
		$sqlwhere = " promotions.teamid = ?";
		$objid = $teamid;
		$objname = getTeamName($teamid, $dbconn);
	}

	if ($pagemode == "standalone"){?>
<h4><?php echo $objname?> Promotion History</h4>
<?php
	}
	// get the promotion history
	$strSQL = "SELECT users.firstname, users.lastname, users.id as userid, promotions.id as promotionid, promotions.*, images.*, levels.* FROM levels, users, promotions LEFT OUTER JOIN images ON (images.objid = promotions.id AND images.type = ?) WHERE promotions.newlevel = levels.id AND promotions.memberid = users.id AND " . $sqlwhere . " ORDER BY " . $sortRequest . ";";
	if ($whomode == "user") 	{
		$promoResults = executeQuery($dbconn, $strSQL, $bError, array(ImageType_Promotion, $userid, $teamid));
	} else {
		$promoResults = executeQuery($dbconn, $strSQL, $bError, array(ImageType_Promotion, $teamid));
	}

	if (count($promoResults) > 0) {
		if ($whomode == "team") { ?>
<h5>Promotions for Active <?php echo $teamterms["termmember"]?>s</h5>
<?php
		} else {
			$objname = $promoResults[0]["firstname"] . " " . $promoResults[0]["lastname"];
			if ($pagemode == "standalone") { ?>
<h5>Promotions for <a href="user-props-form.php<?php buildRequiredParams($session)?>&id=<?php echo $promoResults[0]["userid"]?>" target="_top"><?php echo $objname?></a></h5>
<?php
			}
		} ?>
<table  class="memberlist">
<thead class="head">
<tr>
<?php 		if ($whomode == "team") { ?>
<th ><a href="include-promotions.php<?php buildRequiredParams($session)?>&pagemode=standalone&whomode=team&teamid=<?php echo $teamid?>&sort=<?php echo sortModifier("firstname",$sortRequest)?>"><?php echo $teamterms["termmember"]?></a></th>
<?php 		} else {
				// Create a blank column for picture icon since no name shows up in user mode ?>
<th></th>
<?php
			}?>
<th >Date</th>
<th ><a href="include-promotions.php<?php buildRequiredParams($session)?>&pagemode=standalone&whomode=team&teamid=<?php echo $teamid?>&sort=<?php echo sortModifier("newlevel",$sortRequest)?>">Level</a></th>
<?php
		// Show "actions" column to admins
		if ( !isUser($session, Role_Member)) {?>
<th>Actions</th></tr></thead>
<?php 	}

		$rowCountPromotions = 0;

		for ($i = 0; $i < count($promoResults); $i++) {
			if (is_url($promoResults[$i]["url"])) $bhasurl = true;
			else $bhasurl = false;
?>
<tr class="<?php if ((bool)( $rowCountPromotions % 2 )) echo("even"); else echo("odd") ?>">
<td>
<?php		$rowCountPromotions ++;

			// This part involves a bunch of logic to support display of the user/image for a promotion entry by building an anchor tag
			// This anchor that may be "empty" except for the javascript that supports mouseover for image support
			// Elsewhere, you can have this rollover support in a span object, but this won't work if the anchor is potentially empty,
			// So I put the rollover logic in the anchor and build an anchor that just consists of an pix.gif image for rollover ?>
<a
<?php

			if ($bhasurl){ ?>
id="<?php echo 'p'.$rowCountPromotions?>" onmouseout="document.getElementById('dynamicimage').className = 'hideit'" onmouseover="setDynamicImage('<?php echo $promoResults[$i]["url"]?>', document.getElementById('<?php echo 'p'.$rowCountPromotions?>'), <?php echo dynamicimagediv_Height/2?>)"
<?php
			}

			if ($whomode == "team") {?>
href="user-props-form.php?sessionkey=<?php echo $session["sessionkey"]?>&userid=<?php echo $session["userid"]?>&id=<?php echo $promoResults[$i]["userid"]?>" target="_top" id="<?php echo 'p'.$rowCountPromotions?>"><?php echo $promoResults[$i]["firstname"]?>&nbsp;<?php echo $promoResults[$i]["lastname"]?>
<?php		// This is for an otherwise empty column in user mode for the pix icon
			} else { ?>
href="#">
<?php
			}
			if ($bhasurl){ ?><img src="img/pix.gif" border="0">
<?php
			} ?>
</a></td>
<td>
<?php
			$promodateArray = explode("-",$promoResults[$i]["promotiondate"]);
			$promodatetime = new DateTime($promodateArray[2] . "-" . $promodateArray[1]. "-" . $promodateArray[0] );
			echo $promodatetime->format("m/d/Y") ;

			if ($rowCountPromotions == 1) {
				$datetimelastpromo = new DateTime($promodatetime->format("m/d/Y"));
				// For the first $row in the table (the most recent promotion), show how long ago that was
				$months = datediff("m", $promodatetime->format("m/d/Y"), date("m/d/Y"));
				if ($months < 1) {
					echo(": in the past month");
				} else {
					echo( ": " . $months . " month");
					if ($months > 1) {
						echo("s");
					}
					echo(" ago");
				}

			} ?>
</td>
<td>
<?php		echo $promoResults[$i]["name"];	?>
</td>
<?php
			if (isAnyAdminLoggedIn($session)) {?>
<td><a href="edit-promotion-form.php?<?php echo returnRequiredParams($session)?>&memberid=<?php echo $promoResults[$i]["userid"]?>&id=<?php echo $promoResults[$i]["promotionid"]?>&pagemode=<?php echo $pagemode?>" target="_top" title="Edit"><img src="img/edit.gif" alt="Edit" border="0"></a>&nbsp;
<a href="delete-promotion.php<?php buildRequiredParams($session) ?>&memberid=<?php echo $promoResults[$i]["userid"]?>&id=<?php echo $promoResults[$i]["promotionid"]?>&pagemode=<?php echo $pagemode?>" title="Delete"><img src="img/delete.gif" alt="Delete" border="0"></a>
</td>
<?php
			} ?>
</tr>
<?php
		}?>
<tr></tr>
</table>
<?php
	echo "<p>" . $rowCountPromotions . " promotion";
	if ($rowCountPromotions != 1) echo "s";
	echo " for " . $objname . ".";
	// We get here if there have been no promotions yet. We just need to get the start date so we can calculate how much time has passed.
	} else {
		// Query for the start date
		// Create a new query to get the promotion history based on the page mode
		if ($whomode == "user") {
			$strSQL = "SELECT users.firstname, users.lastname, users.startdate FROM users where users.id = ?";
			$startDateResults = executeQuery($dbconn, $strSQL, $bError, array($userid));
		} else {
			$strSQL = "SELECT teams.name, teams.startdate FROM teams where teams.id = ?";
			$startDateResults = executeQuery($dbconn, $strSQL, $bError, array($teamid));
		}
		if (count($startDateResults) > 0) $startDateResults = $startDateResults[0];
		if (array_key_exists("startdate", $startDateResults)) {
			// default last promotion date to start date in case user has never been promoted
			try{ 
				// $datetimelastpromo = new DateTime($startDateResults["startdate"]);
				$format = "Y-m-d";
				$datetimelastpromo = DateTime::createFromFormat($format,$startDateResults["startdate"]);

			} catch (Exception $e) {
				echo $e->getMessage();
				$datetimelastpromo = new DateTime();
			}
			if ($whomode == "user") {
				$objname = $startDateResults["firstname"] . " " . $startDateResults["lastname"];
			} else {
				$objname = $startDateResults["name"];
			}
		}

		if ($whomode == "team") { ?>
<h5>Promotions for Active <?php echo $teamterms["termmember"]?>s</h5>
<?php
			$objname = $teamterms["termteam"];
		} else { ?>
<h5>Promotions for <a href="user-props-form.php?sessionkey=<?php echo $session["sessionkey"]?>&userid=<?php echo $session["userid"]?>&id=<?php echo $userid?>" target="_top"><?php echo $objname?></a></h5>
<?php
		} ?>
<p>There have been no promotions since the start date of <?php echo $datetimelastpromo->format("F j, Y")?>.</p>
<?php
	}


	// Embed a link to the attendance history since the last promotion
	if ($whomode == "user") { ?>

<p><a href="include-attendance-table.php<?php echo buildRequiredParams($session) ?>&whomode=user&pagemode=standalone&id=<?php echo $objid?>&EventDate=<?php echo $datetimelastpromo->format("d-m-Y")?>&EventDateEnd=today" target="_top">Attendance since <?php echo $datetimelastpromo->format("F j, Y")?></a></p>
<?php
	// End whomode = user (attendance since promo)
	}

	if (isTeamAdminLoggedIn($session)){		?>
<p><a href="promote-member-form.php<?php buildRequiredParams($session) ?>&id=<?php echo $userid?>" target="_top"><img src="img/add.gif" alt="Promote" border="0">Promote</a></p>
<?php
	}
// not error
} ?>
<div id="dynamicimage" class="hideit">
<img name="dynimg" src="" height="<?php echo dynamicimagediv_Height?>">
</div>
<?php
// Error handling
if ($bError) { ?>
<h4 class="usererror">Error: <?php echo $errStr?></h4>
<p><a href="javascript:void(0);" onclick="history.go(-1)">Back</a></p>
<?php
}
if ($pagemode == "standalone"){
	include("footer.php");
} else {?>
</body>
</html>
<?php
} ?>
