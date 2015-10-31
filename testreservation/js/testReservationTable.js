function deleteRecord(recordId) {
	$("#deleteConfirmationDialog").dialog("open");
	$("#confirmDelete").one("click", function(){
		dynamicallySubmitForm({
			"submitType" : "delete",
			"targetReservationId" : recordId
		}, "testReservationTable.php");
	});
}

function editRecord(recordId) {
	dynamicallySubmitForm({
		"submitType" : "update",
		"targetReservationId" : recordId
	}, "testReservationForm.php");
}

function dynamicallySubmitForm(params, target) {

	$.each(params, function(key, value) {
		$("<input>").attr("type", "hidden").attr("name", key).val(value)
				.appendTo($('#submitForm'));
	});
	$('#submitForm').attr("action", target);
	$('#submitForm').submit();
}