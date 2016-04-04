<html>
	<head>
		<meta name="author" content="Modise Makhetha"/>
		<meta name="description" content="SchoolLMS Timetable Settings Page"/>
		<meta name="viewport" content="width=device-width, user-scalable=no"/><!-- "position: fixed" fix for Android 2.2+ -->
		<link rel="stylesheet" href="style.css" type="text/css" media="screen"/>
		<script type="text/javascript">
			var redipsURL = '/javascript/drag-and-drop-example-3/';
		</script>
		<!--<script type="text/javascript" src="header.js"></script>
		<script type="text/javascript" src="redips-drag-min.js"></script>
		<script type="text/javascript" src="script.js"></script> -->
		<script type="text/javascript" src="timetable.js"></script>
		<script type="text/javascript" src="jscolor.js"></script>
		
		
		<link rel="stylesheet" type="text/css" href="themes/jquery-ui.css" />
		<link rel="stylesheet" type="text/css" href="themes/default/easyui.css">
		<link rel="stylesheet" type="text/css" href="themes/icon.css">
		<!-- script type="text/javascript" src="Scripts/jquery-2.0.0.min.js"></script -->
		<script type="text/javascript" src="Scripts/jquery-1.8.3.min.js"></script>
		<script src="Scripts/jquery.easyui.min.js" type="text/javascript"></script>
		<script src="Scripts/datagrid-filter.js" type="text/javascript"></script>
		<script type="text/javascript" src="Scripts/jquery.searchabledropdown-1.0.8.min.js"></script>
		<script src="Scripts/jscode.js" type="text/javascript"></script>
		<script src="Scripts/teachersetting.js" type="text/javascript"></script>	
		<script src="Scripts/classvenuesettings.js" type="text/javascript"></script>
		<script src="Scripts/studentsettings.js" type="text/javascript"></script>
		<script type="text/javascript" >
	$(document).ready(function () {
		//var lessons = sendDataByGet("","getdatafromurl.php");
		//alert(lessons);
		var _class = getUrlParameter("class");
		var subject = getUrlParameter("subject");
		//alert(_class);
		_class = _class.split(" ");
		
		var grade = _class[1].toString();
		grade = grade.trim();
		grade = grade.substring(0,grade.length-1);
		//alert(grade);
		//alert(subject);
		var param ="action=get_subject_terms&subject_name="+subject+"&grade_no="+grade+"&term_no=1";
		$("#data").val(param);
		var result = sendDataByGet(param,"timetable_subject_lessons_settings.php");
		//alert(result);
		result =  jQuery.parseJSON(result)	;
		$('#lesson').html("");
		
		$.each(result, function(i, item) {
			$('#lesson').append(
				$('<option></option>').val(result[i].title+"&lessonurl="+result[i].alias).html(result[i].title)
			); 
		});
		//action=get_subject_terms&subject_name=GEOGRAPHY (J-GEO)&grade_no=8&term_no=1
		
		$("#btnSavePopup").click(function () {
			//alert("we are here");
			var _class = getUrlParameter("class");
			var subject = getUrlParameter("subject");
			var timeslot = getUrlParameter("timeslot");			
			//alert(timeslot);
			timeslot = timeslot.split('<br>');
			var thetime = timeslot[0].split('~');
			//alert(timeslot[2]);
			var day = thetime[1];
			day = day.replace("<b>","");
			day = day.replace("</b>","");
			var params = "save_type=publish_lesson&subject="+subject+"&class="+_class+"&date="+timeslot[2]+"&time="+thetime[0]+"&lesson="+$("#lesson").val()+"&day="+day;
			//alert(params);
			sendDataByGet(params,"timetable_save.php")
			//alert(sendDataByGet(params,"timetable_save.php"));
			alert("Lesson Published");
			/*var daddy = window.self;
			daddy.opener = window.self;
			daddy.close();	*/
			});
	});
</script>
		
</head>
<body>

<?php

include('data_db.inc');

$lessondb = new data();
$lessondb->username = "root";
$lessondb->password = "$0W3t0";
$lessondb->host = "localhost";
$lessondb->db = "school_lms_prod_schools_sphs_teach"; 

extract($_POST);
extract($_GET);

echo "<table width='100%'><tr><td>Lesson </td><td>
<select id='lesson' name='lesson'> 

</select> 
</td><td>
	<button id='btnSavePopup'  name='btnSavePopup'>Publish Lesson</button>
</td></tr></table>";

$class = str_replace(" ","%",$class);
$sql = "select access_id, name, surname, sd.user_id
from schoollms_schema_userdata_learner_schooldetails sd
join schoollms_schema_userdata_access_profile p on p.user_id = sd.user_id
join schoollms_schema_userdata_school_classes sc on sc.class_id = sd.class_id
where class_label like '%$class%'";

$table = "<br/><table border=1 id=tblpopup name=tblpopup width='100%'> <tr><td>ID Number</td><td>Student Name</td><td>Image</td><td>Present</td><td>Merit</td><td>Demerit</td></tr>";
$data->execSQL($sql);
$result = array();
while($row = $data->getRow())
{
	$table .=  "<tr><td>$row->access_id</td><td>$row->name $row->surname</td><td><img src='process.php?action=GETIMAGE&user_id=$row->user_id'/></td><td><input type='checkbox' name='".$row->access_id."_present' value='present' /></td>
	<td><select id='lesson' name='lesson'> <option value='Lesson 1'>Well Behaved </option>
			<option value='Lesson 2'>Good homework</option>
			<option value='Lesson 3'>Class Participation</option>
			</select></td>
	<td><select id='demerit' name='demerit'> <option value='Lesson 1'>Demerit Reason </option>
			<option value='Lesson 2'>Late</option>
			<option value='Lesson 3'>Did Not Do Homework </option>
			</select></td>
	</tr>";
}
$table .= "";

echo $table;
?>
<p id="data" name="data">
</p>
</body>
</html>
