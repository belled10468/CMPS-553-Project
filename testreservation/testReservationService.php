<?php
if (! array_key_exists ( "onSite", $_POST ) || strpos ( $_SERVER ['REQUEST_URI'], "testReservationTable" ) === false) {
	echo $_SERVER ['REQUEST_URI'];
	//die ( "Unallowed Operation!" );
}

require_once ('../config.php');
require_once ('./testReservationUtil.php');
$PAGE->set_url ( $CFG->wwwroot . '/testreservation/testReservationService.php' );
$testReservationRecordTableName = "ods_test_reservation_record";
$testReservationTransactionRecordTableName = "ods_test_reservation_transaction";

$tableRecord = $DB->get_records_sql ( "SHOW TABLES LIKE 'ods_test_reservation_%'" );
$obtainTables = array_keys ( $tableRecord );

if (in_array ( $testReservationRecordTableName, $obtainTables ) && in_array ( $testReservationTransactionRecordTableName, $obtainTables )) {
	if (array_key_exists ( "submitType", $_POST )) {
		$submitType = $_POST ['submitType'];
		switch ($submitType) {
			case "update" :
				$previousReservationId = $_POST ['previousReservationId'];
				$sql = "UPDATE `$testReservationRecordTableName` SET is_vaild = 0 WHERE id = $previousReservationId";
				$DB->execute ( $sql );
				directSQLInsertRR ( $_POST );
				directSQLInsertRT ( $submitType, $_POST );
				// $record = createReservationRecordObj ( $_POST );
				// $lastinsertid = $DB->insert_record ( $testReservationRecordTableName, $record, false );
				// $tansaction = createReservationTransactionObj ( $submitType, $_POST );
				// $lastinsertid = $DB->insert_record ( $testReservationTransactionRecordTableName, $tansaction, false );
				break;
			case "delete" :
				$deletedReservationId = $_POST ['deletedReservationId'];
				$sql = "UPDATE `$testReservationRecordTableName` SET is_vaild = 0 WHERE id = $deletedReservationId";
				$DB->execute ( $sql );
				directSQLInsertRT ( $submitType, $_POST );
				// $record = createReservationRecordObj ( $_POST );
				// $lastinsertid = $DB->insert_record ( $testReservationRecordTableName, $record, false );
				// $tansaction = createReservationTransactionObj ( $submitType, $_POST );
				echo "success";
				break;
		}
	}
} else {
	echo "Database Configuration Error! ";
}