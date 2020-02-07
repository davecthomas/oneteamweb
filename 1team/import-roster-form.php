<?php
$isadminrequired = true;
$title= " Import Roster " ;
include('header.php');
// teamid depends on who is calling
if (isUser($session, Role_TeamAdmin)){
	if (isset($session["teamid"])){
		$teamid = $session["teamid"];
	}
} else {
	if (isset($_GET["teamid"])){
		$teamid = $_GET["teamid"];
	}
}?>
<h3><?php echo $title?></h3>
<div class="indented-group-noborder">
<form name="importform" action="import-roster.php" method="post" enctype="multipart/form-data">
<div class="helpbox">
<div class="helpboxtitle"><h5><a class="linkopacity" href="javascript:togglevis('uploadroster')">Help on roster importing<img src="/1team/img/a_collapse.gif" id="uploadroster_img" border="0"></a></h5></div>
<div class="showit" id="uploadroster">
<div class="helpboxtext">
<p>Upload new <?php echo $teamterms["termmember"] . "s"?> by selecting a comma-separated values (CSV) file with these required columns:
<ol>
<li>firstname</li>
<li>lastname</li>
<li>login - This is the unique sign-on name you assign to each of your <?php echo $teamterms["termmember"] . "s"?>. It must be at least <?php echo minlen_login?> characters long.</li>
<li>email</li>
</ol>
</p>
<p>Non-required columns that are also accepted:
<ol>
<li>address</li>
<li>address2</li>
<li>city</li>
<li>state</li>
<li>postalcode</li>
<li>smsphone - mobile phone</li>
<li>smscarrier - mobile phone carrier (company), can be one of:
	<ul>
		<li>alltel</li>
		<li>att</li>
		<li>boost</li>
		<li>cellularone</li>
		<li>nextel</li>
		<li>sprint</li>
		<li>tmobile</li>
		<li>verizon</li>
		<li>virgin</li>
		<li>quest</li>
	</ul></li>
<li>phone2</li>
<li>birthdate - [mm/dd/yyyy]</li>
<li>startdate - [mm/dd/yyyy]</li>
<li>referredby</li>
<li>notes</li>
<li>emergencycontact</li>
<li>ecphone1</li>
<li>ecphone2</li>
<li>gender - Acceptable values: [F, M]</li>
<li>isbillable - [0,1]</li>
<li>level - The name of the current promotion leve of the <?php echo $teamterms["termmember"]?>. <a href="manage-levels-form.php<?php buildRequiredParams($session) ?>&teamid=<?php echo $session["teamid"]?>">Levels must be defined</a> first.</li>
<li>promotiondate - The date the current level was attained [mm/dd/yyyy].</li>
</ol>
</p>
<p class="bold">Example:</p>
<p>
firstname,lastname,login,email,smsphone,smscarrier,gender<br>
Kyle,Brovlovski,kyle.brovlovski,kbrovlov@gmail.com,404-903-5463,verizon,M<br>
Eric,Cartman,eric.cartman,cartman@hotmail.com,404-903-5498,att,M
</p>
<p>The header row, which defines the columns, is required.</p>
<p>If successfully uploaded, <?php echo appname?> will automatically create new <?php echo $teamterms["termmember"] . "s"?>.</p>
</div></div></div>
<?php buildRequiredPostFields($session) ?>
<input type="hidden" name="teamid" value="<?php echo $teamid?>"/>
<p class="strong">Roster file&nbsp;<input type="file" name="roster" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'"/></p>
<p class="bold">Email password to new <?php echo $teamterms["termuser"] ?>s?&nbsp;<input type="checkbox" name="sendemail" value="1" checked="checked"></p>
<p>If the email password option is <i>not</i> selected, you will need to <a href="reset-password-form.php<?php buildRequiredParams($session) ?>">Reset <?php echo $teamterms["termmember"]?>&nbsp;passwords</a>
	for each new member in order to generate secure passwords and have them emailed to your new members.<br/>
	This step is required before your new members sign on.</p>
<input type="button" value="Upload roster" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onClick="confSubmit(this.form);"/>
</form>
</div>
<?php
// Error on import
if (isset($_GET["err"])){
	showError("Error", "Import failed. Error: " . $_GET["err"], "");
}// Start footer section
include('footer.php'); ?>
<script type="text/javascript">
function confSubmit(form) {
	if (document.importform.roster.value.length != 0){
		form.submit();;
	} else {
		alert("Click 'Choose File' to select a CSV file to import.");
	}
}
</script>
</body>
</html>
