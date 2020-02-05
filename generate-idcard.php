<?php
$title=" Generate ID Card";
$isadminrequired= true;
$bError = false;
include_once('header-minimal.php');
require("php-barcode/php-barcode.php");
define("rowsPerPage", 8);
define("numColumns", 3);
define("userList", 1);
?>
<body>
<div id="userbanner">
<a href="<?php echo $_SERVER['HTTP_REFERER']?>"><- Back</a>&nbsp;Signed in as <?php echo roleToStr($session["roleid"],$teamterms)?>&nbsp;<?php echo $session["fullname"]?>. Session time remaining:&nbsp;<?php echo getSessionTimeRemaining($session)?> <a href="/1team/logout.php<?php buildRequiredParams($session)?>">Sign out</a>
</div>
<div id="wrapper">
<div style="background-color:#FFFFFF">
<?php
// teamid depends on who is calling
if ( !isUser($session, Role_ApplicationAdmin)){
	if ( !isset($session["teamid"])){
		$bError = true;
		$err = "t";
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
$userid = User::UserID_Undefined;
if (isset($_REQUEST["id"])){
	$useridList = $_REQUEST["id"];
	if (is_array($useridList)){
		if ($useridList[0] > User::UserID_Undefined) {
		     $command = userList;
	     } else if ($useridList[0] < User::UserID_Undefined) {
			$command = $useridList[0];
  		} else {
			$bError = true;
			$err = "i";
		}
	} else {
	     $bError = true;
	     $err = "i";
	}
} else {
	$bError = true;
	$err = "i";
}

if (!$bError){
	$dbh = getDBH($session);
     if (strlen($session["teamimageurl"]) > 0) {
		$teamlogo = $session["teamimageurl"];
	} else {
	     $teamlogo = "/1team/img/1teamweb-logo-200.png";
	}

	if (($command == GenerateAllMembers) || ($command == GenerateLatestMembers)){
		$strSQL = "SELECT teams.id as id_team, teams.*, users.firstname, users.lastname, users.teamid, users.id as userid, useraccountinfo.status, useraccountinfo, images.* FROM users, useraccountinfo, teams LEFT OUTER JOIN images ON (images.teamid = teams.id and images.type = ?) WHERE users.teamid = teams.id AND (users.roleid & " . Role_Member  . ") = " . Role_Member . " AND users.teamid = ? AND users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status <> " . UserAccountStatus_Inactive ;

		if ($command == GenerateLatestMembers){
			try{
				$today = new DateTime();
				$prevmonth = new DateTime($today->format("d-m-Y") . " -1 month");
				$strSQL .= " AND users.startdate >= '" . $prevmonth->format("m-d-Y"). "' ORDER BY firstname;";
			} catch(Exception $e){
				$bError = true;
				$err = $e->getMessage( ) ." ". (int)$e->getCode( );
			}
		} else {
			$strSQL .= " ORDER BY firstname;";
		}
		if (!$bError){
			$pdostatement = $dbh->prepare($strSQL);
			$pdostatement->execute(array( ImageType_Team, $teamid));
			$userprops = $pdostatement->fetchAll();
		}
	} else if ($command == userList ){
		$strSQL = "SELECT teams.id as id_team, teams.*, users.firstname, users.lastname, users.teamid, users.id as userid, useraccountinfo.status, useraccountinfo, images.* FROM users, useraccountinfo, teams LEFT OUTER JOIN images ON (images.teamid = teams.id and images.type = ?) WHERE users.id IN (" . implode(",", $useridList) . ") AND users.teamid = teams.id AND (users.roleid & " . Role_Member  . ") = " . Role_Member . " AND users.teamid = ? AND users.useraccountinfo = useraccountinfo.id AND useraccountinfo.status <> " . UserAccountStatus_Inactive ;
		$pdostatement = $dbh->prepare($strSQL);
		$pdostatement->execute(array( ImageType_Team, $teamid));
		$userprops = $pdostatement->fetchAll();
	} else {
		$bError = true;
		$err = "com";
	}

	if (!$bError){
		$loopCount = 0;
		$numUserRecords = count($userprops);
		$rowCount = 0;
		while ($loopCount < $numUserRecords) {
			$loopCount++;?>
<!-- loopCount = <?php echo $loopCount ?>-->
<?php
			if (($loopCount % numColumns) == 1){     // 1, 4, 7, 10, ...
					// Every 3 members, we start a new row
					$rowCount++;     ?>
<!-- rowCount = <?php echo $rowCount ?>-->
<?php				// Every 8 rows we start a new table and force a page break before it
					if (($rowCount % rowsPerPage) == 1) {  // 9, 17, ..
						if ($rowCount != 1) { ?>
</table>
<?php					// Every 8 rows, we ask the page to break (relevant for printing only) ?>
<div style="page-break-before:always;font-size:1;margin:0;border:0;"><span style="visibility: hidden;">-</span></div>
<?php					// Generate a page full of logos so when printed duplex, the logos are on the back of the barcode cards ?>
<table class="idcard">
<?php						for ($loopLogo=1; $loopLogo <= rowsPerPage; $loopLogo++){
								echo "<tr>\n";
								for ($loopRow = 1; $loopRow <= numColumns; $loopRow++){ ?>
<td align="center" class="idcard_cell"><img class="idcard_logo" src="<?php echo $teamlogo?>" alt="logo"></td>
<?php							}
								echo "</tr>\n";
							} ?>
</table>
<div style="page-break-before:always;font-size:1;margin:0;border:0;"><span style="visibility: hidden;">-</span></div>
<?php
	 					} ?>
<table class="idcard">
<?php	    			}
					echo "<tr>\n";
				} ?>
<td align="center" class="idcard_cell">
<div id="idcard">
<span class="strong"><?php echo $userprops[$loopCount-1]["name"]?></span><br>
<?php
				// Format the address.
				if (strlen($userprops[$loopCount-1]["address1"] . $userprops[$loopCount-1]["city"] . $userprops[$loopCount-1]["state"] . $userprops[$loopCount-1]["postalcode"]) > 0)
					$formataddress = $userprops[$loopCount-1]["address1"] . " " . $userprops[$loopCount-1]["city"] . ", ". $userprops[$loopCount-1]["state"] . "&nbsp;" . $userprops[$loopCount-1]["postalcode"];
				else
					$formataddress = "&nbsp;"; ?>
<div id="idcard_address"><?php echo $formataddress?></div>
<?php echo $teamterms["termmember"] . ": " . $userprops[$loopCount-1]["firstname"] . " " . $userprops[$loopCount-1]["lastname"]?><br>
<?php  echo str_pad(getUserBarcodeNumber($userprops[$loopCount-1]["teamid"],$userprops[$loopCount-1]["userid"]), barcodeLength, "0", STR_PAD_LEFT)?><br>
<img class="idcard_barcode" src="./php-barcode/barcode.php?code=<?php echo str_pad(getUserBarcodeNumber($userprops[$loopCount-1]["teamid"],$userprops[$loopCount-1]["userid"]), barcodeLength, "0", STR_PAD_LEFT)?>" alt="barcode"></div>
</td>
<?php
				// move to the next item so we can see if we are done, so we know how much to pad or close row
				if (($loopCount == $numUserRecords) || (($loopCount % numColumns) == 0)) {    //
					for ($loopPad = 1; $loopPad <= (numColumns - $loopCount); $loopPad++){ ?>
<td class="idcard_cell"><div id="idcard"></div></td>
<?php				} ?>
</tr>
<?php   			}

		}
		// Now that we're done with all the cards, close the table and dump out logos for the back of the page ?>
</table>
<div style="page-break-before:always;font-size:1;margin:0;border:0;"><span style="visibility: hidden;">-</span></div>
<table class="idcard">
<?php
		// tweak the rowcount to prevent more than a full page of logos at the end
		if ($rowCount > rowsPerPage) {
			$rowCount = rowsPerPage;
		}
		for ($loopLogo=1; $loopLogo <= $rowCount; $loopLogo++){ ?>
<tr>
<?php		for ($loopRow = 1; $loopRow <= numColumns; $loopRow++){  ?>
<td align="center" class="idcard_cell"><img class="idcard_logo" src="<?php echo $teamlogo?>" alt="logo"></td>
<?php		} ?>
</tr>
<?php   	}  ?>
</table>
<?php
	}
}
if ($bError){
	echo '<p class="error">Error';
	if (isDevelopmentServer() || isStagingServer())
		echo "<br/>".$err;
	echo '</p>';
}?>
</div>
<?php
$cancelbutton = 1;	// places a "return" button just above the footer
// Start footer section
include('footer.php');
?>
</body>
</html>
