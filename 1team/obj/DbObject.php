<?php
/**
 * Base class for DB objects
 *
 * @author dthomas
 */
class DbObject {
	const DbID_Undefined = 0;
	const DbErrorInfoSQL = 0;
	const DbErrorInfoText = 2;

	static $HelpText = "";

	protected $session;
	protected $dbconn;
	protected $teamid;
	protected $id;

	protected $dbrecord;
	protected $isdirty;
	protected $dberrinfo;

	function __construct( $session, $id = DbObject::DbID_Undefined) {
		$this->isdirty = false;
		$this->session = $session;
		$this->dbconn = getConnectionFromSession($session);
		$this->teamid = $session["teamid"];
		$this->id = $id;
	}

	function dump(){
		print_r($this);
	}

	function isValid(){
		return ($this->id != DbObject::DbID_Undefined);
	}

	static function isDbObject($obj){
		return $obj instanceof dbObject;
	}


	function getID(){
		return $this->id;
	}

	static function getHelp(){
		return self::$HelpText;
	}

	public function __toString(){
	    return sprintf('%d', $this->id);
	}

	public function getDberrinfo() {
		return $this->dberrinfo;
	}
/*
	dberrinfo Element	Information
	0	SQLSTATE error code (a five characters alphanumeric identifier defined in the ANSI SQL standard).
	1	Driver specific error code.
	2	Driver specific error message.
 */
	public function setDberrinfo($dbstatement) {
		$this->dberrinfo = $dbstatement->errorInfo();
	}

	public function getDbErrInfoString(){
		if (isset($this->dberrinfo[DbObject::DbErrorInfoText])){
			return $this->dberrinfo[DbObject::DbErrorInfoText];
		}
	}
	public function getDbErrInfoSQLCode(){
		if (isset($this->dberrinfo[DbObject::DbErrorInfoSQL])){
			return $this->dberrinfo[DbObject::DbErrorInfoSQL];
		}
	}


}
?>
