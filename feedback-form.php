<?php
// The title is set here and rendered in header.php
$title= "Send Feedback" ;
include('header.php'); ?>
<h3><?php echo $title . " from " . roleToStr($session["roleid"],$teamterms)?>&nbsp;<?php echo $session["fullname"]?></h3>
<div class="indented-group-noborder">
<form action="/1team/feedback.php" method="post">
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $session["teamid"]?>">
<table class="noborders">
<tr><td><b>Feedback Summary</b></td>
<td><input type="text" value="" maxlength="64" size="64" name="summary"></td></tr>
<tr><td><b>Details</b></td>
<td><p><textarea rows="5" cols="120" name="details" wrap="hard"></textarea></p></td></tr>
</table>
<input type="submit" value="Send" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'">
</form>
</div>
<?php
// On success, we get redirected back from team-props with done parm, triggering this message
if (isset($_GET["err"])){
	showError("Error", "The feedback was not sent successfully.", "");
} else if (isset($_GET["done"])){
	showMessage("Success", "The feedback was saved successfully.");
} 
// Start footer section
include('footer.php'); ?>
</body>
</html>
