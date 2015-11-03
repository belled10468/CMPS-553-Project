<!DOCTYPE HTML>
<?php
require_once ('../config.php');
require_once ('./testReservationUtil.php');
require_once ('./resources/TestReservationInfo.php');
// require_login();

$testReservationInfo = TestReservationInfo::Instance ();
$identity = verifyODSIdentity ( $testReservationInfo );

if (array_key_exists ( "seletedFields", $_POST )) {
	$seletedFields = $_POST ['seletedFields'];
} else {
	die ( "Please select data you want to view." );
}

$recordset = getRecordSet ( $identity );

$recordArray = recordSetToArray ( $recordset );

$formatedRecordArray = formatRecordArray ( $recordArray, $identity );

?>
<html>
<head>
<title>Test Reservation Report</title>
</head>
<body>
	<table id="testReservationRecordTable">
		<thead>
			<!-- Date | Subject | Start time| Student CLID | Name | Duration | Finish time | Preference| Accommodation | Ret type -->
			<tr>
				<?php
				foreach ( $seletedFields as $k => $seletedField ) {
					echo "<th>$seletedField</th>";
				}
				?>
			</tr>
		</thead>

		<tbody>


		<?php
		foreach ( $formatedRecordArray as $k => $formatedRecord ) {
			
			echo "<tr>";
			echo "<td class = 'recordData' style = 'display: none'>" . json_encode ( $recordArray [$k] ) . "</td>";
			foreach ( $formatedRecord as $fieldName => $field ) {
				if (in_array ( $fieldName, $seletedFields )) {
					echo "<td>$field</td>";
				}
			}
			echo "<td>";
			echo "<div class = 'edit button' onclick='editRecord($k, $(this));'><span class='ui-icon ui-icon-pencil'></div>";
			echo "<div class = 'delete button' onclick='deleteRecord($k);'><span class='ui-icon ui-icon-trash'></span></div>";
			echo "</td>";
			echo "</tr>";
		}
		?>
	</tbody>
	</table>
</body>
</html>