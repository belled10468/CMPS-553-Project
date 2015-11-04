<?php
require_once ('../config.php');
require_once ('./testReservationUtil.php');
require_once ('./resources/TestReservationInfo.php');
// require_login();

// Need group verification
$testReservationInfo = TestReservationInfo::Instance();
$identity = verifyODSIdentity ($testReservationInfo);

$PAGE->set_context ( get_system_context () );
$PAGE->set_pagelayout ( 'standard' );
$PAGE->set_title ( "Test Reservation Table" );
$PAGE->set_heading ( "Test Reservation Table" );
$PAGE->set_url ( $CFG->wwwroot . '/testreservation/testReservationTable.php' );
try {
	$transaction = $DB->start_delegated_transaction ();
	
	verifyBasicDatabaseTableSetup();
	
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
					invalidateTargetReservation($previousReservationId);
					directSQLInsertRR ( $_POST );
					directSQLInsertRT ( $submitType, $_POST );
					// $record = createReservationRecordObj ( $_POST );
					// $lastinsertid = $DB->insert_record ( $testReservationRecordTableName, $record, false );
					// $tansaction = createReservationTransactionObj ( $submitType, $_POST );
					// $lastinsertid = $DB->insert_record ( $testReservationTransactionRecordTableName, $tansaction, false );
					break;
				case "delete" :
					$deletedReservationId = $_POST ['targetReservationId'];
					invalidateTargetReservation($deletedReservationId);
					directSQLInsertRT ( $submitType, $_POST );
					// $record = createReservationRecordObj ( $_POST );
					// $lastinsertid = $DB->insert_record ( $testReservationRecordTableName, $record, false );
					// $tansaction = createReservationTransactionObj ( $submitType, $_POST );
					break;
			}
		}
		$recordset = getRecordSet ( $identity );

		$recordArray = recordSetToArray ( $recordset );
		
		$formatedRecordArray = formatRecordArray ( $recordArray, $identity );
	
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
			<?php if(in_array("staff", $identity)){?><th>Student CLID</th>
			<th>Name</th><?php }?>
			<th>Duration</th>
			<th>Finish time</th>
<!-- 			<th>Preference</th> -->
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
<?php if(in_array("student", $identity)){?>
	<div class="button" id="add"
		onclick="location.href='testReservationForm.php';">Add New Reservation</div>
		<?php }else{?>
	<div class="button" id="generateReport" onclick="$('#generateReportDialog').dialog('open')">Generate Report</div>
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
<div id="generateReportDialog" class="dialog" title="Generate Report">
	<form id="testReservationReportForm" action="testReservationReport.php"
		method="post" target="_blank">
		<table>
			<tr>
				<td colspan="3">
					<p>Please select the data you want to examine.</p>
				</td>
			</tr>
			<tr>
				<td colspan="5">
					<input type="checkbox" id="seletedFields1" name="seletedFields[]" value="Date" checked><label for="seletedFields1">Date</label>
					<input type="checkbox" id="seletedFields2" name="seletedFields[]" value="Subject" checked><label for="seletedFields2">Subject</label>
					<input type="checkbox" id="seletedFields3" name="seletedFields[]" value="Start time" checked><label for="seletedFields3">Start time</label>
					<input type="checkbox" id="seletedFields4" name="seletedFields[]" value="Student CLID" checked><label for="seletedFields4">Student CLID</label>
<!-- 					<input type="checkbox" id="seletedFields5" name="seletedFields[]" value="Original Test Time"><label for="seletedFields5">Original Test Time</label> -->
					<input type="checkbox" id="seletedFields6" name="seletedFields[]" value="Name" checked><label for="seletedFields6">Name</label>
					<input type="checkbox" id="seletedFields7" name="seletedFields[]" value="Duration" checked><label for="seletedFields7">Duration</label>
					<input type="checkbox" id="seletedFields8" name="seletedFields[]" value="Finish time" checked><label for="seletedFields8">Finish time</label>
<!-- 					<input type="checkbox" id="seletedFields9" name="seletedFields[]" value="Preference" checked><label for="seletedFields9">Preference</label> -->
					<input type="checkbox" id="seletedFields10" name="seletedFields[]" value="Accommodation" checked><label for="seletedFields10">Accommodation</label>
					<input type="checkbox" id="seletedFields11" name="seletedFields[]" value="Ret type" checked><label for="seletedFields11">Ret type</label>
				</td>
			</tr>
			<tr>
				<td>
					<div class="button horizontalCenter" id="confirmGenerateReport" onclick="$('#testReservationReportForm').submit();$('#generateReportDialog').dialog('close');">Generate</div>
				</td>
				<td>
					<div class="button horizontalCenter"
						onclick="$('#generateReportDialog').dialog('close');">cancel</div>
				</td>
			</tr>
		</table>
	</form>
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