<?php
require_once ('../config.php');

//require_login();

$PAGE->set_context ( get_system_context () );
$PAGE->set_pagelayout ( 'standard' );
$PAGE->set_title ( "Test Reservation Form" );
$PAGE->set_heading ( "Test Reservation Form" );
$PAGE->set_url ( $CFG->wwwroot . '/testreservation/testReservationForm.php' );

//get User Id
$userId = $USER->id;
//Verify user group?
//Verify user identity?


$selectedCourses = enrol_get_all_users_courses($userId);

$courseTeacherMap = array();

//Allow users to modify the instructor fields?
global $DB;
try {
	foreach($selectedCourses as $k => $selectedCourse){
		$courseId = $selectedCourse->id;
		$courseTeacherMap[$courseId] = array();
		$teachers = $DB->get_records_sql(
				"SELECT u.lastname, u.middlename, u.firstname, u.id
				FROM mdl_course c
				JOIN mdl_context ct ON c.id = ct.instanceid
				JOIN mdl_role_assignments ra ON ra.contextid = ct.id
				JOIN mdl_user u ON u.id = ra.userid
				JOIN mdl_role r ON r.id = ra.roleid
				WHERE r.id = 3 and c.id = $courseId");
		foreach($teachers as $k => $teacher){
			$teacherName = $teacher->firstname . (strlen($teacher->middlename) > 0?" ".$teacher->middlename." ":" ") . $teacher->lastname;
			$courseTeacherMap[$courseId][$teacher->id]=$teacherName;
		}
	}
} catch(Exception $e) {
}
$courseTeacherMapJson = json_encode($courseTeacherMap);
echo $OUTPUT->header ();
?>
<link rel="stylesheet" type="text/css"
	href="<?php echo $CFG->wwwroot?>/lib/jquery/ui-1.11.4/jquery-ui.min.css">
<link rel="stylesheet" type="text/css"
	href="<?php echo $CFG->wwwroot?>/testreservation/css/testReservationForm.css">

<div>
	<form id="testReservationForm" action="testReservationTable.php" method="post">
		<table>
			<tr>
				<td>Class Name:</td>
				<td><select name ="class">
				<?php 
				echo "<option selected></option>";
				foreach($selectedCourses as $k => $selectedCourse){
					echo "<option value = '".$selectedCourse->id."'>".$selectedCourse->fullname."</option>";
				}
				
				?>
				</select></td>
			</tr>
			<tr>
				<td>Test Type:</td>
				<td>
				<input type = "radio" id="normalType" name ="testType" class = "availableTimeValidation" value = "normal" checked><label for ="normalType">normal</label>
				<input type = "radio" id="finalType" name ="testType" class = "availableTimeValidation"  value = "final"><label for ="finalType">final</label>
				</td>
			</tr>
			<tr>
				<td>Instructor:</td>
				<td><input type = "text" name ="instructor"></td>
			</tr>
			<tr>
				<td>Original Test Time:</td>
				<td><input type = "text" class = "datepicker" name ="originalTestDate"><input type = "text" class = "time" name ="originalTestTime"></td>
			</tr>
			<tr>
				<td>Test Time Length:</td>
				<td><input type = "text"  class = "timeLength availableTimeValidation" name ="testLength" min = "0">mins</td>
			</tr>
			<tr>
				<td>Reserved Test Date:</td>
				<td><input type = "text" class = "datepicker availableTimeValidation" name ="reservedTestDate"></td>
			</tr>
			<tr>
				<td>Reserved Test Time:</td>
				<td><input type = "text" class = "time availableTimeValidation" name ="reservedTestTime" ></td>
			</tr>
			<tr>
				<td>Required Resources:</td>
				<td>
				<input type = "checkbox" id="computerRequired" name ="requiredResources[]" value = "Computer"><label for ="computerRequired">Computer</label>
				<input type = "checkbox" id="InternetRequired" name ="requiredResources[]" value = "Internet"><label for ="InternetRequired">Internet</label>
				<input type = "checkbox" id="privateRoomRequired" name ="requiredResources[]" value = "Private Room"><label for ="privateRoomRequired">Private Room</label>
				</td>
			</tr>
			<tr>
				<td>
					<input type = "hidden" name="submitType" value = "new">
				</td>
			</tr>
		</table>
	</form>
	<div id = "controlPanel">
	<div class = "button horizontalCenter" id = "submit">Submit</div>
	<div class = "button" id = "update" style = "display:none">Update</div>
	<div class = "button" id = "delete" style = "display:none">Delete</div>
	<div class="button" id="cancel"
		onclick="location.href='testReservationTable.php';">Cancel</div>
	
	</div>
</div>

<div id ="workTimeDialog" class = "dialog" title = "ODS Office Working Hours" style = "display:none">

		<h5>Normal Exam: </h5>
		<b>Mon - Thu:</b> 7:30 AM - 4:45 PM<br>
		<b>Fri:</b> 7:30 AM - 12:15 PM<br>
		<h5>Final Exam: </h5>
		<b>Mon - Thu:</b> 7:30 AM - 7:00 PM<br>
		<b>Fri:</b> 7:30 AM - 2:00 PM<br>

		<div class = "button horizontalCenter" onclick="$('#workTimeDialog').dialog('close');" style=" margin-top:20px;">close</div>
</div>

<div id ="warningDialog" class = "dialog" title = "Warning" style = "display:none">
<p><b>Please Complete the Red Fields</b></p>
<div class = "button horizontalCenter" onclick="$('#warningDialog').dialog('close');" style=" margin-top:20px;">close</div>
</div>

<script type="text/javascript"
	src="<?php echo $CFG->wwwroot?>/lib/jquery/jquery-1.11.2.min.js"></script>
<script type="text/javascript"
	src="<?php echo $CFG->wwwroot?>/lib/jquery/ui-1.11.4/jquery-ui.min.js"></script>
<script type="text/javascript"
	src="<?php echo $CFG->wwwroot?>/testreservation/js/jquery.inputmask.bundle.min.js"></script>
<script type="text/javascript"
	src="<?php echo $CFG->wwwroot?>/testreservation/js/testReservationForm.js"></script>
	
	<script>
	//which one is required
	
	var courseTeacherMapJson ='<?php echo (strlen($courseTeacherMapJson)>0?$courseTeacherMapJson:"{}");?>';
	
$(document).ready(function(){
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

	$("#submit").click(function(){
		console.log($("#testReservationForm").serialize());
		if(validateSubmittedFields()){
			$("#testReservationForm").submit();
		}else{
$("#warningDialog").dialog("open");
			}
			});
	$(".availableTimeValidation").change(validateWorkTimeShift);
	$("select[name='class']").change(function(){
		var courseId = $("select[name='class']").val();
		console.log(courseTeacherMapJson);
		var courseTeacherMap = JSON.parse(courseTeacherMapJson);

		if(courseTeacherMap[courseId] != undefined){

			var teacherNamesString = "";
			$.each(courseTeacherMap[courseId], function(key, value){
				teacherNamesString += (teacherNamesString.length > 0? ", ":"")+value;
			});
			$("input[name='instructor']").val(teacherNamesString);
		}
	});

})


	</script>
<?php
echo $OUTPUT->footer ();

?>