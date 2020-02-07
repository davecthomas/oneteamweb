<?php  
include ('utils.php');

// Assure we have the input we need, else send them to default.php
if ((($sessionkey = getSessionKey()) == RC_RequiredInputMissing) || (($userid = getUserID()) == RC_RequiredInputMissing)){
	redirect("default.php?rc=" . RC_RequiredInputMissing);
}
// Get $session array initialized
$session = startSession($sessionkey, $userid);
if (! isValidSession($session )){
	redirect("default.php?rc=" . $session);
}

$bError = false;
$errno = 0;
// Only app admins can execute this script
if (!isUser($session, Role_ApplicationAdmin)){
	$bError = true;
}
if (!$bError) {
	$dbh = getDBH($session);  

	if ( isset($_POST["teamname"])) {
		$teamname = $_POST["teamname"];
	} else {
		$teamname = "Unnamed team";			
	}
	
	// Prevent duplicate names
	$strSQL = "SELECT * FROM teams WHERE name = ?;";
	$pdostatement = $dbh->prepare($strSQL);
	$pdostatement->execute(array($teamname));
	
	$teamResults = $pdostatement->fetchAll();
	
	$rowCount = 0;
	if (count($teamResults) > 0) {
		$bError = true;
	} else {
	
		if ( isset($_POST["city"])) {
			$city = $_POST["city"];
		} else {
			$city = "";			
		}
		if ( isset($_POST["state"])) {
			$state = $_POST["state"];
		} else {
			$state = "";			
		}
		if ( isset($_POST["address1"])) {
			$address1 = $_POST["address1"];
		} else {
			$address1 = "";			
		}
		if ( isset($_POST["address2"])) {
			$address2 = $_POST["address2"];
		} else {
			$address2 = "";			
		}
		if ( isset($_POST["postalcode"])) {
			$postalcode = $_POST["postalcode"];
		} else {
			$postalcode = "";			
		}
		if ( isset($_POST["email"])) {
			$email = $_POST["email"];
		} else {
			$email = "";			
		}
		if ( isset($_POST["phone"])) {
			$phone = $_POST["phone"];
		} else {
			$phone = "";			
		}
		if ( isset($_POST["website"])) {
			$website = $_POST["website"];
		} else {
			$website = "";			
		}
		if ( isset($_POST["paymenturl"])) {
			$paymenturl = $_POST["paymenturl"];
		} else {
			$paymenturl = "";			
		}
		if ( isset($_POST["activityname"])) {
			$activityname = $_POST["activityname"];
		} else {
			$activityname = "";			
		}
		if ( isset($_POST["notes"])) {
			$notes = $_POST["notes"];
		} else {
			$notes = "";			
		}
	
		if ( isset($_POST["startdate"])) {
			$startdate = $_POST["startdate"];
		} else {
			$startdate = "";			
		}
				
	// TO DO!!! Some day we should be able to upload a Logo file...
	
		$strSQL = "INSERT INTO teams VALUES (DEFAULT, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, NULL, NULL, ?, NULL);";
		$pdostatementUpdate = $dbh->prepare($strSQL);
		
		$pdostatementUpdate->execute(array($teamname, $city, $state, $address1, $address2, $postalcode, $phone, $email, $website, $activityname, $notes, $startdate, $paymenturl));		
	
		// Now get the new ID, then set account settings
		$teamid  = 0;
		$strSQL = "SELECT id FROM teams WHERE name = ?";
		$pdostatement = $dbh->prepare($strSQL);
		$pdostatement->execute(array($teamname));	
		$teamid = $pdostatement->fetchColumn();
	
		if ($teamid > 0){
		
			// Set account info 
			$status = TeamAccountStatus_PendingLicense;  // All teams are created pending license so they have to accept license agreement
			
			if ( isset($_POST["isbillable"])) {
				$isbillable = $_POST["isbillable"];
			} else {
				$isbillable = true;			
			}
			if ( isset($_POST["plan"])) {
				$plan = $_POST["plan"];
			} else {
				$plan = TeamAccountPlan_Undefined;			
			}
			if ( isset($_POST["planduration"])) {
				$planduration = $_POST["planduration"];
			} else {
				$planduration = TeamAccountPlanDuration_Undefined;			
			}
				
			$strSQL = "INSERT INTO teamaccountinfo VALUES (DEFAULT, ?, ?, ?, ?, ?);";
			$pdostatementAccount = $dbh->prepare($strSQL);
			
			$pdostatementAccount->execute(array($teamid, $status, $plan, $planduration, $isbillable));	
			
			redirect("team-props-form.php?" . returnRequiredParams($session) . "&teamid=" . $teamid . "&new=1");
		} else {
			$bError = true;
		}
	}	
} 

if ($bError) {
	redirect("new-team-form.php?" . returnRequiredParams($session) . "&err=" . $errno);
}
 ?>
