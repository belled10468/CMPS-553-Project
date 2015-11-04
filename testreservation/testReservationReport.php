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
<link rel="stylesheet" type="text/css"
	href="<?php echo $CFG->wwwroot?>/testreservation/css/testReservationReport.css">

</head>
<body>
	<table id="testReservationRecordTable">
		<thead>
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
			foreach ( $formatedRecord as $fieldName => $field ) {
				if (in_array ( $fieldName, $seletedFields )) {
					echo "<td>$field</td>";
				}
			}
			echo "</tr>";
		}
		?>
	</tbody>
	</table>
</body>
</html>