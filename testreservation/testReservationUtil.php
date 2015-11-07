<?php

//
function verifyODSIdentity($testReservationInfo) {
	global $USER;
	global $DB;
	$identity = array();
	$userGroups = $testReservationInfo->userGroups;
	$tableRecords = $DB->get_records_sql ( "SELECT g.`name` FROM `mdl_groups_members` as m
			 JOIN `mdl_groups` as g ON g.`id` = m.`groupid` 
			WHERE m.`userid` = ? AND (g.`name` = ? OR g.`name` = ?)" , array($USER->id, $userGroups['studentGroup'], $userGroups['staffGroup']));
	foreach($tableRecords as $tableRecord){
		if($tableRecord->name == $userGroups['studentGroup']){
			array_push($identity, "student");
		}else if($tableRecord->name == $userGroups['staffGroup']){
			array_push($identity, "staff");
		}
	}
	return $identity;
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
function directSQLInsertRR($postArray) {
	global $DB;
	$params = array ();
	array_push ( $params, purifyDataStringFromArray ( 'userId', $postArray ) );
	array_push ( $params, purifyDataStringFromArray ( 'class', $postArray ) );
	array_push ( $params, purifyDataStringFromArray ( 'instructor', $postArray ) );
	array_push ( $params, purifyDataStringFromArray ( 'testType', $postArray ) );
	array_push ( $params, purifyDataStringFromArray ( 'originalTestDate', $postArray ) . " " . purifyDataStringFromArray ( 'originalTestTime', $postArray ) );
	array_push ( $params, purifyDataStringFromArray ( 'reservedTestDate', $postArray ) );
	array_push ( $params, purifyDataStringFromArray ( 'reservedTestTime', $postArray ) );
	array_push ( $params, purifyDataStringFromArray ( 'testLength', $postArray ) );
	array_push ( $params, purifyDataStringFromArray ( 'testingInstructions', $postArray ) );
	array_push ( $params, purifyDataStringFromArray ( 'requiredResources', $postArray ) );
	array_push ( $params, purifyDataStringFromArray ( 'returningInstructions', $postArray ) );
	array_push ( $params, 1 );
	// Add myql filter
	$sql = "INSERT INTO ods_test_reservation_record (`register_id`, `class`, `instructors`,
		`test_type`, `original_test_time`, `test_date`, `test_start_time`,
		`test_duration`, `testing_instructions`, `accommodation`,
		`return_type`, `is_valid`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	$DB->execute ( $sql, $params );
}
function purifyDataStringFromArray($key, $array) {
	if (array_key_exists ( $key, $array )) {
		if (is_array ( $array [$key] )) {
			return implode ( ",", $array [$key] );
		} else {
			return $array [$key];
		}
	} else {
		return "";
	}
}
function obtainCourseTeacherMap($userId, $selectedCourses) {
	global $DB;
	
	$courseTeacherMap = array ();
	
	// Allow users to modify the instructor fields?
	
	try {
		foreach ( $selectedCourses as $k => $selectedCourse ) {
			$courseId = $selectedCourse->id;
			$courseTeacherMap [$courseId] = array ();
			$teachers = $DB->get_records_sql ( "SELECT u.lastname, u.middlename, u.firstname, u.id
				FROM mdl_course c
				JOIN mdl_context ct ON c.id = ct.instanceid
				JOIN mdl_role_assignments ra ON ra.contextid = ct.id
				JOIN mdl_user u ON u.id = ra.userid
				JOIN mdl_role r ON r.id = ra.roleid
				WHERE r.id = 3 and c.id = ?", array (
					$courseId 
			) );
			foreach ( $teachers as $k => $teacher ) {
				$teacherName = $teacher->firstname . (strlen ( $teacher->middlename ) > 0 ? " " . $teacher->middlename . " " : " ") . $teacher->lastname;
				$courseTeacherMap [$courseId] [$teacher->id] = $teacherName;
			}
		}
	} catch ( Exception $e ) {
	}
	return $courseTeacherMap;
}
function invalidateTargetReservation($reservationId) {
	global $DB;
	$sql = "UPDATE `ods_test_reservation_record` SET is_valid = 0 WHERE id = ?";
	$DB->execute ( $sql, array (
			$reservationId 
	) );
}
function directSQLInsertRT($submitType, $postArray) {
	global $DB;
	global $USER;
	$params = array (
			$submitType,
			$USER->id,
			json_encode ( $postArray ) 
	);
	$sql = "INSERT INTO ods_test_reservation_transaction (`action`, `executor`, `data`) VALUES (?, ?, ?)";
	$DB->execute ( $sql, $params );
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
function formatRecordArray($recordArray, $identity) { // Flexible?
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
		if (in_array("staff", $identity)) {
			$formatedRecordArray [$recordId] ['Student CLID'] = $record->username;
			$formatedRecordArray [$recordId] ['Name'] = $record->firstname . " " . (strlen ( $record->middlename ) > 0 ? $record->middlename . " " : "") . $record->lastname;
		}
		
		$formatedRecordArray [$recordId] ['Duration'] = $record->test_duration;
		
		$formatedRecordArray [$recordId] ['Finish time'] = getTestFinishTime ( $record->test_start_time, $record->test_duration );
		
		$formatedRecordArray [$recordId] ['Accommodation'] = $record->accommodation.(strlen($record->testing_instructions) > 0?",".$record->testing_instructions:"");
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
function getRecordSet($identity, $recordId = Null) {
	global $USER;
	global $DB;
	$params = array ();
	if (in_array("staff", $identity)) {
		$sql = "SELECT t.`id`, t.`register_id`,
		u.`username`, u.`firstname`, u.`middlename`, u.`lastname`,
		t.`class`, c.`fullname` as coursename, t.`instructors`,
		t.`test_type`,  t.`original_test_time`, t.`test_date`, t.`test_start_time`,
		t.`test_duration`, t.`testing_instructions`, t.`accommodation`,
		t.`return_type`, t.`created_date`
		FROM `ods_test_reservation_record` t " . "JOIN mdl_user u ON u.id = t.`register_id`" . "JOIN mdl_course c ON c.id = t.`class`" . "WHERE t.is_valid = 1 ";
	} else {
		$params = array (
				$USER->id 
		);
		$sql = "SELECT t.`id`, t.`register_id`,
		t.`class`, c.`fullname` as coursename, t.`instructors`,
		t.`test_type`,  t.`original_test_time`, t.`test_date`, t.`test_start_time`,
		t.`test_duration`, t.`testing_instructions`, t.`accommodation`,
		t.`return_type`, t.`created_date`
		FROM `ods_test_reservation_record` t " . "JOIN mdl_course c ON c.id = t.`class`" . "WHERE t.is_valid = 1 AND t.register_id = ?";
	}
	if ($recordId != Null) {
		array_push ( $params, $recordId );
		$sql .= " AND t.`id` = ? ";
	}
	
	$sql .= " ORDER BY t.`created_date`";
	$recordset = $DB->get_recordset_sql ( $sql , $params);
	return $recordset;
}
function formatRecordIntoForm($record) {
	if ($record ['original_test_time'] != Null) {
		$original_test_time = explode ( " ", $record ['original_test_time'] );
		$record ['original_test_date'] = $original_test_time [0];
		$record ['original_test_time'] = $original_test_time [1];
	} else {
		$record ['original_test_date'] = '';
	}
	if ($record ['accommodation'] != Null) {
		$record ['accommodation'] = valueToIndexArray ( $record ['accommodation'], ",", ":" );
	} else {
		$record ['accommodation'] = array ();
	}
	if ($record ['testing_instructions'] != Null) {
		$record ['testing_instructions'] = valueToIndexArray ( $record ['testing_instructions'], ",", ":" );
	} else {
		$record ['testing_instructions'] = array ();
	}
	if ($record ['return_type'] != Null) {
		$record ['return_type'] = valueToIndexArray ( $record ['return_type'], ",", ":" );
	}
	return $record;
}
function valueToIndexArray($value, $delimiter, $textFieldSeparator = NULL) {
	$indexArray = array ();
	foreach ( explode ( $delimiter, $value ) as $v ) {
		if ($textFieldSeparator == NULL) {
			$indexArray [$v] = '';
		} else if (strlen ( $v ) > 0) {
			$vParts = explode ( $textFieldSeparator, $v );
			$indexArray [$vParts [0]] = (sizeof ( $vParts ) > 1 ? $vParts [1] : "");
		}
	}
	return $indexArray;
}
function defaultValueApply($value, $type, $option = NULL, $default = NULL) {
	global $_POST;
	if (array_key_exists ( "submitType", $_POST )) {
		switch ($type) {
			case "text" :
				echo " value='$value' ";
				break;
			case "checkbox" :
			case "radio" :
				if (array_key_exists ( $option, $value ) && $value [$option] === 'checked') {
					echo " checked ";
				}
				break;
			default :
				break;
		}
	} else if ($default != NULL) {
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