<?php 
ob_start(); 	// This caches non-header output, allowing us to redirect after header.php
// The title is set here and rendered in header.php
$isadminrequired = true;
$title= " New User Step 2" ;
include('header.php'); 
$title = "New " . $teamterms["termmember"] . ": Step 2";
$bError = false;
$err = "";

if ( !$bError){
	echo("1");
	if ( isset($_REQUEST["roleid"])) {
		$roleid = $_REQUEST["roleid"];
	} else {
		$roleid = Role_Member;
	}	
	
	if (( isset($_REQUEST["firstname"]))&& (strlen($_REQUEST["firstname"]) >= minlen_name)) {
		$firstname = $_REQUEST["firstname"];
	} else {
		$bError = true;
		$err = "A user name of at least " . minlen_name . " characters is required.";
	}	

	if (( isset($_REQUEST["lastname"])) && (strlen($_REQUEST["lastname"]) >= minlen_name)) {
		$lastname = $_REQUEST["lastname"];
	} else {
		$bError = true;
		$err = "A user name of at least " . minlen_name . " characters is required.";
	}	

	if ( isset($_REQUEST["startdate"])) {
		$startdate = $_REQUEST["startdate"];
	} else {
		$startdate = date("Y-m-d");
	}	

	if ( isset($_REQUEST["address1"])) {
		$address1 = $_REQUEST["address1"];
	} else {
		$address1 = "";
	}	

	if ( isset($_REQUEST["address2"])) {
		$address2 = $_REQUEST["address2"];
	} else {
		$address2 = "";
	}	

	if ( isset($_REQUEST["city"])) {
		$city = $_REQUEST["city"];
	} else {
		$city = "";
	}	

	if ( isset($_REQUEST["state"])) {
		$state = $_REQUEST["state"];
	} else {
		$state = "";
	}	

	if ( isset($_REQUEST["postalcode"])) {
		$postalcode = $_REQUEST["postalcode"];
	} else {
		$postalcode = "";
	}	

	if ( isset($_REQUEST["smsphone"])) {
		$smsphone = $_REQUEST["smsphone"];
	} else {
		$smsphone = "";
	}
	echo("2");

	if ( isset($_REQUEST["smsphonecarrier"])) {
		$smsphonecarrier = $_REQUEST["smsphonecarrier"];
	} else {
		$smsphonecarrier = "";
	}

	if ( isset($_REQUEST["phone2"])) {
		$phone2 = $_REQUEST["phone2"];
	} else {
		$phone2 = "";
	}	

	if (( isset($_REQUEST["login"])) && (strlen($_REQUEST["login"]) >= minlen_login)){
		$login = $_REQUEST["login"];
	} else {
		$bError = true;
		$err = "A user ID of at least " . minlen_login . " characters is required.";
	}	

	if ( isset($_REQUEST["notes"])) {
		$notes = $_REQUEST["notes"];
	} else {
		$notes = "";
	}	

	if ( isset($_REQUEST["referredby"])) {
		$referredby = $_REQUEST["referredby"];
	} else {
		$referredby = "";
	}	

	// teamid depends on who is calling 
	if ( isUser($session, Role_TeamAdmin)){
		if ( !isset($session["teamid"])){
			$bError = true;
			$err = "A team must be selected.";
		} else {
			$teamid = $session["teamid"];
		}
	} else {
		if ( isset($_REQUEST["teamid"])){
			$teamid = $_REQUEST["teamid"];
		} else {
			$bError = true;
			$err = "A team must be selected, Admin!";
		}
	}
	
	if ((isset($_REQUEST["email"])) && (isValidEmail($_REQUEST["email"]))){
		$email = $_REQUEST["email"];
	} else {
		$bError= true;
		$err = "An email address is required.";
	}	

	// If a checkbox is set in post, that means it was checked
	if (isset($_REQUEST["sendemail"])) {
		$sendemail = true;
	} else {
		$sendemail = false;
	}

	
	// Members have fields that others don't 
	if ((!$bError) && (doesRoleContain($roleid, Role_Member))){
		if ( isset($_REQUEST["status"])) {
			$status = $_REQUEST["status"];
		} else {
			$bError = true;
			$err = "Status.";
		}	
	
		if ( isset($_REQUEST["isbillable"])) {
			$isbillable =$_REQUEST["isbillable"];
		} else {
			$bError = true;
			$err = "Billable";
		}	
	
		if ( isset($_REQUEST["birthdate"])) {
			$birthdate = $_REQUEST["birthdate"];
		} else {
			$birthdate = date("Y-m-d");
		}	
	
		if ( isset($_REQUEST["coachid"])) {
			$coachid = $_REQUEST["coachid"];
		} else {
			$coachid = User::UserID_Undefined;
		}	
	
		if ( isset($_REQUEST["gender"])) {
			$gender = $_REQUEST["gender"];
		} else {
			$gender = Gender_Undefined;
		}	
	
		if ( isset($_REQUEST["emergencycontact"])) {
			$emergencycontact = $_REQUEST["emergencycontact"];
		} else {
			$emergencycontact = "";
		}	
	
		if ( isset($_REQUEST["ecphone1"])) {
			$ecphone1 = $_REQUEST["ecphone1"];
		} else {
			$ecphone1 = "";
		}	
	
		if ( isset($_REQUEST["ecphone2"])) {
			$ecphone2 = $_REQUEST["ecphone2"];
		} else {
			$ecphone2 = "";
		}	

	}

	if ( !$bError) {
	     $userid = createUser($session, $teamid, $roleid, $startdate, $firstname, $lastname, 
			 $login, $email, $address1, $address2, $city, $state, $postalcode,
				$smsphone, $smsphonecarrier, $phone2, $birthdate, $referredby, $notes, $coachid, 
				$emergencycontact, $ecphone1, $ecphone2, $gender, $isbillable, $status,
				$sendemail, /* Gen password is driven off of same value as email */ $sendemail /* Send email */, $bError);
		if ($userid != User::UserID_Undefined) {
			redirect("user-props-form.php?teamid=" . $teamid . "&id=" . $userid . "&" . returnRequiredParams($session)."&new=1");
		} else {
		     $bError = true;
		     $err = "cu";
		}

	}
}
if ($bError){
	// redirectToReferrer("&err=" . urlencode($err));
}

// Start footer section
include('footer.php'); ?>
</body>
</html>
<?php
ob_end_flush();?>
