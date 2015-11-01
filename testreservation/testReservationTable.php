<?php
require_once ('../config.php');
require_once ('./testReservationUtil.php');
// require_login();

// Need group verification
$isStaff = verifyODSIdentity ();

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
					$previousReservationId = $_POST ['targetReservationId'];
					$sql = "UPDATE `$testReservationRecordTableName` SET is_valid = 0 WHERE id = $previousReservationId";
					$DB->execute ( $sql );
					directSQLInsertRR ( $_POST );
					directSQLInsertRT ( $submitType, $_POST );
					// $record = createReservationRecordObj ( $_POST );
					// $lastinsertid = $DB->insert_record ( $testReservationRecordTableName, $record, false );
					// $tansaction = createReservationTransactionObj ( $submitType, $_POST );
					// $lastinsertid = $DB->insert_record ( $testReservationTransactionRecordTableName, $tansaction, false );
					break;
				case "delete" :
					$deletedReservationId = $_POST ['targetReservationId'];
					$sql = "UPDATE `$testReservationRecordTableName` SET `is_valid` = 0 WHERE id = $deletedReservationId";
					$DB->execute ( $sql );
					directSQLInsertRT ( $submitType, $_POST );
					// $record = createReservationRecordObj ( $_POST );
					// $lastinsertid = $DB->insert_record ( $testReservationRecordTableName, $record, false );
					// $tansaction = createReservationTransactionObj ( $submitType, $_POST );
					break;
			}
		}
		$recordset = getRecordSet ( $isStaff );
		$recordArray = recordSetToArray ( $recordset );
		$formatedRecordArray = formatRecordArray ( $recordArray, $isStaff );
	} else {
		echo "Database Configuration Error! \n";
		echo "Cannot find required tables.";
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
	href="<?php echo $CFG->wwwroot?>/testreservation/css/tablesorter/style.css">
	
<table id="testReservationRecordTable" class= "tablesorter">
	<thead>
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
	</thead>
			
	<tbody>


		<?php
		foreach ( $formatedRecordArray as $k => $formatedRecord ) {
			echo "<tr>";
			echo "<td class = 'recordData' style = 'display: none'>" . json_encode ( $recordArray [$k] ) . "</td>";
			foreach ( $formatedRecord as $field ) {
				echo "<td>$field</td>";
			}
			echo "<td>";
			echo "<div class = 'edit button' onclick='editRecord($k, $(this));'><span class='ui-icon ui-icon-pencil'></div>";
			echo "<div class = 'delete button' onclick='deleteRecord($k);'><span class='ui-icon ui-icon-trash'></span></div>";
			echo "</td>";
			echo "</tr>";
		}
		?>
	</tbody></table>
<form id="submitForm" method="post"></form>
<div id="controlPanel">
<?php if(!$isStaff){?>
	<div class="button" id="add"
		onclick="location.href='testReservationForm.php';">Add New Reservation</div>
		<?php }else{?>
	<div class="button" id="generateReport">Generate Report</div>
		<?php }?>
</div>
<div class="dialog" id="warningDialog">
	<p></p>
</div>
<div id="deleteConfirmationDialog" class="dialog" title="Confirmation">
	<table>
		<tr>
			<td colspan="3">
				<p>Do you really want to delete this reservation?</p>
			</td>
		</tr>
		<tr>
			<td>
				<div class="button horizontalCenter" id="confirmDelete">delete</div>
			</td>
			<td>
				<div class="button horizontalCenter"
					onclick="$('#deleteConfirmationDialog').dialog('close');">cancel</div>
			</td>
		</tr>
	</table>
</div>
<script type="text/javascript"
	src="<?php echo $CFG->wwwroot?>/lib/jquery/jquery-1.11.2.min.js"></script>
<script type="text/javascript"
	src="<?php echo $CFG->wwwroot?>/lib/jquery/ui-1.11.4/jquery-ui.min.js"></script>
<script type="text/javascript"
	src="<?php echo $CFG->wwwroot?>/testreservation/js/jquery.tablesorter.min.js"></script>

<script type="text/javascript"
	src="<?php echo $CFG->wwwroot?>/testreservation/js/testReservationTable.js"></script>
<script type="text/javascript">

$(document).ready(function(){
$(".button").button();
// $('.edit').button( "option", "icons", 'ui-icon-pencil');
// $('.delete').button( "option", "icons", 'ui-icon-trash');
$(".dialog").dialog({"autoOpen":false});
$( "#warningDialog" ).on( "dialogclose", function( event, ui ) {$("#warningDialog p").text("");} );
$('.tablesorter').tablesorter();
});
</script>
<?php
echo $OUTPUT->footer ();

?>