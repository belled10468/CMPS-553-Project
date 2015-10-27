function validateWorkTimeShift() {
	var testType = $("input[name='testType']").val();
	var date = $("input[name='reservedTestDate']").datepicker("getDate");
	var time = $("input[name='reservedTestTime']").val();
	var testLength = parseInt($("input[name='testLength']").val());
	if (testType != undefined && date != undefined) {
		console.log(testType + ":" + date + ":" + time + ":" + testLength)
		var weekDay = date.getDay();
		console.log("weekDay: " + weekDay)
		if (testType == "final") {
			// var date = new Date(dateString);
			if (weekDay > 0 && weekDay < 5) {
				// From Mon to Thur
				// 7:30 - 4:45 => 450 - 1005
				validTimePeriod(time, 450, 1005, testLength)
			} else if (weekDay == 5) {
				// Fri
				// 7:30 - 12:15 =>450 - 735
				validTimePeriod(time, 450, 735, testLength)
			} else {
				$("#workTimeDialog").dialog("open");
				$("input[name='reservedTestDate']").val("");
			}
		} else if (testType == "normal") {
			// var date = new Date(dateString);
			if (weekDay > 0 && weekDay < 5) {
				// 7:30 - 7:00 => 450 - 1140
				validTimePeriod(time, 450, 1140, testLength)
			} else if (weekDay == 5) {
				// 7:30 - 2:00 => 450 - 840
				validTimePeriod(time, 450, 840, testLength)
			} else {
				$("#workTimeDialog").dialog("open");
				$("input[name='reservedTestDate']").val("");
			}
		}
	}
}
function validateSubmittedFields() {
	var vailadationResult = true;
	$("#testReservationForm").find("input:not(input[name='instructor']), select").each(function() {
		var value = $(this).val();
		if (value == undefined || value == "") {
			invalidateTarget($(this))
			vailadationResult = false;
		}
	});
	if ($(".timeLength").val() == 0) {
		invalidateTarget($(".timeLength"))
		vailadationResult = false;
	}
	return vailadationResult;
}

function invalidateTarget(target) {
	target.addClass("error");
	if (target.attr('name') != "class") {
		target.one("click", function() {
			target.removeClass("error");
		});
	} else {
		target.one("click", function() {
			target.removeClass("error");
			$("input[name='instructor']").removeClass("error");
		});
	}
}
function validTimePeriod(time, lowerBound, upperBound, testLength) {
	if (time != undefined) {
		var timeParts = time.split(":");
		var timeInMinutes = parseInt(timeParts[0]) * 60
				+ parseInt(timeParts[1]);
		console.log("time: " + timeInMinutes);
		if (timeInMinutes < lowerBound || timeInMinutes >= upperBound) {
			$("#workTimeDialog").dialog("open");
			$("input[name='reservedTestTime']").val(undefined);
			$("input[name='testLength']").val(undefined);
			return false;
		}
		console.log("timeInMinutes + testLength: " + timeInMinutes + testLength);
		if (testLength != undefined && testLength > 0
				&& timeInMinutes + testLength > upperBound) {
			$("#workTimeDialog").dialog("open");
			$("input[name='testLength']").val(undefined);
			return false;
		}
	}
}