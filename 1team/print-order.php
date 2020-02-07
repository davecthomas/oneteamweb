<?php
// Only admins and coaches can execute this script. Header.php enforces this.
$isadminorcoachrequired = true;

$title = "Print Order";
include_once('header-minimal.php');
?>
<div id="userbanner">
Signed in as <?php echo roleToStr($session["roleid"],$teamterms)?>&nbsp;<?php echo $session["fullname"]?>. <a href="/1team/logout.php<?php buildRequiredParams($session)?>">Sign out</a><br />
Session time remaining:&nbsp;<?php echo getSessionTimeRemaining($session)?>
</div>
<?php
$bError = false;
if (isset($_GET["orderid"])) {
	$orderid = $_GET["orderid"];
} else {
	$bError = true;
	$orderid = 0;
} 	
if (isset($_GET["uid"])) {
	$userid = $_GET["uid"];
} else {
	$userid = User::UserID_Undefined;
	$bError = true;
}

// teamid depends on who is calling 
if (isUser($session, Role_TeamAdmin)){
	if (isset($session["teamid"])){
		$teamid = $session["teamid"];
	} else $bError = true;
} else {
	if (isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	} else $bError = true;
}

// Support emailing invoice to member
if (isset($_GET["email"])) {
	$email = true;
} else {
	$email = false;
}

if (!$bError){
	$teaminfo = getTeamInfo($teamid );
	$dbh = getDBH($session);
	if ($email){
		ob_start();
		include 'include-order-details.php';
		$emailmessage = ob_get_contents();
		ob_end_clean();
		$subject = $teaminfo["teamname"];
		if ((!$ispaid) && ($daysDue < 0)){
			$subject .= " - Payment Overdue - ";
		}
		if (!$ispaid) $subject .= " Invoice details";
		else $subject .= " Order details";
		if ((isset($teaminfo["website"])) && (strlen($teaminfo["website"])>strlen("http://.com"))) {
			$teamnamesite = "<a href=\"" . $teaminfo["website"] . "\">" . $teaminfo["teamname"] . "</a>";
		} else {
			$teamnamesite = $teaminfo["teamname"];
		}
		$rolename = roleToStr($session["roleid"], $teamterms);
		if ((isset($session)) && (isset($session["teamimageurl"]))) {
			if (strlen($session["teamimageurl"]) > 0) {
				$logoimg = getRoot() .$session["teamimageurl"];
			} else {
				$logoimg =  url_sslroot.Default_Logo;
			}
		} else {
			$logoimg = url_sslroot .Default_Logo;
		}
		$user = new User($session, $userid);
		$message = "
<style type=\"text/css\"><!--
#wrapper {
    position: absolute;
    top: 1.25em;
    left: 5;
	min-height: 95%;
	height: auto !important;
	height: 95%;
	width: 95%;
	margin: 0 auto -4em;
}

html, body {
	font-family: verdana, arial;
}
p.strong { font-weight:bold ;}
p.smaller {	font-size: .8em;}
span.strong { font-weight:bold ;}
span.smaller {	font-size: .8em;}
p{
	font-size: .8em;
}
#footer {
	font-size: .6em;
	float: left;
	position:absolute;
	bottom: 0; left: 0;
	height: 1em;
}
#logo {
	float: right;
	position:absolute;
	bottom: 0; right: 0;
}
div.pushlogo {
	height: 200px;
}
div.push {
	height: 1em;
}
table.noborders {
	border-width: 0px 0px 0px 0px;
	border-spacing: 2px;
	border-style: outset outset outset outset;
	border-collapse: separate;
	padding: 2px 10px 2px 10px;
}
table.noborders td{
	padding: 2px 10px 2px 10px;
}
tr.totalrow {
	 border: 1px solid #efe;
}
/* Table styling */
table {
	border-width: 1px 1px 1px 1px;
	border-spacing: 2px;
	border-style: none none none none;
	border-color: #efe;
	border-collapse: collapse;
}
table th {
	border-width: 1px 1px 1px 1px;
	padding: 1px 1px 1px 1px;
	border-style: solid solid solid solid;
	border-color: #88aa88;
	background-color:#88aa88;
	font:bold;
}
table tr {
	border: 1px solid #88aa88;
}

