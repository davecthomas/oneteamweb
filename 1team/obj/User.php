<?php
include_once('DbObject.php');
/**
 * Support a user object as stored in the DB
 *
 * @author dthomas
 */
class User extends DbObject {

	const UserID_Guest = -1;
	const UserID_Undefined = DbObject::DbID_Undefined;
	const Username_Guest = "Guest";

	private $userid;
	private $firstname;
	private $lastname;
	private $url;
	private $email;
	private $smsphone;
	private $smsphoneemail;
	private $smsphonecarrier;

	// user account info
	private $status;
	private $isuseraccountdirty;

	function __construct( $session, $id= DbObject::DbID_Undefined) {
		parent::__construct($session);

		$this->id = $id;
		$this->isuseraccountdirty = false;
		if ($id == User::UserID_Guest){
			$this->userid = $id;
			$this->firstname = User::Username_Guest;
			$this->lastname = "";
			$this->status = UserAccountStatus_Guest;
	
		} else if ($id != DbObject::DbID_Undefined) {
			$this->initRecord( );

			if (is_array($this->dbrecord)){
				$this->id = $this->dbrecord["id"];
				$this->userid = $this->dbrecord["userid"];
				$this->firstname = $this->dbrecord["firstname"];
				$this->lastname = $this->dbrecord["lastname"];
				$this->status = $this->dbrecord["status"];
				$this->url = $this->dbrecord["url"];
				$this->email = $this->dbrecord["email"];
				$this->smsphone = $this->dbrecord["smsphone"];
				$this->smsphonecarrier = $this->dbrecord["smsphonecarrier"];
			}
		}
	}

	private function initRecord( ){
		$strSQL = "SELECT users.*, users.id AS userid, useraccountinfo.*, images.* FROM useraccountinfo, teams 
			RIGHT OUTER JOIN images RIGHT OUTER JOIN users ON users.imageid = images.id ON images.teamid = teams.id
			WHERE users.useraccountinfo = useraccountinfo.id AND users.id = ? and users.teamid = ?;";

		$pdostatement = $this->dbh->prepare($strSQL);
		$bError = !($pdostatement->execute(array($this->id, $this->teamid)));
		if ($bError) return RC_PDO_Error;
		else $this->dbrecord = $pdostatement->fetch();
	}

	function commit(){
		if (($this->isdirty) && ($id != User::UserID_Guest)&& ($id != User::UserID_Undefined)){
			$strSQL = "UPDATE users set firstname = ?, lastname = ?, email = ?, smsphone = ?, smsphonecarrier = ?
					WHERE teamid = ? AND id = ?;";
			$pdostatement = $this->dbh->prepare($strSQL);
			$bError = !($pdostatement->execute(array($this->firstname, $this->lastname, $this->email, $this->smsphone,
					$this->smsphonecarrier, $this->teamid, $this->id)));
			if (!$bError) $this->dirty = false;
		}
	}

	function sendEmail( $subject, $message, $fromemail, &$err){
		$err = "";

		$headers = "From: ".$fromemail."\r\nReply-To: ".$fromemail."\r\n";
		// Always set content-type when sending HTML email
		$headers .= "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
		//define the headers we want passed. Note that they are separated with \r\n

		$mailok = mail($this->getEmail(), $subject, $message , $headers);
		if (!$mailok){
			$bError = true;
			$err = "mail";
		}
		if (!$bError ) return RC_Success;
		else return RC_EmailFailed;

	}

	function sendText( $message, $fromemail, &$err){
		$err = "";

		$headers = "From: ".$fromemail."\r\nReply-To: ".$fromemail."\r\n";

		$mailok = mail($toemail, "", $message , $headers);
		if (!$mailok){
			$bError = true;
			$err = "mail";
		}
		if (!$bError ) return RC_Success;
		else return RC_EmailFailed;

	}

	function getUserID(){
		return $this->id;
	}

	function setFirstname($val){
		$this->firstname = $val;
		$this->isdirty = true;
	}
	function getFirstname(){
		return $this->firstname;
	}

	function setLastname($val){
		$this->lastname = $val;
		$this->isdirty = true;
	}
	function getLastname(){
		return $this->lastname;
	}

	function setStatus($val){
		$this->status = $val;
		$this->isuseraccountdirty = true;
	}
	function getAccountStatus(){
		return $this->status;
	}

	function setImageURL($val){
		$this->url = $val;
		$this->isdirty = true;
	}
	function hasImageURL(){
		return (!(is_null($this->url)) && (strlen($this->url)>0));
	}
	function getImageURL(){
		if (hasImageURL())
			return $this->url;
		else return "";
	}

	public function getUrl() {
		return $this->getImageUrl();
	}

	public function setUrl($url) {
		$this->setImageURL($url);
	}

	public function getEmail() {
		return $this->email;
	}

	public function setEmail($email) {
		if (isValidEmail($email)){
			$this->email = $email;
			$this->isdirty = true;
		} else
			return RC_EmailAddrInvalid;
	}

	public function getSmsPhone() {
		return $this->smsphone;
	}

	public function setSmsPhone($smsphone) {
		$this->smsphone = $smsphone;
		$this->isdirty = true;
		$this->setSmsphoneEmail($smsphone."@".getSmsCarrierEmail($this->getSmsphoneCarrier()));
	}

	public function getSmsphoneCarrier() {
		return $this->smsphonecarrier;
	}

	public function setSmsphoneCarrier($smsphonecarrier) {
		$this->smsphonecarrier = $smsphonecarrier;
	}

	public function getSmsphoneEmail() {
		if (!isset($this->smsphoneemail)){
			if (strlen($this->getSmsPhone())< MinLenSMSPhone) return "";
			else {
				$this->setSmsphoneEmail($this->getSmsPhone()."@".getSmsCarrierEmail($this->getSmsphoneCarrier()));
				return $this->smsphoneemail;
			}

		} else
			return $this->smsphoneemail;
	}

	public function setSmsphoneEmail($smsphoneemail) {
		$this->smsphoneemail = $smsphoneemail;
		// This setter doesn't "dirty" the object since it isn't persisted data
	}



}
?>
