<?php
class TestReservationInfo {
	var $dbtables = array (
			"recordTable" => "ods_test_reservation_record",
			"transactionTable" => "ods_test_reservation_transaction" 
	);
	var $userGroups = array(
			"studentGroup" => "ODS student",
			"staffGroup" => "ODS staff"
	);
	public static function Instance() {
		static $inst = null;
		if ($inst === null) {
			$inst = new TestReservationInfo ();
		}
		return $inst;
	}
	
	/**
	 * Private ctor so nobody else can instance it
	 */
	private function __construct() {
		verifyBasicDatabaseTableSetup();
	}
	function verifyBasicDatabaseTableSetup() {
		global $DB;
		$requiredTables = array (
				"ods_test_reservation_record",
				"ods_test_reservation_transaction" 
		);
		// Check table exist or not
		$tableRecord = $DB->get_records_sql ( "SHOW TABLES LIKE 'ods_test_reservation_%'" );
		$obtainTables = array_keys ( $tableRecord );
		foreach ( $requiredTables as $requiredTable ) {
			if (! in_array ( $requiredTable, $obtainTables )) {
				die ( "Database Configuration Error! \n" . "Cannot find required tables." );
			}
		}
	}
}