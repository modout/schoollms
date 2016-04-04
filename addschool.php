<?php
include('data_db_mysqli.inc');
include('util.php');

$sql = "select * from schoollms_schema_userdata_school_type";
$data->execSQL($sql);
$schooltype = "<select name='school_type' id='school_type'>";
while($row = $data->getRow())
{
	$schooltype .= "<option value='$row->type_id'>$row->type_title</option>";
}
$schooltype .= "</select>";

?>
<form id="frmSchoolInfo" name="frmSchoolInfo" method="POST">
	<table width="100%">
		<tr>
			<td>School Type</td>
			<td><?php echo $schooltype; ?></td>
		</tr>
		<tr>
			<td>School Name</td><td><input type="text" name="school_name" id="school_name" /></td>
		</tr>
		<!-- tr>
			<td>Number of buildings</td><td><input type="text" name="numberofbuildings" id="numberofbuildings" /></td>
		</tr>
		<tr>
			<td>Number of fields</td><td><input type="text" name="numberoffields" id="numberoffields" /></td>
		</tr -->
		
		<tr>
			<td colspan="2">
			<table style="width:750px;height:800px">
				<tr>
					<td>
						<iframe src='http://www.gpsvisualizer.com/draw/' width=100% height=100%>  </iframe>
					</td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td>Longitude</td><td><input type="text" name="longitude" id="longitude" /></td>
		</tr>
		<tr>
			<td>Latitude</td><td><input type="text" name="latitude" id="latitude" /></td>
		</tr>
		<tr>
			<td><input type="submit" name="btnSave" id="btnSave" value="Save"  /></td><td><input type="button" name="btnCancel" id="btnCancel" value="Cancel"  onClick="window.close();"; /></td>
		</tr>
	</table>
</form>

<?php
	if(isset($_POST["btnSave"]))
	{
		extract($_POST);
		
		$sql = "insert into schoollms_schema_userdata_schools(school_name,school_type,longitude,latitude) 
			values ('$school_name','$school_type','$longitude','$latitude')";
		$data->execNonSql($sql);
		//echo $sql;
		closeWindow();
	}
?>