<?php 
// Coaches, Admins have search capability to find members
if (! isUser($session, Role_Member) ) {
?>
<div id="search">
<form action="/1team/search.php" method="post">
<input type="hidden" name="teamid" value="<?php
// TeamID is passed in for application admin,  else { it's in the session
if ((!isUser( $session, Role_ApplicationAdmin)) && (isset($session["teamid"]))) {
	echo $session["teamid"];
} else {
	if (isset($_REQUEST["teamid"])) echo $_REQUEST["teamid"];
	else echo TeamID_Undefined;
}?>"/>
<?php buildRequiredPostFields($session) ?>
<input type="text"  name="str" maxlength="50" size="17">
<input type="submit" value="Search" name="search"  class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
</form>
</div>
<?php 
// End coach, admin search block
}
?>
<div id="userbanner">
<?php $rolename = roleToStr($session["roleid"], $teamterms);?>
	Signed in as <?php echo $rolename?>&nbsp;<a href="/1team/home.php<?php buildRequiredParams($session)?>"><?php echo $session["fullname"]?></a>. Session time remaining:&nbsp;<?php echo getSessionTimeRemaining($session)?>&nbsp;
<input type="button" value="Sign out" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onClick="document.location.href='/1team/logout.php<?php buildRequiredParams($session)?>'"/>
</div>
<div class="helpicon"><a class="linkopacity" href="javascript:togglevis2('help_main','helpmainshow','helpmainhide')"><img src="/1team/img/icon_help_widgets.gif" border="0"/></a></div>
