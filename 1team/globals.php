<?php
include('obj/Objects.php');

// Declare global variables
define("dbusername", "postgres");
define("dbname", "postgres");
define("system_wwwroot", "C:/Inetpub/wwwroot/1team/");
define("url_sslroot", "https://www.1teamweb.com/"); // production root url
define("url_root_dev", "http://localhost/"); // dev root url
define("url_root", "http://1teamweb.com");  // This shouldn't be used except for JIRA, which is not https:
define("mysqlp", system_wwwroot."config/"."mysqlp");  // For local mysql settings, not on Heroku
define("dbconn", "wwwpg1teamweb");
define("dbport", "5234");                   // Note this is a non-standard PostgreSQL port for simple security reasons
define("dbdriver", "PostgreSQL Unicode");
define("visitors", 0);
define("companyname", "1 Team Web");
define("appname",  "1 Team Web");
define("appname_nowhitespace",  "1TeamWeb");
define("apptagline",  "Focus on the Team.");
define("appversion",  "3.0.2");       // oneteamweb3
define("author",  "David Thomas");
define("contact",  "contact.php");
define("emailadmin",  "seattlejits@gmail.com");
define("siteurl", "https://www.1teamweb.com");
define("MailServer", "smtp.sendgrid.net");
define("Default_Logo", "1team/img/1teamweb-logo-200.png");
define("jirahome", url_root.":8080");

// Default team terms
define("defaultterm_user", "User");
define("defaultterm_admin", "Team Administrator");
define("defaultterm_coach", "Coach");
define("defaultterm_member", "Member");
define("defaultterm_team", "Team");
define("defaultterm_class", "Class");
define("defaultterm_appadmin", "Application Administrator");

// Note: this could change, so stay on top of it. It is used to visually distinguish staging vs. production
define("stagingserveraddr",  "localhost:8000");
define("productionserveraddr",  "192.168.0.8");

// Dojo dijit style
define("dojostyle", "tundra");

// Store Account Status as array
$aStatus = array("undefined", "Inactive", "Active", "Overdue", "Disabled");

$aTeamStatus = array("undefined", "Inactive", "Active",  "Pending License", "Overdue",  "Disabled");


// Store Team Account Plan as Array
$aTeamPlan = array("undefined", "10 Members Maximum",  "25 Members Maximum", "50 Members Maximum",  "100 Members Maximum",  "250 Members Maximum", "500 Members Maximum", "1000 Members Maximum");

// Store Team Account Plan member numbers as Array
// The intent is to access this like so: x = aTeamPlanMaxMembers($session['teamaccountplan'])
// Note, this is limiting, even though it is "unlimited"
$aTeamPlanMaxMembers = array(0, 10, 25, 50, 100, 250, 500, 750, 1000);

// Store Team Costs as Array
// All costs are per month
$aTeamCost = array(0, 1.00, 2.50, 5.00, 10.00, 25.00, 50.00, 75.00, 100.00);
//			0, 	 	// undefined
//			1.00, 	// 10 users
//			2.50,	// 25 users
//			5.00,	// 50 users
//			10.00,	// 100 users
//			25.00,	// 250 users
//			45.00,	// 500 users
//			75.00, 	// 750 users
//			100.00);	// 1000 users

$aTeamPlanDuration = array("undefined",  "Unlimited", "");

define("ApplicationAdminEmail",  "coach@austinjiujitsu.com");

// Declare our roles, since they are referenced everywhere
// These represent bit fields that can be combined with binary operators, allowing a team admin to also be a coach, for example
define("Role_Undefined", 0);
define("Role_ApplicationAdmin", 1);
define("Role_TeamAdmin", 2);
define("Role_Coach", 4);
define("Role_Member", 8);

// Gender
define("Gender_Undefined", "NA");
define("Gender_Male", "male");
define("Gender_Female", "female");

// Custom data
define("CustomDataType_Undefined", 0);
define("CustomDataType_Text", 1);
define("CustomDataType_Num", 2);
define("CustomDataType_Float", 3);
define("CustomDataType_Bool", 4);
define("CustomDataType_Date", 5);
define("CustomDataType_List", 7);

