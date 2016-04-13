<?php

//include('data_db_mysqli.inc');
//include('config_mysql_learn.php');
include("db.inc");
include("util.php");

extract($_POST);
extract($_GET);
$user_type = isset($user_type) ? $user_type:4;


$school_id = 0;
$disable = "";
if(isset($_REQUEST['school_id']))
{
 $school_id = $_REQUEST['school_id'];
 $disable = ($user_type == 1 or $user_type== 5)? "" :"disabled";
} 



//$user_type = 4;
/**
usertype : 2
staff : 1
learner : 2
parent : 3
teacher : 4
manager :  5
support : 6

[1/11/2016, 15:43] Modise: Teachers see View Timetable...
[1/11/2016, 15:44] Modise: But the school in Tzaneen has nobody that can do what I still need to do...
[1/11/2016, 15:44] Modise: Parents see view Timetable...
[1/11/2016, 15:45] Modise: Management sees View Timetable same as Admin plus Reports...
[1/11/2016, 15:46] Modise: Support sees all tabs...

*/

$student_table_title = "All Students";
$students_per_grade = "Grade Students";


$sql = "select * from schoollms_schema_userdata_schools";
$data->execSQL($sql);
$schools = "<select name=\"schools\" id=\"schools\" $disable>";
$schools .= "<option value='0'>Select School</option>"; 
while($row = $data->getRow())
{
	$school_name = str_replace('^',' ',$row->school_name);
	if($row->school_id == $school_id )
	{	
		$schools .= "<option value='$row->school_id' selected>$school_name</option>"; 
	}
	else{
		$schools .= "<option value='$row->school_id' >$school_name</option>";
	}
}
$schools .= "</select>";

$sql = "select * from schoollms_schema_userdata_school_subjects ";
$data->execSQL($sql);
$subjects = "<select name=\"subject_id\" id=\"subject_id\" >";
$subjects .= "<option value='0'>Select Subject to get Settings</option>"; 

$teacher_subjects = "<select name=\"teacher_subject_id\" id=\"teacher_subject_id\" >";
$teacher_subjects .= "<option value='0'>Select Subject</option>"; 

while($row = $data->getRow())
{
	$teacher_subjects .= "<option value='$row->subject_id' >$row->subject_title</option>";
	$subjects .= "<option value='$row->subject_id' >$row->subject_title</option>";
}
$teacher_subjects .= "</select>";
$subjects .= "</select>";

$sql = "select * from schoollms_schema_userdata_school_year";
$data->execSQL($sql);
$year = date("Y");
//echo $year;
$teachertimetable_year_id = "<select name=\"teachertimetable_year_id\" id=\"teachertimetable_year_id\" >";
$teachertimetable_year_id .= "<option value='All' >All</option>";
$teacheryear = "<select name=\"year_id\" id=\"year_id\" >";
$learneryear = "<select name=\"student_year_id\" id=\"student_year_id\" >";
$classyear = "<select name=\"class_year_id\" id=\"class_year_id\" >";
$ttyear = "<select name=\"tt_year_id\" id=\"tt_year_id\" >";
$yearselected = "";
while($row = $data->getRow())
{
	if($year == $row->year_label)
	{
		$yearselected = "selected";
	}
	else{
		$yearselected = "";
	}
	$teacheryear .= "<option value='$row->year_id' $yearselected>$row->year_label</option>";
	$learneryear .= "<option value='$row->year_id' $yearselected>$row->year_label</option>";
	$classyear .= "<option value='$row->year_id' $yearselected>$row->year_label</option>";
	$ttyear .= "<option value='$row->year_id' $yearselected>$row->year_label</option>";
	$teachertimetable_year_id .= "<option value='$row->year_id' $yearselected>$row->year_label</option>";
}

$classyear .= "</select>";
$teacheryear .= "</select>";
$learneryear .= "</select>";
$ttyear .= "</select>";
$teachertimetable_year_id .= "</select>";



