<div class="push"></div>
<div class="push-logo"></div><?php  // This is to keep the footer from getting crowded at the bottom of long pages ?>
<div id="logo"><img alt="<?php echo companyname?>" src="<?php
if ((isset($session)) && (isset($session["teamimageurl"]))) {
	if (strlen($session["teamimageurl"]) > 0) { 
		echo $session["teamimageurl"];
	} else { 
		echo "/1team/img/1teamweb-logo-200.png";
	}
} else { 
	echo "/1team/img/1teamweb-logo-200.png";
}
?>" onclick="document.location.href = '/1team/default.php'" width="200" align="right"/></div>
<div class="push"></div><?php  // This is to keep the footer from getting crowded at the bottom of long pages ?>
<div id="footer">
<?php
if (isset($cancelbutton)){?>
<input type="button" value="Return" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = '<?php echo $_SERVER['HTTP_REFERER']?>'"/>
<?php
}?>
<p align="right">Copyright &copy; <?php echo date("Y") . " <a href=\"" . siteurl . "\">" . companyname . "</a>."?>&nbsp;&nbsp;
<a href="/1team/help/privacy.php<?php if (isset($session)) buildRequiredParams($session);?>">Privacy policy</a>&nbsp;&nbsp;
<a href="/1team/help/security.php<?php if (isset($session)) buildRequiredParams($session);?>">Site security</a> </p>
</div>
<?php  // This ends the wrapper div ?>
</div>