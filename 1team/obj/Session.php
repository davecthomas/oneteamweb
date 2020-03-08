<?php
include_once('DbObject.php');
/**
 * TO DO - have this mirror session object in DB
 *
 * @author dthomas
 */
class Session extends DbObject {
	// Pull an existing object
	function __construct( $session, $id = DbObject::DbID_Undefined) {
		parent::__construct($session);
	}

	function init(){

	}

	function commit(){
		// +----------------+-------------+------+-----+---------------------+-----------------------------------------------+
		// | Field          | Type        | Null | Key | Default             | Extra                                         |
		// +----------------+-------------+------+-----+---------------------+-----------------------------------------------+
		// | id             | int         | NO   | PRI | NULL                | auto_increment                                |
		// | ipaddr         | char(16)    | YES  |     | NULL                |                                               |
		// | userid         | int         | YES  |     | NULL                |                                               |
		// | sessionkey     | char(8)     | YES  |     | NULL                |                                               |
		// | timecreated    | timestamp   | NO   |     | CURRENT_TIMESTAMP   | DEFAULT_GENERATED on update CURRENT_TIMESTAMP |
		// | timeexpires    | timestamp   | NO   |     | 0000-00-00 00:00:00 |                                               |
		// | login          | varchar(50) | YES  |     | NULL                |                                               |
		// | roleid         | int         | YES  |     | NULL                |                                               |
		// | fullname       | varchar(50) | YES  |     | NULL                |                                               |
		// | teamid         | int         | YES  |     | NULL                |                                               |
		// | isbillable     | tinyint(1)  | YES  |     | NULL                |                                               |
		// | status         | int         | YES  |     | NULL                |                                               |
		// | authsms        | int         | YES  |     | NULL                |                                               |
		// | authsmsretries | int         | YES  |     | NULL                |                                               |
		// +----------------+-------------+------+-----+---------------------+-----------------------------------------------+
	}

	public static function deleteStaleSessions($session){

		$strSQL = "delete from sessions where timeexpires < current_timestamp";
		$dbconn = getConnectionFromSession($session);
		$results = executeQuery($dbconn, $strSQL, $bError);

		if ($bError) return RC_SessionCleanupFail;
		else return RC_Success;
    }
}
?>
