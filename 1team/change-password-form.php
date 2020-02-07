<?php
// The title is set here and rendered in header.php
$title= " Change Password " ;
include('header.php'); ?>
<h3>Change password for <?php echo roleToStr($session["roleid"], $teamterms)?>&nbsp;<?php echo $session["fullname"]?></h3>
<div class="indented-group-noborder">
<form action="/1team/change-password.php" method="post" onsubmit="return confirmpasswordmatch(this);">
<?php buildRequiredPostFields($session) ?>
<table class="noborders">
<tr><td>Current Password</td><td><input type="password"  name="current-password" maxlength="50"></td></tr>
<tr><td>New Password</td><td><input type="password"  name="new-password" maxlength="50"></td></tr>
<tr><td>Confirm New Password</td><td><input type="password"  name="new-password-confirm" maxlength="50"></td></tr>
</table>
<input type="submit" value="Change Password" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/>
<input type="button" value="Cancel" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onclick="document.location.href = 'home.php?<?php echo returnRequiredParams($session)?>'"/>
</form>
</div>
<?php
// Start footer section
include('footer.php'); ?>
</body>
</html>
