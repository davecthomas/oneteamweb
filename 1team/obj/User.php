<?php
include_once('DbObject.php');
include_once('Mail1t.php');
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

		$this->dbrecord = executeQuery( getConnectionFromSession($this->session),array($this->id, $this->teamid));
		if ($bError) return RC_PDO_Error;
	}

	function init(
		// id                
		$firstname,         
		$lastname,          
		$startdate,         
		$address,           
		$city,              
		$state,             
		$postalcode,        
		$smsphone,          
		$phone2,            
		$login,             
		$birthdate,         
		$referredby,        
		$notes,             
		$coachid,           
		$emergencycontact,  
		$ecphone1,          
		$ecphone2,          
		$gender,            
		$stopdate,          
		$stopreason,        
		$teamid,            
		$roleid,            
		$address2,          
		$useraccountinfo,   
		$salt = "",              
		$passwd = "",           
		$imageid = null,        
		$smsphonecarrier = 0,   
		$ipaddr = "",            
		$timelockoutexpires = null
	){
		$this->$firstname = $firstname;         
		$this->$lastname = $lastname;         
		$this->$startdate =$startdate;
		$this->$address = $address;
		$this->$city = $city;
		$this->$state = $state;     
		$this->$postalcode = $postalcode;   
		$this->$smsphone = $smsphone;
		$this->$phone2 = $phone2;
		$this->$login = $login; 
		$this->$birthdate = $birthdate;   
		$this->$referredby = $referredby;
		$this->$notes = $notes;
		$this->$coachid = $coachid;   
		$this->$emergencycontact = $emergencycontact;
		$this->$ecphone1 = $ecphone1;
		$this->$ecphone2 = $ecphone2;
		$this->$gender = $gender;
		$this->$stopdate = $stopdate; 
		$this->$stopreason = $stopreason;
		$this->$teamid = $teamid;
		$this->$roleid = $roleid; 
		$this->$address2 = $address2; 
		$this->$useraccountinfo = $useraccountinfo;
		$this->$salt = $salt;
		$this->$passwd = $passwd;     
		$this->$imageid = $imageid; 
		$this->$smsphonecarrier = $smsphonecarrier;
		$this->$ipaddr = $ipaddr;
		$this->$timelockoutexpires = $timelockoutexpires;
 	// +--------------------+--------------+------+-----+-------------------+-----------------------------------------------+
 	// | Field              | Type         | Null | Key | Default           | Extra                                         |
 	// +--------------------+--------------+------+-----+-------------------+-----------------------------------------------+
 	// | id                 | int          | NO   | PRI | NULL              | auto_increment                                |
 	// | firstname          | varchar(50)  | YES  |     | NULL              |                                               |
 	// | lastname           | varchar(50)  | YES  |     | NULL              |                                               |
 	// | startdate          | date         | YES  |     | NULL              |                                               |
 	// | address            | varchar(254) | YES  |     | NULL              |                                               |
 	// | city               | varchar(50)  | YES  |     | NULL              |                                               |
 	// | state              | varchar(20)  | YES  |     | NULL              |                                               |
 	// | postalcode         | varchar(20)  | YES  |     | NULL              |                                               |
 	// | smsphone           | varchar(30)  | YES  |     | NULL              |                                               |
 	// | phone2             | varchar(30)  | YES  |     | NULL              |                                               |
 	// | login              | varchar(50)  | NO   |     | NULL              |                                               |
 	// | birthdate          | date         | YES  |     | NULL              |                                               |
 	// | referredby         | varchar(50)  | YES  |     | NULL              |                                               |
 	// | notes              | text         | YES  |     | NULL              |                                               |
 	// | coachid            | int          | YES  |     | NULL              |                                               |
 	// | emergencycontact   | varchar(50)  | YES  |     | NULL              |                                               |
 	// | ecphone1           | varchar(50)  | YES  |     | NULL              |                                               |
 	// | ecphone2           | varchar(30)  | YES  |     | NULL              |                                               |
 	// | gender             | char(1)      | YES  |     | NULL              |                                               |
 	// | stopdate           | date         | YES  |     | NULL              |                                               |
 	// | stopreason         | varchar(80)  | YES  |     | NULL              |                                               |
 	// | teamid             | int          | YES  |     | NULL              |                                               |
 	// | roleid             | int          | YES  |     | NULL              |                                               |
 	// | address2           | varchar(80)  | YES  |     | NULL              |                                               |
 	// | useraccountinfo    | int          | YES  |     | NULL              |                                               |
 	// | salt               | char(9)      | YES  |     | NULL              |                                               |
 	// | passwd             | varchar(64)  | YES  |     | NULL              |                                               |
 	// | imageid            | int          | YES  |     | NULL              |                                               |
 	// | smsphonecarrier    | varchar(48)  | YES  |     | NULL              |                                               |
 	// | ipaddr             | char(16)     | YES  |     | NULL              |                                               |
 	// | timelockoutexpires | timestamp    | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED on update CURRENT_TIMESTAMP |
 	// +--------------------+--------------+------+-----+-------------------+-----------------------------------------------+
	}

	function commit(){
		if (($this->isdirty) && ($id != User::UserID_Guest)&& ($id != User::UserID_Undefined)){
			$strSQL = "UPDATE users set firstname = ?, lastname = ?, email = ?, smsphone = ?, smsphonecarrier = ?
					WHERE teamid = ? AND id = ?;";
			executeQuery( getConnectionFromSession($this->session),array($this->firstname, $this->lastname, $this->email, $this->smsphone,
					$this->smsphonecarrier, $this->teamid, $this->id));
			if (!$bError) $this->dirty = false;
		}
	}

	function sendEmail( $subject, $message, $fromemail, &$err){
		$mailer = new Mail1t($session);
		$bError = $mailer->mail($this->getEmail(), $subject, $message);
		if (!$mailer->statusok()){
			$err = $mailer->statuscode;
		}
		if (!$bError ) return RC_Success;
		else return RC_EmailFailed;
	}

	function sendText( $message, $fromemail, &$err){
		$mailer = new Mail1t($session);
		$bError = $mailer->mail($this->getEmail(), "", $message);
		if (!$mailer->statusok()){
			$err = $mailer->statuscode;
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
