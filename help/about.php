<?php 
$title="About";
include ("help-header.php"); ?>
<div class="helpboxtext">
<p>Version <?php if (($session != RC_SessionKey_Invalid) && (isAnyAdminLoggedIn($session))) {
	echo '<a target="_blank" href="http://1teamweb.com:8080/secure/IssueNavigator.jspa?reset=true&jqlQuery=project+%3D+OTW+AND+fixVersion+%3D+%22'.appversion.'%22+AND+status+%3D+Resolved+ORDER+BY+priority+DESC&mode=hide">'. appversion . '</a>';
} else echo appversion?>.</p>
<p>Written by <a href="<?php echo contact?><?php buildRequiredParams($session)?>"><?php echo author ?></a>.</p>
<p>Read our <a href="default.php">overview</a> for more details.</p>
</div>
<?php
// Start footer section
include('../footer.php'); ?>
</body>
</html>
