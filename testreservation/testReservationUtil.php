<?php
function directSQLInsertRR($postArray) {
	global $DB;
	global $USER;
	
	// Add myql filter
	$sql = "INSERT INTO ods_test_reservation_record (`register_id`, `class`, `instructors`,
		`test_type`, `original_test_time`, `test_date`, `test_start_time`,
		`test_duration`, `preference`, `accommodation`,
		`return_type`, `is_valid`) VALUES (" . "'" . $USER->id . "'," . "'" . (array_key_exists ( "class", $postArray ) ? $postArray ['class'] : "") . "'," . "'" . (array_key_exists ( "instructor", $postArray ) ? $postArray ['instructor'] : "") . "'," . "'" . (array_key_exists ( "testType", $postArray ) ? $postArray ['testType'] : "") . "'," . "'" . (array_key_exists ( "originalTestDate", $postArray ) ? $postArray ['originalTestDate']." " : ""). (array_key_exists ( "originalTestTime", $postArray ) ? $postArray ['originalTestTime'] : "") . "'," . "'" . (array_key_exists ( "reservedTestDate", $postArray ) ? $postArray ['reservedTestDate'] : "") . 
	    "'," . "'" . (array_key_exists ( "reservedTestTime", $postArray ) ? $postArray ['reservedTestTime'] : "") . 
	    "'," . "'" . (array_key_exists ( "testLength", $postArray ) ? $postArray ['testLength'] : "") . 
		"'," . "'" . (array_key_exists ( "preference", $postArray ) ? $postArray ['preference'] : "") . 
		"'," . "'" . (array_key_exists ( "requiredResources", $postArray ) ? (is_array($postArray ['requiredResources'])? implode(",", $postArray ['requiredResources']): $postArray ['requiredResources']) : "") . 
		"'," . "'" . (array_key_exists ( "returnType", $postArray ) ? $postArray ['returnType'] : "") . "'," . "1" . ")";
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
//Date | Subject | Start time| Student CLID | Name | Duration | Finish time | Preference| Accommodation | Ret type
function formatRecordSet($recordset, $staffView = true){//Flexible?
	global $USER;
	$formatedRecordSet = array();
	if($recordset->valid()){
		foreach ($recordset as $record){
			//var_dump($record);
			$recordId = $record->id;
			$userId = $record->register_id;
			$formatedRecordSet[$recordId] = array();
			$formatedRecordSet[$recordId]['Date'] = $record->test_date;
			$formatedRecordSet[$recordId]['Subject'] = $record->coursename;
			$formatedRecordSet[$recordId]['Start time'] = $record->test_start_time;			
			//Current User Identify
			if($staffView){
				$formatedRecordSet[$recordId]['Student CLID'] = $record->username;
				$formatedRecordSet[$recordId]['Name'] = $record->firstname. " ".
				(strlen($record->middlename) > 0? $record->middlename." ":"").
				$record->lastname;
				
			}
			

			$formatedRecordSet[$recordId]['Duration'] = $record->test_duration;
			
			$formatedRecordSet[$recordId]['Finish time'] = getTestFinishTime($record->test_start_time, $record->test_duration);
			
			$formatedRecordSet[$recordId]['Preference'] = $record->preference;
			$formatedRecordSet[$recordId]['Accommodation'] = (is_array($record->accommodation)? implode(",", $record->accommodation): $record->accommodation);
			$formatedRecordSet[$recordId]['Ret type'] = $record->return_type;
		}
	}
	return $formatedRecordSet;
}

function getTestFinishTime($startTime, $testLength){
	$timeParts = explode(":", $startTime);
	$hours = intval($timeParts[0]);
	$mins = intval($timeParts[1]);
	$testLengthMin = intval($testLength);
	return sprintf('%02d:%02d', ($hours + intval($testLengthMin / 60)) , ($mins + $testLengthMin % 60));
}