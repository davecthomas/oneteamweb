<?php
include_once('DbObject.php');
/**
 * Support an array of attendance consoles per team as stored in the DB
 *
 * @author dthomas
 */

class AttendanceConsoles extends DbObject{

	// This is an array of AttendanceConsole objects, with the index being the attendanceconsole DB ID
	private $attendanceconsoles = array();
	private $numconsoles;

	// This operates on a list of AttendanceConsole objects for a team, so doesn't take the normal ID
	function __construct( $session, $teamid = DbObject::DbID_Undefined) {
		parent::__construct($session);

		$this->teamid = $teamid;
		$this->numconsoles = 0;

		if ($teamid != DbObject::DbID_Undefined) {
			$this->getRecord( );

			if (is_array($this->dbrecord)){
				foreach ($this->dbrecord as $attendanceconsoleRecord) {
					// Construct an empty attendance console object
					$attendanceconsole = new AttendanceConsole($session, DbObject::DbID_Undefined);
					// initialize it from our array of results into our local array
					$attendanceconsole->init(
						   $attendanceconsoleRecord['name'], $attendanceconsoleRecord['ip'], $attendanceconsoleRecord['id']);
					$this->add($attendanceconsole);
				}
			}
		}
	}

	private function getRecord( ){
		$strSQL = "SELECT * FROM attendanceconsoles WHERE teamid = ?;";
		executeQuery($this->dbh, $strSQL, $bError, array($this->teamid)));
		if ($bError) return RC_PDO_Error;
		else $this->dbrecord = $pdostatement->fetchAll();
	}

	function getNumAttendanceConsoles(){
		return count($this->attendanceconsoles);
	}

	function add($attendanceconsole){
		if ($attendanceconsole instanceof AttendanceConsole){
			$this->attendanceconsoles[$this->numconsoles++] = $attendanceconsole;
		}
	}

	function get($id){
		if (isset($this->attendanceconsoles[$id])) {
			$attendanceconsole = $this->attendanceconsoles[$id];
			if ($attendanceconsole instanceof AttendanceConsole)
				return $attendanceconsole;
		}
	}

	public function getAttendanceConsoles() {
		return $this->attendanceconsoles;
	}

	function remove($id){
		if (isset($this->attendanceconsoles[$id])) {
			$attendanceconsole = $this->attendanceconsoles[$id];
			$attendanceconsole->remove();

			unset( $this->attendanceconsoles[$id]);
		}
	}

	// Find an IP address in the current set of attendance consoles
	function find($ip){
		foreach ($this->attendanceconsoles as $ac){
			if ($ac->isIPAddressEquivalent($ip)) return $ac;
		}
		return false;
	}


}


/*
 * Represents an attendanceconsole record
 * Note: to create an empty object, then initialize it, do this:
 * $foo = new Bar($session, DbObject::DbID_Undefined)
 * $foo->init( $x, ...);
 */
class AttendanceConsole extends DbObject {
	const MaxLenIPAddress = 16;
	private $name;
	private $ip;

	static $HelpText = "Certain administrative features can be restricted to a select list of computers,
		such as your club's attendance console and your PDA for mobile attendance recording. ";

	static function getHelp(){
		return self::$HelpText;
	}

	// Determine if (the user is running the applicatin from the IP Address of the administrative console
	static function isAttendanceConsole( $session){
		$acs = new AttendanceConsoles($session, $session["teamid"]);
		$ac = $acs->find($_SERVER["REMOTE_ADDR"]);
		if (($ac instanceof AttendanceConsole) && ($ac->isValid()))
			return true;
		else
			return false;
	}

	// Pull an existing object
	function __construct( $session, $id = DbObject::DbID_Undefined) {
		parent::__construct($session);
		$this->id = $id;

		if ($id != DbObject::DbID_Undefined) {
			$this->getRecord( );

			if (is_array($this->dbrecord)){
				$this->init( $this->dbrecord["name"], $this->dbrecord["ip"], $this->dbrecord["id"]);
			}
		}
	}

	// Initialize object without a DB call
	function init( $name, $ip, $id = DbObject::DbID_Undefined ){
		$this->name = $name;
		$this->ip = $ip;
		$this->id = $id;
	}

	function remove(){
		$strSQL = "DELETE FROM attendanceconsoles WHERE id = ? and teamid = ?;";

		executeQuery($this->dbh, $strSQL, $bError, array($this->id, $this->teamid)));
		if ($bError) return RC_PDO_Error;
		// Invalidate object
		else $this->id = DbObject::DbID_Undefined;
	}

	private function getRecord( ){
		$strSQL = "SELECT * FROM attendanceconsoles WHERE id = ? and teamid = ?;";
		executeQuery($this->dbh, $strSQL, $bError, array($this->id, $this->teamid)));
		if ($bError) return $this->getDberrinfo();
		else $this->dbrecord = $pdostatement->fetch();
	}

	function commit(){
		if (($this->isdirty) && ($this->isValid())){
			$strSQL = "UPDATE attendanceconsoles set name = ?, ip = ? WHERE teamid = ? AND id = ?;";
			executeQuery($this->dbh, $strSQL, $bError, array($this->name, $this->ip, $this->teamid, $this->id)));
			if (!$bError) $this->isdirty = false;
		} else if ($this->id == DbObject::DbID_Undefined){
			$strSQL = "INSERT INTO attendanceconsoles VALUES(DEFAULT, ?, ?, ?) RETURNING id;";
			$this->id = executeQueryFetchColumn($this->dbh, $strSQL, $bError, array($this->name, $this->ip, $this->teamid)));
			if ($bError) $this->id = null;
			else return $this->getDberrinfo();
		}
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->isdirty = true;
		$this->name = $name;
	}

	public function getIp() {
		return $this->ip;
	}

	public function setIp($ip) {
		$this->ip = $ip;
		$this->isdirty = true;
	}

	public function isIPAddressEquivalent($ip){
		return isIPAddressEquivalent($ip, $this->ip, 24);
	}

}
?>
