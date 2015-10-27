<?php
require_once ('../config.php');
require_once ('./testReservationUtil.php');
// require_login();

// Need group verification
$isStaff = false;

$PAGE->set_context ( get_system_context () );
$PAGE->set_pagelayout ( 'standard' );
$PAGE->set_title ( "Test Reservation Table" );
$PAGE->set_heading ( "Test Reservation Table" );
$PAGE->set_url ( $CFG->wwwroot . '/testreservation/testReservationTable.php' );
try {
	$transaction = $DB->start_delegated_transaction ();
	$testReservationRecordTableName = "ods_test_reservation_record";
	$testReservationTransactionRecordTableName = "ods_test_reservation_transaction";
	// Check table exist or not
	$tableRecord = $DB->get_records_sql ( "SHOW TABLES LIKE 'ods_test_reservation_%'" );
	$obtainTables = array_keys ( $tableRecord );
	
	if (in_array ( $testReservationRecordTableName, $obtainTables ) && in_array ( $testReservationTransactionRecordTableName, $obtainTables )) {
		if (array_key_exists ( "submitType", $_POST )) {
			$submitType = $_POST ['submitType'];
			
			switch ($submitType) {
				case "new" :
					directSQLInsertRR ( $_POST );
					directSQLInsertRT ( $submitType, $_POST );
					// $record = createReservationRecordObj ( $_POST );
					// $lastinsertid = $DB->insert_record_raw ( $testReservationRecordTableName, $record, false );
					// $tansaction = createReservationTransactionObj ( $submitType, $_POST );
					// $lastinsertid = $DB->insert_record_raw ( $testReservationTransactionRecordTableName, $tansaction, false );
					break;
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
					break;
			}
		}
		if ($isStaff) {
			$sql = "SELECT t.`id`, t.`register_id`, 
		u.`username`, u.`firstname`, u.`middlename`, u.`lastname`,
		t.`class`, c.`fullname` as coursename, t.`instructors`, 
		t.`test_type`,  t.`original_test_time`, t.`test_date`, t.`test_start_time`,
		t.`test_duration`, t.`preference`, t.`accommodation`, 
		t.`return_type`, t.`created_date` 
		FROM `$testReservationRecordTableName` t " . "JOIN mdl_user u ON u.id = t.`register_id`". "JOIN mdl_course c ON c.id = t.`class`" . "WHERE t.is_valid = 1 ";
		} else {
			$sql = "SELECT t.`id`, t.`register_id`,
			t.`class`, c.`fullname` as coursename, t.`instructors`,
			t.`test_type`,  t.`original_test_time`, t.`test_date`, t.`test_start_time`,
			t.`test_duration`, t.`preference`, t.`accommodation`,
			t.`return_type`, t.`created_date` 
			FROM `$testReservationRecordTableName` t ".
			"JOIN mdl_course c ON c.id = t.`class`". 
			"WHERE t.is_valid = 1 AND t.register_id = ".$USER->id;
		}
		$recordset = $DB->get_recordset_sql ( $sql );

		$formatedRecordSet = formatRecordSet ( $recordset , $isStaff);
	} else {
		echo "Database Configuration Error! ";
	}
	$transaction->allow_commit ();
} catch ( Exception $e ) {
	$transaction->rollback ( $e );
}

echo $OUTPUT->header ();
?>
<link rel="stylesheet" type="text/css"
	href="<?php echo $CFG->wwwroot?>/lib/jquery/ui-1.11.4/jquery-ui.min.css">
<link rel="stylesheet" type="text/css"
	href="<?php echo $CFG->wwwroot?>/testreservation/css/jquery.dataTables.min.css">
<table id="testReservationRecordTable">
	<tbody>
		<!-- Date | Subject | Start time| Student CLID | Name | Duration | Finish time | Preference| Accommodation | Ret type -->
		<tr>
			<th>Date</th>
			<th>Subject</th>
			<th>Start time</th>
			<?php if($isStaff){?><th>Student CLID</th>
			<th>Name</th><?php }?>
			<th>Duration</th>
			<th>Finish time</th>
			<th>Preference</th>
			<th>Accommodation</th>
			<th>Ret type</th>
			<th>Operations</th>
		</tr>
		<?php 
		foreach($formatedRecordSet as $formatedRecord){
			echo "<tr>";
			foreach($formatedRecord as $field){
				echo "<td>$field</td>";
			}
			echo "</tr>";
		}
		?>
	</tbody>
</table>
<div id="controlPanel">
	<div class="button" id="add"
		onclick="location.href='testReservationForm.php';">Add New Reservation</div>
	<div class="button" id="generateReport" style="display: none">Generate
		Report</div>
</div>
<div class="dialog" id="warningDialog"></div>
<script type="text/javascript"
	src="<?php echo $CFG->wwwroot?>/lib/jquery/jquery-1.11.2.min.js"></script>
<script type="text/javascript"
	src="<?php echo $CFG->wwwroot?>/lib/jquery/ui-1.11.4/jquery-ui.min.js"></script>
<script type="text/javascript"
	src="<?php echo $CFG->wwwroot?>/testreservation/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">

$(document).ready(function(){
$(".button").button();
$(".dialog").dialog({"autoOpen":false});
$('#testReservationRecordTable').DataTable();
	});


</script>
<?php
echo $OUTPUT->footer ();

?>