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
$PAGE->set_title ( "Test Reservation Form" );
$PAGE->set_heading ( "Test Reservation Form" );
$PAGE->set_url ( $CFG->wwwroot . '/testreservation/testReservationForm.php' );
$availableRecord = NULL;
if (array_key_exists ( "submitType", $_POST )) {
	if ($_POST ['submitType'] == "update") {
		$recordset = getRecordSet ( $identity, $_POST ['targetReservationId'] );
		
		if ($recordset->valid ()) {
			
			foreach ( $recordset as $record ) {
				$availableRecord = formatRecordIntoForm ( get_object_vars ( $record ) );
			}
		}
		if ($availableRecord == Null) {
			// Add one button for return
			die ( "No Valid Record!" );
		}
	}
}
// When user is staff and doing update
if (in_array("staff", $identity) && (array_key_exists ( "submitType", $_POST ) && $_POST ['submitType'] == "update")) {
	// register_id not null
	$userId = $availableRecord ['register_id'];
} else {
	$userId = $USER->id;
}

$selectedCourses = enrol_get_all_users_courses ( $userId );
$courseTeacherMap = obtainCourseTeacherMap($userId, $selectedCourses);

$courseTeacherMapJson = json_encode ( $courseTeacherMap );
echo $OUTPUT->header ();
?>
<link rel="stylesheet" type="text/css"
	href="<?php echo $CFG->wwwroot?>/lib/jquery/ui-1.10.4/css/base/jquery-ui.min.css">
<link rel="stylesheet" type="text/css"
	href="<?php echo $CFG->wwwroot?>/testreservation/css/testReservationForm.css">

