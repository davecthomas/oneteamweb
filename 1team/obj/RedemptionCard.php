<?php
include_once('DbObject.php');
/**
 * Support a redemptioncard object as stored in the DB
 *
 * @author dthomas
 */
class RedemptionCard extends DbObject {

	const TypeUndefined = Attendance::AttendanceLogTypeUndefined;
	const TypeRegular = Attendance::AttendanceLogTypeRegular;
	const TypeGuestPass= Attendance::AttendanceLogTypeGuestPass;
	const TypeGiftCard = Attendance::AttendanceLogTypeGiftCard;
	const TypeEPunch = Attendance::AttendanceLogTypeEPunch;

	const RecipientGroupUndefined = 0;
	const RecipientGroupAllActiveMembers = 1;		// Everyone
	const RecipientGroupArbitrarySelection = 2;		// Manually selected from list
	const RecipientGroupNewMembers = 3;			// People joined recently
	const RecipientGroupNonParticipants = 4;		// Those who do not have an active (non-expired) order within a given program
	const RecipientGroupActiveParticipants = 5;		// Those who do have an active (non-expired) order within a given program
	const RecipientGroupRecentlyExpired = 6;		// People who have recently had a given SKU expire
	const RecipientGroupPastMembers = 7;			// Inactive members
	const RecipientGroupGuest = 8;				// Guests - non-members

	// Returns an array of supported Redemption Cards
	static function getRedemptionCardTypes(){
		return array(RedemptionCard::TypeUndefined => "undefined", RedemptionCard::TypeGiftCard => "Gift Certificate", RedemptionCard::TypeGuestPass => "Guest Pass", RedemptionCard::TypeEPunch => "Electronic Punch Card");
	}

	private $userid;
	private $skuid;
	private $createdate;
	private $amountpaid;
	private $numeventsremaining;
	private $description;
	private $paymentmethod;
	private $expires;
	private $type;
	private $facevalue;
	private $code;


	function __construct( $session, $id = DbObject::DbID_Undefined, $code = ""){
		parent::__construct($session);
		if ($id != DbObject::DbID_Undefined)
			$this->initLocals( $this->getRecord($id));
		else if (strlen($code)>0)
			$this->initLocals( $this->getRecordFromCode($code));
		// if neither branch executes, we basically have a useless, harmless empty object, so no need to error

	}
	function initFromCode( $barcode) {
		initLocals( $this->getRecordFromCode($barcode));
	}

	private function initLocals($dbrecord){
		if (is_array($dbrecord)){
			$this->dbrecord = $dbrecord;
			$this->id = $this->dbrecord["id"];
			$this->userid = $this->dbrecord["userid"];
			if (empty($this->dbrecord["skuid"])) $this->skuid = Sku::SkuID_Undefined;
			else $this->skuid = $this->dbrecord["skuid"];
			$this->createdate = $this->dbrecord["createdate"];
			$this->amountpaid = $this->dbrecord["amountpaid"];
			$this->desciption = $this->dbrecord["description"];
			$this->numeventsremaining = $this->dbrecord["numeventsremaining"];
			$this->description = $this->dbrecord["description"];
			$this->paymentmethod = $this->dbrecord["paymentmethod"];
			$this->expires = $this->dbrecord["expires"];
			$this->type = $this->dbrecord["type"];
			$this->facevalue = $this->dbrecord["facevalue"];
			$this->code = $this->dbrecord["code"];
		}
	}

	private function getRecord( $id){
		// Now get the sum of the face value since this should be very interesting for guest passes and other give-aways
		$strSQL = "SELECT * FROM redemptioncards WHERE teamid = ? AND id = ?;";
		$this->dbrecord = executeQuery( getConnectionFromSession($this->session), array($this->teamid, $id)));
		if ($bError) return RC_PDO_Error;
		else return $this->dbrecord ;
	}


	private function getRecordFromCode( $barcode){
		// Now get the sum of the face value since this should be very interesting for guest passes and other give-aways
		$strSQL = "SELECT * FROM redemptioncards WHERE teamid = ? AND code = ?;";
		$this->dbrecord = executeQuery( getConnectionFromSession($this->session), array($this->teamid, $barcode)));
		if ($bError) return RC_PDO_Error;
		else return $this->dbrecord ;
	}

	function commit(){
		$bError = false;
		if ($this->isdirty){
			// Note we don't update the code. This is because the barcode is based on the redemptioncardid, the teamid, and the type, which are immutable
			// by definition in order to guarantee the barcode doesn't change for an existing card
			$strSQL = "UPDATE redemptioncards SET description = ?, numeventsremaining = ?, expires=?, facevalue=?, paymentmethod=?, userid=?, skuid=?,
					createdate=?, amountpaid=? WHERE teamid = ? AND id = ?;";
			executeQuery( getConnectionFromSession($this->session),array($this->getDescription(), $this->getNumEventsRemaining(), $this->getExpires(), $this->getFaceValue(),
				$this->getPaymentMethod(), $this->userid, $this->getSkuID(), $this->getCreateDate(), $this->getAmountPaid(), $this->teamid, $this->id));
			if (!$bError) $this->isdirty = false;
		}
		if ($bError) return RC_PDO_Error;
		else return RC_Success;
	}

	function setDescription($description){
		$this->description = $description;
		$this->isdirty = true;
	}
	function getDescription(){
		return $this->description;
	}

	function setNumEventsRemaining($val){
		$this->numeventsremaining = $val;
		$this->isdirty = true;
	}
	function getNumEventsRemaining(){
		return $this->numeventsremaining;
	}

	function setPaymentMethod($val){
		$this->paymentmethod = $val;
		$this->isdirty = true;
	}
	function getPaymentMethod(){
		return $this->paymentmethod;
	}
	function setExpires($expires){
		$this->expires = $expires;
		$this->isdirty = true;
	}
	function getExpires(){
		return $this->expires;
	}

	function setUserID($val){
		$this->userid = $val;
		$this->isdirty = true;
	}
	function getUserID(){
		return $this->userid;
	}


	function setSkuID($val){
		$this->skuid = $val;
		$this->isdirty = true;
	}
	function getSkuID(){
		return $this->skuid;
	}

	function setCreateDate($val){
		$this->createdate = $val;
		$this->isdirty = true;
	}
	function getCreateDate(){
		return $this->createdate;
	}

	function setAmountPaid($val){
		$this->amountpaid = $val;
		$this->isdirty = true;
	}
	function getAmountPaid(){
		return $this->amountpaid;
	}

	function setType($val){
		$this->type = $val;
		$this->isdirty = true;
	}
	function getType(){
		return $this->type;
	}

	function setFaceValue($val){
		$this->facevalue = $val;
		$this->isdirty = true;
	}
	function getFaceValue(){
		return $this->facevalue;
	}

	function setCode($val){
		$this->code = $val;
		$this->isdirty = true;
	}
	function getCode(){
		return $this->code;
	}
}
?>