// Custom Lists
define("CustomList_Undefined", 0);

// Display conditions for custom data are based on user and useraccountinfo columns
define("DisplayConditionObject_Undefined", 0);
define("DisplayConditionObject_User", "users");
define("DisplayConditionObject_UserAccount", "useraccountinfo");

define("DisplayConditionOperator_EQ", "=");
define("DisplayConditionOperator_LT", "<");
define("DisplayConditionOperator_GT", ">");
define("DisplayConditionOperator_NE", "<>");

define("DisplayConditionUserColumn_Undefined", 0);
define("DisplayConditionUserColumn_birthdate", "birthdate");
define("DisplayConditionUserColumn_gender", "gender");
define("DisplayConditionUserColumn_coachid" , "coachid");
define("DisplayConditionUserColumn_roleid" , "roleid");
define("DisplayConditionUserColumn_programid" , "programid");

define("DisplayConditionUserAccountColumn_Undefined", 0);
define("DisplayConditionUserAccountColumn_status" , "status");
define("DisplayConditionUserAccountColumn_isbillable" , "isbillable");

// User account statuses stored in useraccountinfo.status column
define("UserAccountStatus_Undefined", -1);
define("UserAccountStatus_Inactive", 0);
define("UserAccountStatus_Active", 1);
define("UserAccountStatus_Overdue", 2);
define("UserAccountStatus_Disabled", 3);
define("UserAccountStatus_Guest", 4);	// Special status to allow guests to scan guest passes since they aren't technically active
define("UserAccountStatus_Error", 999);

// Team Account Status stored in teamaccountinfo.status column
define("TeamAccountStatus_Inactive", 0);
define("TeamAccountStatus_Active", 1);
define("TeamAccountStatus_PendingLicense", 2);	// Only let team admin see license page. No other logins allowed
define("TeamAccountStatus_Overdue", 3);

// Account Payment Method for members and teams, stored in useraccountinfo and teamaccountinfo.paymentmethod column
define("PaymentMethod_Undefined", 0);
define("AccountPaymentMethod_NotBillable", -1);
define("AccountPaymentMethod_Undefined", 0);
define("AccountPaymentMethod_Paypal", 1); 	// This is reserved for all teams

// Account payment period
define("AccountPaymentPeriod_Undefined", 0);

define( "betauserlimittxt", "two hundred");
define( "betauserlimit", "200");

// Team Account Plan stored in teamaccountinfo.plan column
define("TeamAccountPlan_Unlimited", -1);
define("TeamAccountPlan_Undefined", 0);

// Team Account Plan Duration stored in teamaccountinfo.planduration column
define("TeamAccountPlanDuration_Unlimited", -1);
define("TeamAccountPlanDuration_Undefined", 0);
// Other values in this column will match the number of months the plan is valid for

// These are potentially confusing constants.
// if (you add these offset to the above constants you will get the
// array index of the string name for the values. For example, if (the status is
// UserAccountStatus_Active (value is 1), the array index would be 3.
define("UserAccountStatus_ArrayOffset", 1);
define("TeamAccountStatus_ArrayOffset", 1);
define("AccountPaymentMethod_ArrayOffset", 2);
define("TeamAccountPlan_ArrayOffset", 2);
define("TeamAccountPlanDuration_ArrayOffset", 0);
define("PaymentType_ArrayOffset", 1);

// Payment types used in the orderitems table paymenttype column
define("PaymentType_Undefined", 0);
define("PaymentType_Regular", 1);
define("PaymentType_Annual", 2);

// Program IDs
define("Program_Undefined", 0);
define("Program_Default", 1);

// define custom data types
define("Datatype_Undefined", 0);
define("Datatype_Text", 1);
define("Datatype_Int", 2);
define("Datatype_Float", 3);
define("Datatype_Boolean", 4);
define("Datatype_Date", 5);
define("Datatype_List", 7);

// Base values
define("TeamID_Undefined", 0);
define("UserID_Base", 1);
define("TeamID_Base", 1);

