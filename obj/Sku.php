<?php
include_once('DbObject.php');
/**
 * Support a SKU object as stored in the DB
 *
 * @author dthomas
 */
class Sku extends DbObject {
	const SkuID_Undefined = DbObject::DbID_Undefined;
	const NumEventsUnlimited = -1;
	const NumEventsUndefined = 0;

	private $name;
	private $programid;
	private $listorder;
	private $price;
	private $description;
	private $numevents;
	private $expires;

	function __construct( $session, $id= DbObject::DbID_Undefined) {
		parent::__construct($session);
		$this->teamid = $session["teamid"];
		$this->id = $id;
		if ($id != DbObject::DbID_Undefined) {
			$this->getSku( );

			$this->name = $this->dbrecord["name"];
			$this->programid = $this->dbrecord["programid"];
			$this->listorder = $this->dbrecord["listorder"];
			$this->price = $this->dbrecord["price"];
			$this->description = $this->dbrecord["description"];
			$this->numevents = $this->dbrecord["numevents"];
			$this->expires = $this->dbrecord["expires"];
		}
	}

	function getSku( ){
		// Now get the sum of the face value since this should be very interesting for guest passes and other give-aways
		$strSQL = "SELECT * FROM skus WHERE teamid = ? AND id = ?;";
		$pdostatement = $this->dbh->prepare($strSQL);
		$bError = ! $pdostatement->execute(array($this->teamid, $this->getID()));
		if ($bError) return RC_PDO_Error;
		else $this->dbrecord = $pdostatement->fetch();
	}

	function setName($name){
		$this->name = $name;
		$this->isdirty = true;
	}
	function getName(){
		return $this->name;
	}

	function setProgramID($programid){
		$this->programid = $programid;
		$this->isdirty = true;
	}
	function getProgramID(){
		return $this->programid;
	}

	function setListOrder($listorder){
		$this->listorder = $listorder;
		$this->isdirty = true;
	}
	function getListOrder(){
		return $this->listorder;
	}

	function setPrice($price){
		$this->price = $price;
		$this->isdirty = true;
	}
	function getPrice(){
		return $this->price;
	}

	function setDescription($description){
		$this->description = $description;
		$this->isdirty = true;
	}
	function getDescription(){
		return $this->description;
	}

	function setNumEvents($numevents){
		$this->numevents = $numevents;
		$this->isdirty = true;
	}
	function getNumEvents(){
		return $this->numevents;
	}

	function setExpires($expires){
		$this->expires = $expires;
		$this->isdirty = true;
	}
	function getExpires(){
		return $this->expires;
	}
}
?>
