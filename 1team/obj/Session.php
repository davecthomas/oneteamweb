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

	public static function deleteStaleSessions($session){

		$strSQL = "delete from sessions where timeexpires < current_timestamp";
		$dbconn = getConnection();
		$results = executeQuery($dbconn, $strSQL, $bError);

		if ($bError) return RC_SessionCleanupFail;
		else return RC_Success;
    }
}
?>