<div>
	<form id="testReservationForm" action="testReservationTable.php"
		method="post">
		<table>
		<?php if(in_array("staff", $identity)){?>
		<tr>
				<td>Student Name:</td>
				<td><b><?php echo $availableRecord['username']?></b></td>
			</tr>
			<tr>
				<td>CLID:</td>
				<td><b><?php echo $availableRecord['firstname'] . " " . (strlen ( $availableRecord['middlename'] ) > 0 ? $availableRecord['middlename'] . " " : "") . $availableRecord['lastname']?></b></td>
			</tr>
		<?php }?>
			<tr>
				<td>Class Name:</td>
				<td><select name="class">

						<option value=''></option>
				<?php
				foreach ( $selectedCourses as $k => $selectedCourse ) {
					$courseId = $selectedCourse->id;
					echo "<option value = '" . $courseId . "' " . " >" . $selectedCourse->fullname . "</option>";
				}
				
				?>
				</select></td>
			</tr>
			<tr>
				<td>Test Type:</td>
				<td><input type="radio" id="testType-normal" name="testType"
					class="availableTimeValidation" value="normal"><label
					for="testType-normal">normal</label> <input type="radio"
					id="testType-final" name="testType" class="availableTimeValidation"
					value="final"><label for="testType-final">final</label></td>
			</tr>
			<tr>
				<td>Instructor:</td>
				<td><input type="text" name="instructor"></td>
			</tr>
			<tr>
				<td>Original Test Time:</td>
				<td><input type="text" class="datepicker" name="originalTestDate"> <input
					type="text" class="time" name="originalTestTime"></td>
			</tr>
			<tr>
				<td>Test Time Length:</td>
				<td><input type="text" class="timeLength availableTimeValidation"
					name="testLength" min="0">mins</td>
			</tr>
			<tr>
				<td>Reserved Test Date:</td>
				<td><input type="text" class="datepicker availableTimeValidation"
					name="reservedTestDate"></td>
			</tr>
			<tr>
				<td>Reserved Test Time:</td>
				<td><input type="text" class="time availableTimeValidation"
					name="reservedTestTime"></td>
			</tr>
			<tr>
				<td>Required Resources:</td>
				<td><input type="checkbox" id="requiredResources-Computer"
					name="requiredResources[]" value="Computer"><label
					for="requiredResources-Computer">Computer</label> <input
					type="checkbox" id="requiredResources-Internet"
					name="requiredResources[]" value="Internet"><label
					for="requiredResources-Internet">Internet</label> <input
					type="checkbox" id="requiredResources-Private_Room"
					name="requiredResources[]" value="Private Room"><label
					for="requiredResources-Private_Room">Private Room</label></td>
			</tr>
			<?php if(in_array("staff", $identity)){?>
			<tr>
				<td>Returning Instructions:</td>
				<td><input type="radio"
					id="returningInstructions-Hand_Deliver_to_department"
					name="returningInstructions[]" value="Hand Deliver to department"><label
					for="returningInstructions-Hand_Deliver_to_department">Hand Deliver
						to department</label> <input type="radio"
					id="returningInstructions-Will_pick_up"
					name="returningInstructions[]" value="Will pick up"><label
					for="returningInstructions-Will_pick_up">Will pick up</label> <input
					type="radio" id="returningInstructions-Call_for_pick_up"
					name="returningInstructions[]" value="Call for pick up"
					class="optionWithTextField"><label
					for="returningInstructions-Call_for_pick_up">Call for pick up</label><input
					type="text" class="optionTextField"
					for="returningInstructions-Call_for_pick_up"
					placeholer="Phone Number"> <input type="radio"
					id="returningInstructions-Other" name="returningInstructions[]"
					value="Other" class="optionWithTextField"><label
					for="returningInstructions-Other">Other</label><input type="text"
					class="optionTextField" for="returningInstructions-Other"></td>
			</tr>
			<tr>
				<td>Testing Instructions:</td>
				<td><input type="checkbox" id="testingInstructions-Extra_Paper"
					name="testingInstructions[]" value="Extra Paper"><label
					for="testingInstructions-Extra_Paper">Extra Paper</label> <input
					type="checkbox" id="testingInstructions-Statistical_Tables"
					name="testingInstructions[]" value="Statistical Tables"><label
					for="testingInstructions-Statistical_Tables">Statistical Tables</label>
					<input type="checkbox" id="testingInstructions-Open_Book" value="Open Book"
					name="testingInstructions[]" class="optionWithTextField"><label
					for="testingInstructions-Open_Book">Open Book</label><input
					type="text" class="optionTextField"
					for="testingInstructions-Open_Book"><input type="checkbox"
					id="testingInstructions-Open_Notes" name="testingInstructions[]" value="Open Notes"
					class="optionWithTextField"><label for="testingInstructions-Open_Notes">Open Notes</label><input
					type="text" class="optionTextField" for="testingInstructions-Open_Notes"><input
					type="checkbox" id="testingInstructions-Calculator" value="Calculator"
					name="testingInstructions[]" class="optionWithTextField"><label
					for="testingInstructions-Calculator">Calculator</label><input type="text"
					class="optionTextField" for="testingInstructions-Calculator"><input
					type="checkbox" id="testingInstructions-Other"
					name="testingInstructions[]" class="optionWithTextField"><label
					for="testingInstructions-Other">Other</label><input type="text"
					class="optionTextField" for="testingInstructions-Other"></td>
			</tr>
			<?php }?>
			<tr>
				<td><input type="hidden" name="submitType"
					value='<?php echo (array_key_exists('submitType', $_POST)?$_POST['submitType']:'new')?>'>
					<input type="hidden" name="targetReservationId" value= '<?php if($availableRecord != NULL){ echo $availableRecord['id'];}?>'></td>
					<input type="hidden" name="userId" value= '<?php if($userId != NULL){ echo $userId;}?>'></td>
	
			</tr>
		</table>
	</form>
	<div id="controlPanel">
	<?php if(!array_key_exists('submitType', $_POST)){?>
	<div class="button horizontalCenter" id="submit">Submit</div>
	<?php }else if ($_POST['submitType'] == 'update'){?>
	<div class="button horizontalCenter" id="update">Update</div>
		<div class="button" id="delete">Delete</div>
	<?php }?>
	<div class="button" id="cancel"
			onclick="location.href='testReservationTable.php';">Cancel</div>

	</div>