// Sessions
define("SessionExpiration_Hour", (int)(60*60));			// Non-admin User default
define("SessionExpiration_Month", (int)(60*60*24*30));	// Admin users
define("SessionExpiration_Hour_SQL", "hours");	// Non-admin User default
define("SessionExpiration_Month_SQL", "1 months");	// Admin users

// SKU Expiration
define("Interval_Undefined", 0);
define("skuExpiration_Undefined", Interval_Undefined);
define("skuExpirationUnits_Undefined", "undefined");
define("skuExpirationUnits_Days", "days");
define("skuExpirationUnits_Weeks", "weeks");
define("skuExpirationUnits_Months", "months");
define("skuExpirationUnits_Years", "years");

define("skuExpiration_Never", -1);

// This is for SQL conversion of intervals
define("skuSQL_Days", "day");
define("skuSQL_Weeks", "week");
define("skuSQL_Months", "mon");
define("skuSQL_Years", "year");

// ePayment sources
define("ePaymentSourcePayPal", 1);	// epayment source

// Other epayment constants
define( "ePaymentID_Undefined", 0);
define( "ePaymentItemDisplayLength", 32);	// Only display a limited numbers for the item description - keeps table neat


// Error codes
// Requirements: all error codes are < 1. All success codes are >=1. isSuccessful($rc) function depends on it
define("RC_Success", 1);
define("RC_SessionKey_Invalid", 	RC_Success - 1); 	// 0
define("RC_SessionExpiration_Failure", RC_Success -2); 	//-1
define("RC_UserNotFound_Failure", 	RC_Success-3); 		//-2
define("RC_SessionUpdate_Failure", 	RC_Success-4);		//-3 	-- use odbc_error or check postgres log for help
define("RC_HashGenError", 			RC_Success-5);		//-4
define("RC_OdbcError", 				RC_Success-6);		//-5  	-- use odbc_error or check postgres log for help
define("RC_SessionCreateError", 	RC_Success-7);		//-6  	-- use odbc_error or check postgres log for help
define("RC_TeamInfoError", 			RC_Success-8);		//-7  	-- use odbc_error or check postgres log for help
define("RC_TeamTermsError", 		RC_Success-9);		//-8  	-- use odbc_error or check postgres log for help
define("RC_UserID_Invalid", 		RC_Success-10);		//-9
define("RC_SessionNotFound_Error", 	RC_Success-11);		//-10	-- use odbc_error or check postgres log
define("RC_RequiredInputMissing", 	RC_Success-12);		//-11	-- either userid or sessionkey missing in GET
define("RC_HashFailure", 			RC_Success-13);		//-12	-- sha1
define("RC_TeamID_Invalid", 		RC_Success-14);		//-13
define("RC_SessionExpired", 		RC_Success-15);		//-14
define("RC_NoLicense", 			RC_Success-16);		//-15	user login attempted without team admin having accepted license
define("RC_IncorrectPassword", 	RC_Success-17);		//-16
define("RC_NotAuthorized", 		RC_Success-18);		//-17
define("RC_LoginFailure", 		RC_Success-19);		//-18
define("RC_PDO_Error", 			RC_Success-20);		//-19     Generic PDO Execute error
define("RC_Promotion_Error", 		RC_Success-21);		//-20     Error promoting user
define("RC_EmailFailure", 		RC_Success-22);		//-21     Error sending email
define("RC_LoginFailAccountLocked",RC_Success-23);		//-22     Error - Admin account locked
define("RC_LogAttendanceUnsuccessful",RC_Success-24);		//-23     Error - LogAttendance
define("RC_EmailFailed",			RC_Success-25);		// -24	Error sending email
define("RC_EmailAddrInvalid",		RC_Success-26);		// -25	Error sending email - bad address
define("RC_SessionCleanupFail",	RC_Success-27);		// -26	Error deleting stale sessions
//
// Human-readable error codes - TO DO - get rid of these and replace with numeric codes that tranlsate in the script to error strings
define("NotFound", "Object not found");
define("UserNotFound", "User not found");
define("Error", "ERROR");
define("TeamNameError", "Team Name not found");
define("NotAuthorized", "You are not authorized to do that.");

