<?php
include("globals.php");
$title = appname . " : " . apptagline;  ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><?php echo $title ?></title>
<link rel="stylesheet" type="text/css" href="1team.css"/>
<script type="text/javascript" src="/1team/utils.js"></script>
</head>
<body>
<div id="wrapper">
<?php include('nav-notloggedin.php'); ?>
<h3><?php echo $title ?></h3>
<?php 
include ('help/overview.php');
include ('help/help-default.php');
include('footer.php');
if (isset($_GET["logout"])){ ?>
<div class="msgboxshow" id="msgbox">
<div class="msgboxtitle">Signed off<div class="msgboxclose"><a class="linkopacity" href="javascript:togglevis2('msgbox', 'msgboxshow', 'msgboxhide')"><img src="img/x-closebutton.png" alt="close"/></a></div><hr /></div>
You have been signed out. <a href="login-form.php">Sign on again</a> to access <?php echo appname?>.
<br /><br />
<div class="boxbutton"><input type="button" value="Ok" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onClick="javascript:togglevis2('msgbox', 'msgboxshow', 'msgboxhide');"/></div>
</div><script type="text/javascript">setTimeout("opacity('msgbox',100, 0, 1000);",3000)</script><?php
}?>
</body>
</html>