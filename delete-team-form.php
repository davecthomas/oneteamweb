<?php  
// Only admins can execute this script. Header.php enforces this.
$isadminrequired = true;
$title= " Delete Team" ;
include('header.php'); 

$bError = false;
if (!isUser($session, Role_ApplicationAdmin)) $bError = true;

if (isset($_GET["id"])) {
	$id = (int)(getCleanInput($_GET["id"]));
} else {
	$id= TeamID_Undefined;
}
$dbh = getDBH($session);  

$strSQL = "SELECT teams.name, teams.id FROM teams ORDER BY name;";
$pdostatement = $dbh->prepare($strSQL);
$pdostatement->execute();

$teamResults = $pdostatement->fetchAll();
$rowCount = 0;	
$numRows = count($teamResults); 
?>
<h3><?php echo $title?></h3>
<?php 
// If we were directed here from a previously deleted team, show completion status text 
if (isset($_GET["done"])) {
	if (strcmp($_GET["done"], "1") == 0) {
		if (isset($_GET["deletedteam"])) {
			$deleteduser = $_GET["deletedteam"];
		} else {
			$deleteduser = "";
		}
		echo "<p>Team '" . $deleteduser . "' successfully deleted.</p>\n";
	} else {
		echo "<p>There was an error deleting the team '" . $deleteduser . "'. Try again.</p>\n";
	}
}
	
// If no members, tell them so and don't display the form
if ($numRows == 0) { 
	echo "<p>No teams exist.<br>\n";
	echo '<a href="/1team/new-team-form.php?' . returnRequiredParams($session) . '">Create a team</a></p>';
	echo "\n";
	$bOkForm = false;
} else { 
	$bOkForm = true;
} 
if ($bOkForm ){ ?>
<form name="deleteform" action="/1team/delete-team.php" method="post">
<?php buildRequiredPostFields($session) ?>
<p>
<table class="noborders">
<tr>
<td class="bold">Team name:</td>
<td><select name="id">
<?php  
	if ( $id == 0 ) {
		echo("<option value=\"0\" selected>Select team...</option>");
	}
	while($rowCount < $numRows) { 
		echo "<option value=\"";
		echo $teamResults[$rowCount]["id"];
		echo "\"";
		if ( $id== $teamResults[$rowCount]["id"] ) {
			echo("selected");
		}
		echo ">";
		echo $teamResults[$rowCount]["name"];
		echo "</option>\n";
		$rowCount++;
	} ?>
</select></td>
</tr>		
<tr><td><input type="button" value="Delete" class="btn" onmouseover="this.className='btn btnhover'" onmouseout="this.className='btn'" onClick="confSubmit(this.form);"></td>
</tr>
</table>
</form>
<script type="text/javascript">
function confSubmit(form) {
	if (confirm("Are you sure you want to delete this team?")) {
		document.forms['deleteform'].submit();
	}
}
</script>
<?php 
}
// Start footer section
include('footer.php'); ?>
</body>
</html>