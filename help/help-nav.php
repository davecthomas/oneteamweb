<div class="navtop">
<ul id="nav">
<li><?php
if ((isset($session)) && ($session != RC_SessionKey_Invalid)) { ?>
<a href="/1team/home.php<?php buildRequiredParams($session) ?>">
<?php
} else { ?>
<a href="/1team/default.php<?php buildRequiredParams($session) ?>">
<?php
} 
echo appname?> Home</a></li>
<li><a href="default.php<?php buildRequiredParams($session) ?>">Help Center Home</a></li>
<li><a href="#">Topics</a>  
<ul>
<li><a href="default.php<?php buildRequiredParams($session) ?>">Overview</a></li>
<li><a href="about.php<?php buildRequiredParams($session) ?>">About <?php echo appname?></a></li>
<?php if (($session != RC_SessionKey_Invalid) && (isAnyAdminLoggedIn($session))) { ?>
<li><a href="<?php echo jirahome ?>/secure/CreateIssue!default.jspa" target="_blank">Report an issue in <?php echo appname?></a></li>
<li><a href="1TeamWebAdminGuide.pdf" target="_blank"><?php echo appname?> Administrator's Guide</a></li>
<?php } ?>
<li><a href="privacy.php<?php if (isset($session)) buildRequiredParams($session);?>">Privacy policy</a></li>
<li><a href="security.php<?php if (isset($session)) buildRequiredParams($session);?>">Site security</a></li>
</ul>
<li><a href="#">Contact Us</a>  
<ul>
<li><a href="contact.php<?php buildRequiredParams($session) ?>">Contact <?php echo companyname?></a></li>
</ul>
</li>
</ul>
</div>