?>
<!DOCTYPE html PUBLIC>
<html>
	<head>
		<meta name="author" content="Siphiwo Dzingwe;Modise Makhetha"/>
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
			var subjectChoice = [
				{selectionid:'1',value:'Grade Choice'},
				{selectionid:'2',value:'Learner Choice'}
			];		
			 var subjects;			
			$(document).ready(function () {
			
				//alert("Ek se ");
				//return;
				var user_type = getUrlParameter("user_type");
				if(user_type != 6)
				{
					$('#mydiv').find('input, textarea, button, select').attr('disabled','disabled');
				}
				
				 //viewTimeTable();
				
				var school_id = getUrlParameter("school_id");
				if(school_id != null && school_id != undefined)
				{
					
					viewTimeTable();
					getStep1Data();
					getSchoolData(school_id);
				}
				//alert("aredf ");
			
				$('.numbersOnly').keyup(function () { 
					this.value = this.value.replace(/[^0-9\.]/g,'');
				});
				
				
				
				
				for(i =1;i<=3;i++)
				{
					$("#rw"+i+"parent2").hide();					
				}
				
				$("#number_of_parents").on('change', function() {
					
					for(z=1;z<=2;z++)
					{
						for(i =1;i<=3;i++)
						{
							$("#rw"+i+"parent"+z).hide();					
						}
					}
					
					for(z=1;z<=$("#number_of_parents").val();z++)
					{
						for(i =1;i<=3;i++)
						{
							$("#rw"+i+"parent"+z).show();					
						}
					}
				});
				
				//alert(subjects);
				getTimetable(22);
				
				
				//$('#students').datagrid({singleSelect:false,remoteFilter:true,enableFilter:true});
			
			
			var page = getUrlParameter("page");
			$("#" + page).addClass("current");

			$(".next").click(function () {
				//alert("next");
				next();
			});

			$(".previous").click(function () {
				//alert("previous");
				previous();
			});
			
			$("#student_grade").on('change', function() {
				setStudentSettings();
			});
			
			$("#teachertimetable_grade_id").on('change', function() {
				//alert($("#teachertimetable_grade_id").val());
				viewTimeTable($("#teachertimetable_grade_id").val());
			});

			for(i =2;i <5;i++)
			{					
				$("#row_break_"+i).hide();					
			}

			
			$("#number_of_breaks").on('change', function() {
				//alert($("#number_of_breaks").val());
				
				for(i =1;i <5;i++)
				{				
					$("#row_break_"+i).hide();					
				}
				
				for(i =0;i<$("#number_of_breaks").val();i++)
				{
					$("#row_break_"+(i+1)).show();	
				}
				
				
				
				/*if($("#number_of_breaks").val() == "1")
				{
					$("#break_type").val("first break");
					$("#break_type").prop("disabled", true);
				}
				else{
					$("#break_type").prop("disabled", false);
				}*/
			});
			
			$("#subject_id").on('change', function() {			
				//alert($("#subjects").val());
				var school_id = getUrlParameter("school_id");
				var setting = getJasonData("school_id="+school_id+"&action=GETSUBJECTSETTINGS&subject_id="+$("#subject_id").val());	
				//alert(setting);
				setting =  jQuery.parseJSON(setting);
				
				//alert(setting);
				if (setting.length == 0) {
					alert("No Settings found for the selected subject");
					return;
				}
				 gradeSubjectSetting(setting);
				
			});
			
			$("#timetable_id").on('change', function() {	
				
				getTimetable($("#timetable_id").val());
				getClassList($("#timetable_id").val());
			});
			
			$("#class_list").on('change', function() {
				//alert("We are here");
				getTimetable($("#class_list").val());
				//getClassList($("#timetable_id").val());
			});
			
			$("#teacher_list").on('change', function() {
				var class_id = $("#timetable_id").val();
				var classes = getJasonData("action=GETCLASSLIST&class_id="+class_id+"&year_id="+$("#teachertimetable_year_id").val());
				classes = jQuery.parseJSON(classes);
				//alert(classes);
				$("#class_list").html("");
				$('#class_list').append(
					$('<option></option>').val(0).html("Select Learner To View Time Table")
				); 
				$.each(classes, function(i, item) {
					var name = item.access_id + " " + item.name + " " + item.surname;
					$('#class_list').append(
						$('<option></option>').val(item.user_id).html(name)
					); 
				});
			});
			
			
			$("#class_list").on('change', function() {
				var class_id = $("#timetable_id").val();
				var teachers = getJasonData("action=GETCLASSTEACHER&class_id="+class_id);
				teachers = jQuery.parseJSON(teachers);
				//alert(teachers);
				$("#teacher_list").html("");
				$('#teacher_list').append(
					$('<option></option>').val(0).html("Select Teacher To View Time Table")
				); 
				$.each(teachers, function(i, item) {
					//var name = item.access_id + " " + item.name + " " + item.surname;
					var name = item.name + " " + item.surname;
					$('#teacher_list').append(
						$('<option></option>').val(item.user_id).html(name)
					); 
				});
			});
			
			var theUrl = "getpages.php?page=school";
	 
	  
			  $.ajax({  
				type: "GET",  
				url: theUrl,  
				data: "",
				success: function(data) {  
				  $("#capturetable").html(data)
				}  
			  }); 
			 

			/*("#class_list").searchable({
				maxListSize: 100,
				maxMultiMatch: 100,
				latency: $("#latency").val(),
				exactMatch: false,
				wildcards: true,
				ignoreCase: true
			});*/
			 

		});
		
		
		
		</script>
		
		<title>SchoolLMS Timetable Settings</title>
	</head>
	<body>
	
		<div id="tabs" class="easyui-tabs" style="width:100%;height:800px">
			
			
			<?php 
			
			
			
			if($user_type == 1)
			{
				include_once("pages/staff.php");
			}
			if($user_type == 2)
			{
				include_once("pages/learner.php");
			}
			if($user_type == 3)
			{
				include_once("pages/parent.php");
			}
			if($user_type == 4)
			{
				//
				include_once("pages/teacher.php");
			}
			if($user_type == 5)
			{
				include_once("pages/manager.php");
			}			
			
			if($user_type == 6)
			{
				//echo "halo";
				require("pages/support.php");
			}

			?>
			
		</div>
		
		<div id="dlg" class="easyui-dialog" style="width:500px;height:500px;padding:10px 20px"
         closed="true" buttons="#dlg-buttons">
         <div class="ftitle">Time Table Slot Information</div>
		<p id="slotinfo" name="slotinfo">
		<table width="100%">
                <tr><td>
                    <a href="javascript:save()" class='button medium green' >
                        Save</a>  
                        </td>
                        <td><a href="javascript:close()" class='button medium green'
                            style="text-decoration: none">Cancel</a>
                        </td>
                    </tr>
                </table>	
		</p>
	 </div>
	<?php  
		if($user_type != 4 and $user_type != 2)
		{
	?>
	 
	<div id="toolbar">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newLearner()">New Learner</a>
    </div>
	
	<div id="teacher_toolbar">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newTeahcer()">New Teacher</a>
    </div>
	<?php
		}
	?>
	 <div id="newLearnerDlg" class="easyui-dialog" style="width:520px;height:550px;padding:10px 20px"
            closed="true" buttons="#dlg-buttons">
        <div class="ftitle"><strong>Learner Information</strong></div>
         <form runat="server" id="frmSaveLearner" name="frmSaveLearner"  >
        <table style="width: 100%" cellspacing=2 cellpadding=2 >   
			<tr>
				<td align="left" colspan=2>
					<input type="hidden" id="user_id" name="user_id" />
				</td>
			</tr>	
            <tr>
                <td style="width: 40%">
                     Learner ID
                </td>
                <td align="left">
                    <input type="text" id="learner_id" name="learner_id"  placeholder="Learner ID" />
                </td>
            </tr>
            <tr>
                <td>
                   Name  
                </td>
                <td>
                    <input type="text" id="name" name="name"  placeholder="Learner Name" />
                </td>
            </tr>
            <tr>
                <td>
                    Surname 
                </td>
                <td>
                    <input type="text" id="surname" name="surname" placeholder="Learner Surname"/>
                </td>
            </tr>
            <tr>
                <td>
                    Potential Score
                </td>
                <td>
                    <input type="text" id="potential_score" name="potential_score"  placeholder="Potential Score" />
                </td>
            </tr>
            <tr>
                <td>
                   Learner Average
                </td>
                <td>
                    <input type="text" id="learner_average" name="learner_average"  placeholder="Learner Average" />
                </td>
            </tr>
            <tr>
                <td>
                    Subject Choice
                </td>
                <td align="left">
                    <select name="subject_choice" id="subject_choice">
                        <option value="1">Grade Choice</option>
                        <option value="2">Learner Choice</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    Current Grade
                </td>
                <td align="left">
                    <select name="current_grade" id="current_grade">
                    </select>
                </td>
            </tr>
			 <tr>
                <td>
                    Current Class
                </td>
                <td align="left">
                    <select name="current_class" id="current_class">
                    </select>
                </td>
            </tr>
			 <tr>
                <td>
                    Next Grade
                </td>
                <td align="left">
                    <select name="next_grade" id="next_grade">
                    </select>
                </td>
            </tr>
			 <tr>
                <td>
                    Next Class
                </td>
                <td align="left">
                    <select name="next_class" id="next_class">
                    </select>
                </td>
            </tr>
			<tr>
                <td>
                    Number of Registered Parents/Guardians
                </td>
                <td align="left">
                    <select name="number_of_parents" id="number_of_parents">
						<option value=1>1</option>
						<option value=2>2</option>
                    </select>
                </td>
            </tr>
			
			<tr id="rw1parent1" name="rw1parent1">
                <td>
                    Parent 1 Name 
                </td>
                <td>
                    <input type="text" id="parent_name_1" name="parent_name_1"  placeholder="Parent Name" />
                </td>
            </tr>
			<tr id="rw2parent1" name="rw2parent1" >
                <td>
                    Parent/Guardian 1 Surname
                </td>
                <td>
                    <input type="text" id="parent_surname_1" name="parent_surname_1"  placeholder="Parent Surname" />
                </td>
            </tr>
			<tr id="rw3parent1" name="rw3parent1">
                <td>
                    Parent/Guardian 1 ID Number
                </td>
                <td>
                    <input type="text" id="parent_id_1" name="parent_id_1"  placeholder="Parent ID Number" />
                </td>
            </tr>
			
			<tr id="rw1parent2" name="rw1parent2" style="visibility:false" >
                <td>
                    Parent 2 Name 
                </td>
                <td>
                    <input type="text" id="parent_name_2" name="parent_name_2"  placeholder="Parent Name" />
                </td>
            </tr>
			<tr id="rw2parent2" name="rw2parent2" style="visibility:false" >
                <td>
                    Parent 2 Surname
                </td>
                <td>
                    <input type="text" id="parent_surname_2" name="parent_surname_2"  placeholder="Parent Surname" />
                </td>
            </tr>
			<tr id="rw3parent2" name="rw3parent2" style="visibility:false">
                <td>
                    Parent 2 ID Number
                </td>
                <td>
                    <input type="text" id="parent_id_2" name="parent_id_2"  placeholder="Parent ID Number" />
                </td>
            </tr>
        </table>
        </form>
		<table width="80%">
		<tr><td>
				<button id="btnSaveLearner" name="btnSaveLearner" onClick="SaveNewLearner()">Save</button>
					</td>
					<td>
						<button id="btnSaveLearner" name="btnSaveLearner" onClick="$('#newLearnerDlg').dialog('close');">Cancel</button>
					</td>
				</tr>
		</table>
    </div>
	
	
		
	</body>
</html>

