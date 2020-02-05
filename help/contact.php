<?php 
$title="Contact";
include ("help-header.php"); ?>
<div class="helpboxtext">
<p><?php echo companyname?> can be reached at:<br>
Email: <a title="1teamweb email"><img border="0" src="/1team/img/1teamwebemail.gif" align="absbottom" alt="1teamweb email"></a>.<br>Sorry this email address link isn't clickable. You know how it is with spam nowadays.</p>
<p>Registered users: please use our <a href="/1team/feedback-form.php<?php buildRequiredParams($session)?>">feedback link</a> to report issues or send ideas to <?php echo companyname?>. </p>
</div>
<?php
// Start footer section
include('../footer.php'); ?>
</body>
</html>
