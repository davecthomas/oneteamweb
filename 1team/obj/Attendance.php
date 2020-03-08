<?php
include_once('DbObject.php');
/**
 * Support a user object as stored in the DB
 *
 * @author dthomas
 */
class Attendance extends DbObject {

	private $userid;
	private $attendancedate;
	private $eventid;
	private $type;

	const AttendanceLogTypeUndefined = 0;
	const AttendanceLogTypeRegular = 1;
	const AttendanceLogTypeGuestPass = 2;
	const AttendanceLogTypeGiftCard = 3;
	const AttendanceLogTypeEPunch = 4;

	// Pull an existing object
	function __construct( $session, $id = DbObject::DbID_Undefined) {
		parent::__construct($session);
		$this->id = $id;

		if ($id != DbObject::DbID_Undefined) {
			$this->initRecord( );

			if (is_array($this->dbrecord)){
				$this->id = $this->dbrecord["id"];
				$this->userid = $this->dbrecord["memberid"];
				$this->attendancedate = $this->dbrecord["attendancedate"];
				$this->eventid = $this->dbrecord["eventid"];
				$this->type = $this->dbrecord["type"];
			}
		}
	}

	function init( $userid, $attendancedate, $eventid, $type = AttendanceLogTypeUndefined){
		$this->userid = $userid;
		$this->attendancedate = $attendancedate;
		$this->eventid = $eventid;
		$this->type = $type;
		$this->isdirty = true;
	}

	private function initRecord( ){
		$strSQL = "SELECT * FROM attendance WHERE id = ? and teamid = ?;";
		$results = executeQuery( getConnectionFromSession($this->session), $strSQL, $bError, array($this->id, $this->teamid));
		if ($bError) return RC_PDO_Error;
		else $this->dbrecord = $results;
	}

	function commit(){
		if (($this->isdirty) && ($this->isValid())){
			$strSQL = "UPDATE attendance set memberid = ?, attendancedate = ?, eventid = ?, type = ? WHERE teamid = ? AND id = ?;";
			executeQuery($this->dbconn, $strSQL, $bError, array($this->userid, $this->attendancedate, $this->eventid, $this->type, $this->teamid, $this->id));
			if (!$bError) $this->isdirty = false;
		} else if ($this->id == DbObject::DbID_Undefined){
			// PostgreSQL $strSQL = "INSERT INTO attendance VALUES(?, ?, ?, DEFAULT, ?, ?) RETURNING id;";
			$strSQL = "INSERT INTO attendance VALUES(?, ?, ?, DEFAULT, ?, ?);";
			executeQuery($this->dbconn, $strSQL, $bError, array($this->userid, $this->attendancedate, $this->eventid, $this->teamid, $this->type));
			$strSQL = "SELECT LAST_INSERT_ID();";
			$this->id = executeQuery($this->dbconn, $strSQL, $bError);
			if ($bError) $this->id = null;
			else return $this->getDberrinfo();
		}
	}

	function getUserID(){
		return $this->userid;
	}

	function setAttendanceType($val){
		$this->type = $val;
		$this->isdirty = true;
	}
	function getAttendanceType(){
		return $this->type;
	}

}
?>