// Simple const to check for session variable when no Admin IP address is set for a team
define('SALT_LENGTH', 9);
define("PASSWORD_LENGTH", 64);
define("CleartextPasswordLength", 8);
define("MinLenEmail", 6);
define("SESSIONKEY_LENGTH", 8);
define("MinLenSMSPhone", 7);
define("MaxLenSMSPhone", 10);

// Event ID
define("eventidUndefined", 0);

// Scan related
define("scanTimeResultDisplayTimeout", 4500);

// Page modes for include-member-roster
define("pagemodeAnnualUndefined", 0);
define("pagemodeSearch", 1);
define("pagemodeAnnualFee", 2);
define("pagemodeRoster", 3);
define("pagemodeAttendanceOnDate", 4);
define("pagemodeEmbedded", 5);
define("pagemodeStandalone", 6);

// Images
define("dynamicimagediv_Height", 100);

// File locations
define("Image_Root", "img");
define("ImageID_Undefined", 0);
define("uploadsDir", "uploaded");
// For creating hash of image filenames <teamid>-<objid>-<image type>
define("ImageType_Undefined", 0);
define("ImageType_Team", 1);
define("ImageType_Event", 2);
define("ImageType_Promotion", 3);
define("ImageType_User", 4);

// Order item array is used to package orderitems into orders
define("OrderItemArraySize", 3);
define("OrderItemArrayIndex_SKU", 0);
define("OrderItemArrayIndex_Amount", 1);
define("OrderItemArrayIndex_Fee", 2);

// Team Admin intro email text
define("teamadmin_introtext", "Welcome to " . appname . ". As a " . defaultterm_admin . ", you are authorized to create new " . defaultterm_user ."s and set up, manage, and customize your " . defaultterm_team . ". Your first steps should be to set up your team properties, then create new members.");

// minimum lengths
define("minlen_name", 1);
define("minlen_login", 6);

define("barcodeLength", 12);
define("GenerateAllMembers", -1);
define("GenerateLatestMembers", -2);
define("GenerateMultipleMembers", -3);

define("LevelID_Undefined", 0);

define("smsphonecarrier_Undefined", 0);
define("authsmsretries_Max", 3);		// Maximum times we allow the Team Admin to get an SMS resent in 5 minutes
define("authsmsfailure_LockoutPenalty", "5 minutes");	// Assuming I'll use milliseconds. Unimplemented.
// MaxHeight of the member selection lists
define("memberSelectionListMaxRows", 25);
// Email recipient groups
define("emailRecipientGroupUndefined", 0);
define("emailRecipientGroupAllActiveMembers", 1);
define("emailRecipientGroupArbitrarySelection", 2);
define("emailRecipientGroupNewMembers", 3);
define("emailRecipientGroupNonParticipants", 4);		// Those who do not have an active (non-expired) order within a given program
define("emailRecipientGroupActiveParticipants", 5);	// Those who do have an active (non-expired) order within a given program
define("emailRecipientGroupRecentlyExpired", 6);		// People who have recently had a given SKU expire
define("emailRecipientGroupPastMembers", 7);			// Inactive members
define("smsTextLimit", 140);							// How many characters

// SMS phone carrier email addresses
define("alltel", "text.wireless.alltel.com");
define("tmobile", "tmomail.net");
define("googlefi", "msg.fi.google.com");
define("boost", "boostmobile.com");
define("cellularone", "mobile.celloneusa.com");
define("qwest", "qwestmp.com");
define("virgin", "bills.com");
define("att", "txt.att.net");
define("sprint", "messaging.sprintpcs.com");
define("verizon", "vtext.com");
define("nextel", "page.nextel.com");

// Facebook Graph API registration for 1TeamWeb, see http://github.com/facebook/php-sdk/
define("FBAppName", "1TeamWeb");
define("FBAppURL","https://www.1teamweb.com/");
define("FBAppID","134315679923894");
define("FBAppSecret","6eed50b5b89d5c7707cfbc175e98ac97");

// Redemption cards
define("RedemptionCardID_All", -1);
define("RedemptionCardID_Unknown", 0);
define ("NewCard", 0);
define ("EditCard", 1);

?>
