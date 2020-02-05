<?php
include("globals.php");
$title = appname . " : " . apptagline;  ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><?php echo $title ?></title>
<link rel="stylesheet" type="text/css" href="1team.css"/>
<script type="text/javascript" src="/1team/utils.js"></script>
<script type="text/javascript">
function validateRegForm(form) {
	if (! ValidateEmail( form)) {
		return false;
	}
	var e = form.elements;
	if ((e['firstname'].value.length < 1) || (e['firstname'].value.length < 1) || (e['lastname'].value.length < 1) || (e['email'].value.length < 1) || (e['teamname'].value.length < 1) || (e['activityname'].value.length < 1)){
		alert("All fields are required.");
		return false;
	}

	return true;
}
</script>
</head>
<body>
<div id="wrapper">
<?php include('nav-notloggedin.php'); ?>
<h3><?php echo $title ?></h3>
<form action="/1team/register.php" method="post" name="registerform" onsubmit="return validateRegForm(this)">
<h4><a class="linkopacity" href="javascript:togglevis('personalinfo')">Team Administrator Information<img src="img/a_collapse.gif" alt="collapse section" id="personalinfo_img" border="0"></a></h4>
<p>Team administrators are responsible for the <?php echo appname?> account. All fields are required.</p>
<div class="showit" id="personalinfo">
<div class="group">
<div class="indented-group-noborder">
<table class="noborders">
<tr>
<td><b>First Name</b></td>
<td><input type="text" value="" name="firstname"></td>
</tr>
<tr>
<td><b>Last Name</b></td>
<td><input type="text" value="" name="lastname"></td>
</tr>
<tr>
<td><b>Phone </b></td>
<td ><input type="text" value="" name="phone"></td>
</tr>
<tr>
<td><b>Email Address</b></td>
<td><input type="text" value="" name="email"></td>
</tr>
</table>
</div>
</div>
</div>
<h4><a class="linkopacity" href="javascript:togglevis('otherinfo')">Team Information<img src="img/a_collapse.gif" alt="collapse section" id="otherinfo_img" border="0"></a></h4>
<div class="showit" id="otherinfo">
<div class="group">
<div class="indented-group-noborder">
<table class="noborders">
<tr>
<td><b>Team Name</b></td>
<td><input type="text" value="My Team" name="name" width="80" size="80">
</td>
</tr>
<tr>
<td><b>Activity Name</b></td>
<td><input type="text" value="Enter name of activity" name="activity" width="80" size="80">
</td>
</tr>
</table>
</div>
</div>
</div>
<table class="noborders">
<tr>
<td><b>How did you hear about us?</b></td>
<td ><input type="text" value="" name="referredby" width="80" size="80"></td>
</tr>
</table>
<p>
<input type="submit" value="Sign me up!" name="new" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
</form></p>
<?php
include ('help/overview.php');
include ('captcha.php');
include('footer.php'); ?>
</body>
</html>
