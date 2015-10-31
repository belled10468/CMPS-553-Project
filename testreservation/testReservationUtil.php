<?php
function verifyODSIdentity() {
	return true;
}
function directSQLInsertRR($postArray) {
	global $DB;
	global $USER;
	
	// Add myql filter
	$sql = "INSERT INTO ods_test_reservation_record (`register_id`, `class`, `instructors`,
		`test_type`, `original_test_time`, `test_date`, `test_start_time`,
		`test_duration`, `preference`, `accommodation`,
		`return_type`, `is_valid`) VALUES (" . "'" . $USER->id . "'," . "'" . (array_key_exists ( "class", $postArray ) ? $postArray ['class'] : "") . "'," . "'" . (array_key_exists ( "instructor", $postArray ) ? $postArray ['instructor'] : "") . "'," . "'" . (array_key_exists ( "testType", $postArray ) ? $postArray ['testType'] : "") . "'," . "'" . (array_key_exists ( "originalTestDate", $postArray ) ? $postArray ['originalTestDate'] . " " : "") . (array_key_exists ( "originalTestTime", $postArray ) ? $postArray ['originalTestTime'] : "") . "'," . "'" . (array_key_exists ( "reservedTestDate", $postArray ) ? $postArray ['reservedTestDate'] : "") . "'," . "'" . (array_key_exists ( "reservedTestTime", $postArray ) ? $postArray ['reservedTestTime'] : "") . "'," . "'" . (array_key_exists ( "testLength", $postArray ) ? $postArray ['testLength'] : "") . "'," . "'" . (array_key_exists ( "preference", $postArray ) ? $postArray ['preference'] : "") . "'," . "'" . (array_key_exists ( "requiredResources", $postArray ) ? (is_array ( $postArray ['requiredResources'] ) ? implode ( ",", $postArray ['requiredResources'] ) : $postArray ['requiredResources']) : "") . "'," . "'" . (array_key_exists ( "returnType", $postArray ) ? $postArray ['returnType'] : "") . "'," . "1" . ")";
	$DB->execute ( $sql );
}
function directSQLInsertRT($submitType, $postArray) {
	global $DB;
	global $USER;
	$sql = "INSERT INTO ods_test_reservation_transaction (`action`, `executor`, `data`) VALUES (" . "'" . $submitType . "'," . "'" . $USER->id . "'," . "'" . json_encode ( $postArray ) . "'" . ")";
	$DB->execute ( $sql );
}
function createReservationTransactionObj($submitType, $postArray) {
	global $USER;
	$transaction = new stdClass ();
	$transaction->action = $submitType;
	$transaction->executor = $USER->id;
	$transaction->data = json_encode ( $postArray ); // Persitence remember?
	return $transaction;
}
function createReservationRecordObj($postArray) {
	global $USER;
	$record = new stdClass ();
	$resord->register_id = $USER->id;
	$resord->class = $postArray ['class'];
	$resord->instructors = $postArray ['instructor'];
	$resord->test_type = $postArray ['testType'];
	$resord->test_date = $postArray ['reservedTestDate'];
	$resord->test_start_time = $postArray ['reservedTestTime'];
	$resord->test_duration = $postArray ['testLength'];
	$resord->is_valid = 1; // valid
	return $record;
}
// Date | Subject | Start time| Student CLID | Name | Duration | Finish time | Preference| Accommodation | Ret type
function formatRecordArray($recordArray, $staffView = true) { // Flexible?
	global $USER;
	$formatedRecordArray = array ();
	foreach ( $recordArray as $k => $record ) {
		// var_dump($record);
		$recordId = $record->id;
		$userId = $record->register_id;
		$formatedRecordArray [$recordId] = array ();
		$formatedRecordArray [$recordId] ['Date'] = $record->test_date;
		$formatedRecordArray [$recordId] ['Subject'] = $record->coursename;
		$formatedRecordArray [$recordId] ['Start time'] = $record->test_start_time;
		// Current User Identify
		if ($staffView) {
			$formatedRecordArray [$recordId] ['Student CLID'] = $record->username;
			$formatedRecordArray [$recordId] ['Name'] = $record->firstname . " " . (strlen ( $record->middlename ) > 0 ? $record->middlename . " " : "") . $record->lastname;
		}
		
		$formatedRecordArray [$recordId] ['Duration'] = $record->test_duration;
		
		$formatedRecordArray [$recordId] ['Finish time'] = getTestFinishTime ( $record->test_start_time, $record->test_duration );
		
		$formatedRecordArray [$recordId] ['Preference'] = $record->preference;
		$formatedRecordArray [$recordId] ['Accommodation'] = (is_array ( $record->accommodation ) ? implode ( ",", $record->accommodation ) : $record->accommodation);
		$formatedRecordArray [$recordId] ['Ret type'] = $record->return_type;
	}
	return $formatedRecordArray;
}
function recordSetToArray($recordset) {
	$recordArray = array ();
	if ($recordset->valid ()) {
		foreach ( $recordset as $record ) {
			$recordId = $record->id;
			$recordArray [$recordId] = $record;
		}
	}
	return $recordArray;
}
function getTestFinishTime($startTime, $testLength) {
	$timeParts = explode ( ":", $startTime );
	$hours = intval ( $timeParts [0] );
	$mins = intval ( $timeParts [1] );
	$testLengthMin = intval ( $testLength );
	return sprintf ( '%02d:%02d', ($hours + intval ( $testLengthMin / 60 )), ($mins + $testLengthMin % 60) );
}
function getRecordSet($isStaff, $recordId = Null) {
	global $USER;
	global $DB;
	if ($isStaff) {
		$sql = "SELECT t.`id`, t.`register_id`,
		u.`username`, u.`firstname`, u.`middlename`, u.`lastname`,
		t.`class`, c.`fullname` as coursename, t.`instructors`,
		t.`test_type`,  t.`original_test_time`, t.`test_date`, t.`test_start_time`,
		t.`test_duration`, t.`preference`, t.`accommodation`,
		t.`return_type`, t.`created_date`
		FROM `ods_test_reservation_record` t " . "JOIN mdl_user u ON u.id = t.`register_id`" . "JOIN mdl_course c ON c.id = t.`class`" . "WHERE t.is_valid = 1 ";
	} else {
		$sql = "SELECT t.`id`, t.`register_id`,
		t.`class`, c.`fullname` as coursename, t.`instructors`,
		t.`test_type`,  t.`original_test_time`, t.`test_date`, t.`test_start_time`,
		t.`test_duration`, t.`preference`, t.`accommodation`,
		t.`return_type`, t.`created_date`
		FROM `ods_test_reservation_record` t " . "JOIN mdl_course c ON c.id = t.`class`" . "WHERE t.is_valid = 1 AND t.register_id = " . $USER->id;
	}
	if ($recordId != Null) {
		$sql .= " AND t.`id` = $recordId ";
	}
	
	$sql .= " ORDER BY t.`created_date`";
	$recordset = $DB->get_recordset_sql ( $sql );
	return $recordset;
}
function formatRecordIntoForm($record) {
	if ($record['original_test_time'] != Null) {
		$original_test_time = explode ( " ", $record['original_test_time'] );
		$record['original_test_date'] = $original_test_time [0];
		$record['original_test_time'] = $original_test_time [1];
	}else{
		$record['original_test_date'] = '';
	}
	if ($record['accommodation'] != Null) {
		$record['accommodation'] = valueToIndexArray ( $record['accommodation'], "," );
	}else{
		$record['accommodation'] = array();
	}
	if ($record['return_type'] != Null) {
		$record['return_type'] = valueToIndexArray ( $record['return_type'], "," );
	}
	return $record;
}
function valueToIndexArray($value, $delimiter) {
	$indexArray = array ();
	foreach ( explode ( $delimiter, $value ) as $v ) {
		$indexArray [$v] = 'checked';
	}
	return $indexArray;
}
function defaultValueApply($value, $type, $option = NULL,  $default = NULL) {
	global $_POST;
	if (array_key_exists ( "submitType", $_POST )) {
		switch ($type) {
			case "text" :
				echo " value='$value' ";
				break;
			case "checkbox" :
			case "radio" :
				if (array_key_exists($option, $value) && $value[$option] === 'checked') {
					echo " checked ";
				}
				break;
			default :
				break;
		}
	} else if($default != NULL){
		switch ($type) {
			case "text" :
				echo " value='$default' ";
				break;
			case "checkbox" :
			case "radio" :
				if ($default === 'checked') {
					echo " checked ";
				}
				break;
			default :
				break;
		}
	}
}