tr.even td {
	background-color:#aaddaa;
}
tr.odd td {
	background-color:#ddffdd;
}
.error { color: red;font-weight:bold; background-color:inherit}
.noevents { color: red;font-weight:bold; background-color:inherit}
.money { background-color:inherit}
.debit { color: red;font-style:italic; background-color:inherit}
.moneytotal { border-width: 3px 0 0 0; border-style: solid;  border-color:#000000; font-weight:bold ; font-size: 1.5em; background-color:inherit}
.moneytotaldebit { border-width: 3px 0 0 0; border-style: solid; font-style:italic; border-color:#000000; color: red; font-weight:bold ; font-size: 1.5em; background-color:inherit}
.billingissue { color:#CC6600;font-weight:bold; background-color:inherit}
.important { color:#CC33CC;font-weight:bold; background-color:inherit}
.usererror { color: red;font-weight:bold; background-color:inherit}
.attention {font-weight:bold; background-color:inherit}
.bold { font-weight:bold ; background-color:inherit}
.subdued{ color:#999999; background-color:inherit}
.small {	font-size: .8em;background-color:inherit}
.smallnormal {	font-size: .8em;font-weight: normal;background-color:inherit}
.normal {	font-weight: normal;}
.smallbold {	font-size: .8em;font-weight:bold ;background-color:inherit}
.smaller {	font-size: .6em;background-color:inherit}
.smallerbold {	font-size: .6em;font-weight:bold ;background-color:inherit}
.bigstrong {	 font-weight:bold ;font-size: 1.5em;background-color:inherit}
.bigstrongcenter {	 font-weight:bold ;font-size: 1.5em;background-color:inherit;text-align: center}
.strong { font-weight:bold ;}
--></style>
<html>
<head>
<title>".$subject."</title>
</head>
<body>
<div id=\"wrapper\">
<p class=\"strong\">".$user->getFirstName() . ",</p>
<p>".$emailmessage."</p>
<p>Thank you,</p>
<p><span class=\"strong\">". $session["fullname"] . "</span><br/>
<span class=\"smaller\">".$teamnamesite." " .$rolename."</span></p>
<div class=\"push\"></div>
<div class=\"push-logo\"></div>
<div id=\"logo\"><img alt=\"". $teaminfo["teamname"] ."\" src=\"".$logoimg."\" width=\"200\" align=\"right\"/></div>
<div class=\"push\"></div>
<div id=\"footer\">
<p>This message was sent from <a href=\"" . siteurl . "\">" . companyname . "</a> on ". date("l F j, Y") . ".</p>
</div>
</div>
</body>
</html>";
//		print_r(array($subject, $message, getUserEmail($session, $dbh)));
		$rc = $user->sendEmail($subject, $message, getUserEmail($session, $dbh), $err);
		if ($rc == RC_Success)
			redirect( "manage-orders-form.php?".returnRequiredParams($session)."&teamid=" . $teamid . "&done=1");
		else
			redirect( "manage-orders-form.php?".returnRequiredParams($session)."&teamid=" . $teamid . "&err=".$err);

	} else {
		include('include-order-details.php');
		if ($numOrders > 0){?>
<table class="noborders" width="65%">
<tr>
<td width="5%"></td>
<td width="45%"><input type="button" value="Print" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="window.print();return false;"/>&nbsp;<input type="button" value="Email <?php
		if ($ispaid)
			echo "receipt";
		else	echo "invoice";
		echo " to ". $orderResults[0]["firstname"]?>" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'print-order.php?<?php echo returnRequiredParams($session). "&orderid=".$orderid."&uid=".$userid."&teamid=".$teamid."&email=1"?>';"/>&nbsp;<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = '<?php if (isset($_SERVER["HTTP_REFERER"])) echo $_SERVER["HTTP_REFERER"]; else echo "manage-orders-form.php?". returnRequiredParams($session) ."&teamid=" . $teamid ?>'"/>
</td>
<td width="20%">
</td>
<td width="20%">
</td>
</tr>
</table>
<?php
		}
	}
}
if ($bError){
	redirect( "manage-orders-form.php?".returnRequiredParams($session)."&teamid=" . $teamid . "&err=1");
}
// Start footer section
include('footer.php'); ?>
</body>
</html>
<?php
ob_end_flush();?>