</div>

<div id="workTimeDialog" class="dialog" title="ODS Office Working Hours"
	style="display: none">

	<h5>Normal Exam:</h5>
	<b>Mon - Thu:</b> 7:30 AM - 4:45 PM<br> <b>Fri:</b> 7:30 AM - 12:15 PM<br>
	<h5>Final Exam:</h5>
	<b>Mon - Thu:</b> 7:30 AM - 7:00 PM<br> <b>Fri:</b> 7:30 AM - 2:00 PM<br>

	<div class="button horizontalCenter"
		onclick="$('#workTimeDialog').dialog('close');"
		style="margin-top: 20px;">close</div>
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
<div id="warningDialog" class="dialog" title="Warning"
	style="display: none">
	<p>
		<b>Please Complete the Red Fields</b>
	</p>
	<div class="button horizontalCenter"
		onclick="$('#warningDialog').dialog('close');"
		style="margin-top: 20px;">close</div>
</div>

<script type="text/javascript"
	src="<?php echo $CFG->wwwroot?>/lib/jquery/jquery-1.11.0.min.js"></script>
<script type="text/javascript"
	src="<?php echo $CFG->wwwroot?>/lib/jquery/ui-1.10.4/jquery-ui.min.js"></script>
<script type="text/javascript"
	src="<?php echo $CFG->wwwroot?>/testreservation/js/jquery.inputmask.bundle.min.js"></script>
<script type="text/javascript"
	src="<?php echo $CFG->wwwroot?>/testreservation/js/testReservationForm.js"></script>

<script>
	//which one is required
	
	var courseTeacherMapJson ='<?php echo (strlen($courseTeacherMapJson)>0?$courseTeacherMapJson:"{}");?>';
	var oldRecord ='<?php echo ($availableRecord != Null?json_encode($availableRecord):"{}");?>';
$(document).ready(function(){

	setPreviousRecord(JSON.parse(oldRecord));
	
	$(".button").button();
	$( ".datepicker" ).datepicker({
		  dateFormat: "yy-mm-dd"
	});
	$(".datepicker.availableTimeValidation").datepicker("option", "onSelect", validateWorkTimeShift)
	$(".datepicker").inputmask("y-m-d",{ "placeholder": "yyyy-mm-dd" });
	$(".time").inputmask("hh:mm",{ "placeholder": "00:00" });
	$(".timeLength").inputmask({'alias': 'numeric',  'autoGroup': true, 'digitsOptional': false, 'placeholder': '0'});
	$(".dialog").dialog({"autoOpen":false});
	$("#workTimeDialog").dialog("close");
	$("#workTimeDialog").show();

	$("#submit, #update").click(function(){
		console.log($("#testReservationForm").serialize());
		appendTextFieldOnOptionValue();
		if(validateSubmittedFields()){
			$("#testReservationForm").submit();
		}else{
		$("#warningDialog").dialog("open");
		}
			});
	$("#delete").click(function(){
		$("#deleteConfirmationDialog").dialog("open");
	});
	$("#confirmDelete").click(function(){
		$("input[name = 'submitType']").val("delete");
		$("#testReservationForm").submit();
	});
	$(".availableTimeValidation").change(validateWorkTimeShift);
	$("select[name='class']").change(function(){
		var courseId = $("select[name='class']").val();
		console.log(courseTeacherMapJson);
		var courseTeacherMap = JSON.parse(courseTeacherMapJson);

		if(courseTeacherMap[courseId] != undefined){

			var teacherNamesString = "";
			$.each(courseTeacherMap[courseId], function(key, value){
				//Escape special character
				teacherNamesString += (teacherNamesString.length > 0? ", ":"")+encodeURI(value);
			});
			$("input[name='instructor']").val(teacherNamesString);
		}
	});

	

})


	</script>
<?php
echo $OUTPUT->footer ();

?>