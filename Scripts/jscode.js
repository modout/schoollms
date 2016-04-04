var whorotates= "";
var subjects = [];
var grades = [];
var classes = [];

/*8*******

START FROM PHP FILE

*************/

function getStep1Data()
		{
			//alert($("#schools").val());
			
			var school_id = getUrlParameter("school_id");
			if(school_id == undefined || school_id == "undefined")
			{
				school_id = $("#schools").val();
			}
			
			if($("#schools").val()  == "0")
			{
				alert('Please Select School')
			}
			else{
				if(school_id == undefined )
				{
					school_id = $("#schools").val();
				}
				//alert(school_id);
				getSchoolData(school_id);
				//alert("Step 1");
				next();
			}
		}
		
		 function processSubmit()
		 {
			var theUrl = "getpages.php?"+$("#frmSchoolInfo").serialize();
			$.ajax({  
				type: "GET",  
				url: theUrl,  
				data: "",
				success: function(data) {  
				  $("#capturetable").html(data)
				}  
			  }); 
			//alert(url);
			//alert('Submitting');
		 }
		 
		function save()
		{
			var user_type = getUrlParameter("user_type");
			if(user_type != 4)
			{
				//alert($("#subjectselect").val());
				//alert($("#venueSelect").val());
				//alert($('#periodColumnVal').html());
				var day = $('#dayColumnVal').html().split("</b>");
				day = day[0];
				day = day.replace("<b>","");
				//alert($('#dayColumnVal').html());
				var teacher = $("teacherselect").val();
				//var teacher_id=$("teacherselect").val();
				if(teacher == null || teacher == undefined)
				{
					teacher = $("#teacherColumnVal").html();				
				}
				
				
				var school_id = getUrlParameter("school_id");
				if(school_id == undefined || school_id == "undefined")
				{
					school_id = $("#schools").val();
				}		
				
				var params = "school_id="+school_id+"&year_id="+$("#teachertimetable_year_id").val()+"&grade_id="+$("#teachertimetable_grade_id").val();
				params = params + "&class_id="+$("#timetable_id").val() + "&subject_id="+$("#subjectselect").val()+"&classroom="+$("#venueSelect").val();
				params = params +"&period_time="+$('#periodColumnVal').html()+"&teacher="+teacher+"&teacher_id="+teacher_id+"&day="+day+"&save_type=admin_timetable_slot";
				alert(params);
				sendDataByGet(param,"timetable_save.php");
			}
			else{
				
			}
			alert("Time Table Slot Information Saved.");
						
			$('#dlg').dialog('close');
		}
		
		function close()
		{
			//alert("Time Table Slot Information Saved.");
			$('#dlg').dialog('close');
		}
	  
        function viewTimeTableSlot(information,subject,slotClass,times) {
			var user_type = getUrlParameter("user_type");
			if(user_type == 2)
			{
				//alert("here");
				//return;
			}
			if(user_type == 4)
			{
				//alert(times);
				subject = subject.replace('<b>','');
				subject = subject.replace('</b>','');
				window.open("classlist.php?class="+slotClass+"&subject="+subject+"&timeslot="+times,'1453910749489','width=700,height=500,toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,left=0,top=0');
				return;
				
				var classlist = getJasonData("action=GETSLOTCLASSLIST&class="+slotClass);
				//alert(classlist);
				classlist =  jQuery.parseJSON(classlist)	;
				var table = "<table border=1 id=tblpopup name=tblpopup width='100%'> <tr><td>ID Number</td><td>Student Name</td><td>Image</td><td>Present</td></tr>";
				
				$.each(classlist, function(i, item) {
					table = table+ "<tr><td>"+item.access_id+"</td><td>"+item.name + "  "+item.surname+"</td><td>< img src='action=process.php?GETIMAGE&user_id=2'/></td><td><input type='checkbox' name='"+item.access_id+"_present' value='present' /></td></tr>";
				});
				table = table+"</table>";
				information = table
				$('#slotinfo').html(table);
				//return;
			}
			
			if(user_type == 6)
			{	
				var subselect = "";
				subject = subject.replace('<b>','');
				subject = subject.replace('</b>','');
				
				if($.trim(subject) != "")
				{
					var subjects = getJasonData("action=getsubjects");
					
					subjects =  jQuery.parseJSON(subjects)	;
					subselect = "<select id='subjectselect' name='subjectselect' onchange='getSubjectTeacher(this.value)'>";
					$.each(subjects, function(i, item) {
						//alert(item.subject_id);
						if(item.subject_title == subject)
						{
							subselect = subselect + "<option value='"+item.subject_id+"' selected>"+item.subject_title+"</option>";
						}
						else{
							subselect = subselect + "<option value='"+item.subject_id+"'>"+item.subject_title+"</option>";
						}
					});
					subselect = subselect +"</select>";				
				}
				
				var info = information.split("<br>");
				var teacher = info[1];
				//alert(teacher);
				var teacherSelect = "";
				teacherSelect = "<select id='teacherlist' name='teacherlist'>";
				var teachers = getJasonData("action=GETCLASSTEACHER&class_id="+$("#timetable_id").val());
				teachers = jQuery.parseJSON(teachers);
				//alert(teachers);
				//$("#teacherlist").html("");
				$.each(teachers, function(i, item) {
					//var name = item.access_id + " " + item.name + " " + item.surname;
					var name = item.name + " " + item.surname;
					if(name == teacher)
					{
						teacherSelect = teacherSelect + "<option value='"+item.user_id+"' selected>"+name+"</option>";						
					}
					else{
						teacherSelect = teacherSelect + "<option value='"+item.user_id+"'>"+name+"</option>";
					
					}					
				});
				teacherSelect = teacherSelect + "</select>";
				var mytimes = times.split("~");
				var periodtimes = mytimes[0].split("-");
				mytimes[1] = mytimes[1].replace('<b>','');
				mytimes[1] = mytimes[1].replace('</b>','');
				var theday = mytimes[1].split("<br>");
				
				var days = getJasonData("action=TIMETABLEDAYS");
				days = jQuery.parseJSON(days);
				//alert(days);
				//alert(times);
				var myDays = "<select id='day' name='day' disabled='true'>";
				//alert(mytimes[2]);
				$.each(days, function(i, item) {
					//alert(days[i].day_label);
					if(days[i].day_label.trim() == theday[0].trim())
					{
						myDays = myDays + "<option value='"+days[i].day_id+"' selected>"+days[i].day_label+"</option>";
					}
					else{
						myDays = myDays +  "<option value='"+days[i].day_id+"' >"+days[i].day_label+"</option>";
					}
					
				});
				myDays = myDays +"</select>";
				
				var rooms = getJasonData("action=GETROOMS");
				rooms = jQuery.parseJSON(rooms);
				var theRooms = "<select id='room' name='room'>";
				$.each(rooms, function(i, item) {
					theRooms = theRooms +  "<option value='"+rooms[i].room_id+"' >"+rooms[i].room_label+"</option>";
				});
				
				theRooms = theRooms+ "</select>";
				
				var table = "<table><tr><td>Subject</td><td>:SUBJECTS</td></tr><tr><td>Teacher List</td><td id='teachers' name='teachers' >:TEACHER</td></tr><tr>";
				table = table + "</tr><tr><td>Day</td><td>:DAYS</td></tr>";
				//table = table + "<tr><td>Start Time</td><td id='starttime' name='starttime'>:STARTTIME</select></td></tr><tr><td>End Time</td><td id='endtime' name='endtime'>";
				//table = table + ":ENDTIME</td></tr><tr></tr>";
				table = table + "<tr><td>Room</td><td>:ROOM</td></tr>";
				table = table + "<tr><td><input type='button' value='Save' name='btnSaveTimeTableSlot' id='btnSaveTimeTableSlot' onClick='saveTimeTableSlot()' /></td>";
				table = table +"<td><input type='button' value='Cancel' name='btnCancelTimeTableSlot' id='btnCancelTimeTableSlot' onclick=\"$('#dlg').dialog('close')\"/></td></tr></table>";
				
				
				table = table.replace(":SUBJECTS",subselect);
				table = table.replace(":TEACHER",teacherSelect);
				table = table.replace(":STARTTIME",periodtimes[0]);
				table = table.replace(":ENDTIME",periodtimes[1]);
				table = table.replace(":DAYS",myDays);	
				table = table.replace(":ROOM",theRooms);				
				
				$('#slotinfo').html(table);
				$("#teachers").html(teacherSelect);
				
				$('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Timetable Slot');
				
			}
        }
		
		function saveTimeTableSlot()
		{
			//alert('Subject ID '+ $("#subjectselect").val());
			//alert('Teacher ID '+ $("#teacherlist").val());
			//alert('Day ID '+ $("#day").val());
			//alert('starttime '+ $("#starttime").html());
			//alert('endtime '+ $("#endtime").html());
			var param = "subject_id="+$("#subjectselect").val()+"&teacher_id="+ $("#teacherlist").val()+"&day_id="+$("#day").val();
			param =param + "&start_time="+$("#starttime").html()+"&endtime="+$("#endtime").html()+"&timetable_id="+$("#timetable_id").val();
			param = param + "&year_id="+$("#teachertimetable_year_id").val()+"&grade_id="+$("#teachertimetable_grade_id").val();
			param = param + "&room_id="+$("#room").val()+"&save_type=save_timetable_slot";
			//param = param + "&timetable_teacher_id="+$("#teacher_list").val();
			//param = param + "&timetable_learner_id="+$("#class_list").val();
			param = param + "&timetable_user_id="+($("#teacher_list").val() == 0?$("#class_list").val():$("#teacher_list").val());
			var school_id = getUrlParameter("school_id");
			if(school_id == undefined || school_id == "undefined")
			{
				school_id = $("#schools").val();
			}			
			param = param + "&school_id="+school_id;
			alert("WTF 1 : "+ param);
			sendDataByGet(param,'timetable_save.php');
			getTimetable($("#timetable_id").val());
			getClassList($("#timetable_id").val());
			alert("Timetable Slot Changes Saved Changes");
			//alert(param);			
		}
		
		function addNewTimeTableSlot()
		{			
			var subselect = "";
			var subjects = getJasonData("action=getsubjects");
			var school_id = getUrlParameter("school_id"); 
			if(school_id == undefined || school_id == "undefined") 
			{ 
				school_id = $("#schools").val(); 
			}
			
			subjects =  jQuery.parseJSON(subjects)	;
			subselect = "<select id='subjectselect' name='subjectselect' onchange='getSubjectTeacher(this.value)'>";
			$.each(subjects, function(i, item) {
				subselect = subselect + "<option value='"+item.subject_id+"'>"+item.subject_title+"</option>";
			});
			subselect = subselect +"</select>";
			
			var teacherSelect = "";
			teacherSelect = "<select id='teacherlist' name='teacherlist'>";
			var teachers = getJasonData("action=GETCLASSTEACHER&class_id="+$("#timetable_id").val());
			teachers = jQuery.parseJSON(teachers);
			$.each(teachers, function(i, item) {
				var name = item.name + " " + item.surname;
				teacherSelect = teacherSelect + "<option value='"+item.user_id+"'>"+name+"</option>";			
			});
			teacherSelect = teacherSelect + "</select>";
			
			var days = getJasonData("action=TIMETABLEDAYS");
			days = jQuery.parseJSON(days);
			var myDays = "<select id='day' name='day'>";
			$.each(days, function(i, item) {
				myDays = myDays +  "<option value='"+days[i].day_id+"' >"+days[i].day_label+"</option>";				
			});
			myDays = myDays + "</select>";
			var rooms = getJasonData("action=GETROOMS");
			rooms = jQuery.parseJSON(rooms);
			var theRooms = "<select id='room' name='room'>";
			$.each(rooms, function(i, item) {
				theRooms = theRooms +  "<option value='"+rooms[i].room_id+"' >"+rooms[i].room_label+"</option>";
			});
			theRooms = theRooms+ "</select>";
			alert("Here");
			var periods = getJasonData("action=GETPERIODS&school_id="+school_id);
			alert(periods);
			periods = jQuery.parseJSON(periods);
			var thePeriods = "<select id='period_id' name='period_id'>";
			$.each(periods, function(i, item) {
				thePeriods = thePeriods +  "<option value='"+periods[i].period_label_id+"' >"+periods[i].period_label+"</option>";
			});
			
			thePeriods = thePeriods+ "</select>";
			
			var startHour = "<select name='starthour' id='starthour'>";
			var endHour = "<select name='endhour' id='endhour'>";
			
			for(i=1;i<=24;i++)
			{
				var z =i;
				if(i < 10)
				{
					z = "0"+z;
				}
				startHour =startHour+ "<option value='"+z+"'>"+z+"<option>";
				endHour =endHour+ "<option value='"+z+"'>"+z+"<option>";
			}				
			startHour = startHour + "</select>";
			endHour = endHour + "</select>";
			
			var starMinute = "<select name='startminute' id='startminute' >";
			var endMinute = "<select name='endminute' id='endminute' >";			
			
			for(i = 0;i<60;i=i+5)
			{
				var z =i;
				if(i < 10)
				{
					z = "0"+z;
				}
				starMinute =starMinute+ "<option value='"+z+"'>"+z+"<option>";
				endMinute =endMinute+ "<option value='"+z+"'>"+z+"<option>";
			}
			
			starMinute = starMinute + "</select>";
			endMinute = endMinute + "</select>";
			var starttime = startHour + " : "+ starMinute;
			var endtime = endHour + " : "+ + endMinute;
			
			var table = "<table><tr><td>Subject</td><td>:SUBJECTS</td></tr><tr><td>Teacher List</td><td id='teachers' name='teachers' >:TEACHER</td></tr><tr>";
			table = table + "</tr><tr><td>Day</td><td>:DAYS</td></tr>";
			table = table + "<tr><td>Period</td><td id='starttime' name='starttime'>:STARTTIME</select></td></tr>";
			table = table + "<tr><td>Room</td><td>:ROOM</td></tr>";
			table = table + "<tr><td><input type='button' value='Save' name='btnSaveTimeTableSlot' id='btnSaveTimeTableSlot' onClick='addTimeTableSlot()' /></td>";
			table = table +"<td><input type='button' value='Cancel' name='btnCancelTimeTableSlot' id='btnCancelTimeTableSlot' onclick=\"$('#dlg').dialog('close')\"/></td></tr></table>";
			
			
			table = table.replace(":SUBJECTS",subselect);
			table = table.replace(":TEACHER",teacherSelect);
			table = table.replace(":STARTTIME",thePeriods);
			table = table.replace(":ENDTIME",endtime);
			table = table.replace(":DAYS",myDays);	
			table = table.replace(":ROOM",theRooms);
			$('#slotinfo').html(table);
			$("#teachers").html(teacherSelect);
				
			$('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Add Timetable Slot');			
			
		}
		
		
		function addTimeTableSlot()
		{
			var param = "subject_id="+$("#subjectselect").val()+"&teacher_id="+ $("#teacherlist").val()+"&day_id="+$("#day").val();
			param =param + "&period_id="+$("#period_id").val()+"&timetable_id="+$("#timetable_id").val();
			param = param + "&year_id="+$("#teachertimetable_year_id").val()+"&grade_id="+$("#teachertimetable_grade_id").val();
			param = param + "&room_id="+$("#room").val()+"&save_type=save_timetable_slot";
			//param = param + "&timetable_teacher_id="+$("#teacher_list").val();
			//param = param + "&timetable_learner_id="+$("#class_list").val();
			param = param + "&timetable_user_id="+($("#teacher_list").val() == 0?$("#class_list").val():$("#teacher_list").val());
			var school_id = getUrlParameter("school_id");
			if(school_id == undefined || school_id == "undefined")
			{
				school_id = $("#schools").val();
			}			
			param = param + "&school_id="+school_id;
			alert("WTF 2 : "+ param);
			sendDataByGet(param,'timetable_save.php');
			getTimetable($("#timetable_id").val());
			getClassList($("#timetable_id").val());
			alert("Timetable Slot Changes Saved Changes");
		}
		
		
		function getTimetable(timetableID)
		{
			var user_type = getUrlParameter("user_type");
			var user_id = getUrlParameter("user_id");
			var year_id = $("#teachertimetable_year_id").val();
			//alert("We are here");
			//alert(user_type);
			//alert(user_id);
			//alert(year_id);
			var school_id = getUrlParameter("school_id");
			if(school_id == undefined || school_id == "undefined")
			{
				school_id = $("#schools").val();
			}
			var timetable = "";
			var params = "";
			if(user_id == null || user_id == undefined)
			{
				params = "action=PRINT_DAYS&id="+timetableID+"&user_type="+user_type+"&year_id="+year_id+"&user_id=0"+"&school_id="+school_id;
				//alert(params);
				timetable = getJasonData(params);
			}
			else{
				//alert(user_id);
				params = "action=PRINT_DAYS&id="+timetableID+"&user_type="+user_type+"&user_id="+user_id+"&year_id="+year_id+"&school_id="+school_id;
				//alert(params);
				timetable = getJasonData(params);
			}
			alert(params);
			$("#timetable").html(timetable);
		}
		
		var teacher_id = 0;
		function getTeacherName(id)
		{
			teacher_id = id;
			var MyRows = $('table#tblpopup').find('tbody').find('tr');
			var teachers  = getJasonData("action=GETTEACHER&id="+id);
			teachers =  jQuery.parseJSON(teachers)	;
			$.each(teachers, function(i, item) {
				//alert(item.subject_id);
				//teacherselect = teacherselect + "<option value='"+item.user_id+"'>"+item.teacher_name+"</option>";
				$(MyRows[1]).find('td:eq(1)').html(item.name+ " " +item.surname);
			});
		}
		
		function getSubjectTeacher(subject_id){		
			//alert(subject_id);
			var MyRows = $('table#tblpopup').find('tbody').find('tr');
			var teachers  = getJasonData("action=GETSUBJECTTEACHER&subject_id="+subject_id);
			teachers =  jQuery.parseJSON(teachers)	;
			var teacherselect = "<select id='teacherlist' name='teacherlist' onchange='getTeacherName(this.value)'";
			teacherselect = teacherselect + "<option value='select'>Select Teacher</option>";
			$.each(teachers, function(i, item) {
				//alert(item.subject_id);
				teacherselect = teacherselect + "<option value='"+item.user_id+"'>"+item.teacher_name+"</option>";
				
			});
			teacherselect = teacherselect + "</select>";
			//alert(teacherselect);
			//alert($("#teachers").html());
			$("#teachers").html(teacherselect);
			//$(MyRows[1]).find('td:eq(1)').html(teacherselect);
		}
		
		
		
/***************

END ROM PHP FILE

***************/		

function getstudents()
{
	var xmlhttp;
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp = new XMLHttpRequest();
	}
	else {// code for IE6, IE5
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	var url = "process.php?action=GETSTUDENTS&t=" + Math.random();
	//alert(url);
	xmlhttp.open("GET", url, true);
	xmlhttp.send();
	students =  xmlhttp.responseText;
	
}

function setTab() {
    var tab = $('#tabs').tabs('getSelected');
	var index = $('#tabs').tabs('getTabIndex', tab);
	//alert(index);
	
	if(index== 2)
	{
		SettingsPerRotation();
		classSettings = [];
	}
	var user_type = getUrlParameter("user_type");
	if(index== 3 || (index== 2 && (user_type == 5 || user_type == 6)))
	{
		//getSubjectData();
		
		//var students = getJasonData("action=GETSTUDENTS");	
		//alert(students);
		setStudentSettings();
		var dg = $('#students');
		/*$('#students').datagrid({singleSelect:false,remoteFilter:true,enableFilter:true, 
					onLoadSuccess:function(data){
					  
						//var rows = $('#students').datagrid('getRows');
						//for(var i=0; i<rows.length; i++){
						//	$('#students').datagrid('beginEdit', i);
						//}
				   },
				   onCheck:function(index,row){onStudentClickCell(index,row);}});*/
				   
		//alert("this is the tab " + index);
		
		$('#students').datagrid('enableFilter'[{
				field:'name',
				type:'textbox',
				options:{precision:1},
				op:['equal','notequal','less','greater']
			}]);
		
	}
	//alert("here again");
}

var getUrlParameter = function getUrlParameter(sParam) {
		var sPageURL = decodeURIComponent(window.location.search.substring(1)),
		sURLVariables = sPageURL.split('&'),
		sParameterName,
		i;

		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');

			if (sParameterName[0] === sParam) {
				return sParameterName[1] === undefined ? true : sParameterName[1];
			}
		}
	};
	
function next()
{
	var tab = $('#tabs').tabs('getSelected');
	var index = $('#tabs').tabs('getTabIndex', tab);            
	var indx = index + 1;
	//alert(indx);
	if($('#tabs').tabs('exists', indx))
	{
		$('#tabs').tabs('enableTab', indx);
		//alert(indx);
		$('#tabs').tabs('select', indx); // switch to third tab
		setTab();
	}
	return false;
}	

function previous()
{
	var tab = $('#tabs').tabs('getSelected');
	var index = $('#tabs').tabs('getTabIndex', tab);
	var indx = index - 1;
	if($('#tabs').tabs('exists', indx))
	{
		$('#tabs').tabs('enableTab', indx);
		$('#tabs').tabs('select', indx); // switch to third tab
		setTab();
	}
	return false;
}



function getJasonData(params) {
	//alert(params);
	var xmlhttp;
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp = new XMLHttpRequest();
	}
	else {// code for IE6, IE5
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	var url = "process.php?" + params + "&t=" + Math.random();
	//alert(url);
	xmlhttp.open("GET", url, false);
	xmlhttp.send();
	return xmlhttp.responseText;
}

function sendDataByGet(params,tofile) {
	//alert(params);
	var xmlhttp;
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp = new XMLHttpRequest();
	}
	else {// code for IE6, IE5
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	var url = tofile+"?" + params + "&t=" + Math.random();
	//alert(url);
	xmlhttp.open("GET", url, false);
	xmlhttp.send();
	//alert(xmlhttp.responseText);
	return xmlhttp.responseText;
}


function getSchoolData(school_id)
{
	//alert("here we are");
	var setting = getJasonData("action=GETSCHOOLSETTING&school_id="+school_id);	
	//alert(setting);
	setting =  jQuery.parseJSON(setting);
	var school = getJasonData("action=GETSCHOOLINFO&school_id="+school_id);		
	//alert(school);
	school =  jQuery.parseJSON(school)	;
	
	if (setting.length === 0) {
		$("instructions").html(" The timetable settings are not captured ");
	}
	var from_grade = 0;
	var to_grade = 1;
	$.each(setting, function(i, item) {
		var data = setting[i].split("=");
		if(data[0] == "from_grade")
		{
			//alert(data[1]);			
			from_grade = data[1];
			if(data[1] == "R")from_grade = "0";
		}
		if(data[0] == "to_grade")
		{
			$("#_from_grade").html($("#_from_grade").html() +" to "+ data[1]);
			to_grade = data[1];
		}
		else{
			$("#_"+data[0]).html(data[1]);
			if(data[0] == "rotation_type")
			{
				whorotates = data[1];
				$("#_selected_rotation").html(whorotates);
			}
		}
		if(data[0] == "break_times"){
			
			var breaks = data[1].split("*");
			for(i =0;i<breaks.length;i++)
			{
				var times = breaks[i].split("!");
				//alert( times);
				$("#_break_"+(i+1)).html("At " + times[1] + " for " + times[2] + " minutes");
				$("#break_time_"+(i+1)).val(times[1]);
				$("#break_length_"+(i+1)).val(times[2]);
				$("#row_break_"+(i+1)).show();
			}
		}
		
		$("#"+data[0]).val(data[1]);
		$("#school_id").val(school_id);
	});
	from_grade = parseInt(from_grade);
	if(typeof from_grade == "string")
	{
		from_grade = 0;
	}
	//alert(from_grade);
	to_grade = parseInt(to_grade);
	$('#teachertimetable_grade_id').html("");
	$('#teachertimetable_grade_id').append(
			$('<option></option>').val("All").html("All")
		); 
	for(i = from_grade;i<=to_grade;i++)
	{
		//alert(i);
		if(from_grade == "0" && i == from_grade)
		{
			$('#teachertimetable_grade_id').append(
				$('<option></option>').val(i).html("R")
			);
		}
		else{
			$('#teachertimetable_grade_id').append(
				$('<option></option>').val(i).html(i)
			);
		}
		 
	}
	var school_name = school.school_name;
	$("#_schoolname").html(school_name +" Timetable Settings" );
	/*$.each(school, function(i, item) {
		$("#_schoolname").html(school[i].school_name);
	});*/
} 

function saveSettings1()
{
	alert($("#from_grade").val() );
	//alert($("#to_grade").val() );
	
	var school_id = getUrlParameter("school_id");
	if(school_id == undefined || school_id == "undefined")
	{
		school_id = $("#schools").val();
	}		
	if($("#from_grade").val() == "")
	{
		alert("Please select From Grade");
	}
	else
	 if($("#to_grade").val() == "")
	 {
		alert("Please select To Grade");
	 }
	 else{
		whorotates = $("#rotation_type").val();
		//alert($("#frmSchoolInfo").serialize());
		//alert($("#frmSchoolInfo").serialize()+"&save_type=save_general_timetable_settings");
		var param = $("#frmSchoolInfo").serialize()+"&save_type=save_general_timetable_settings";		
		var school_id = getUrlParameter("school_id");
		if(school_id == undefined || school_id == "undefined")
		{
			school_id = $("#schools").val();
		}		
		param = param + "&school_id="+school_id;
		alert(param);
		sendDataByGet(param,"timetable_settings_save.php");
		getSchoolData(school_id);
	}
}

function saveSubjectSettings()
{
	//alert($("#frmSubjectSetting").serialize()+"&school_id="+$("#schools").val()+ "&grade_id="+$("#grade_id").val());
	var school_id = getUrlParameter("school_id");
	if(school_id == undefined || school_id == "undefined")
	{
		school_id = $("#schools").val();
	}			
	
	var grade;
	if($("#grade_id").val() == "R")
	{
		grade = "0";
	}
	else{
		grade = $("#grade_id").val();
	}
	//var value = $("#frmSubjectSetting").serialize()+"&school_id="+school_id+ "&grade_id="+$("#grade_id").val();
	var value = $("#frmSubjectSetting").serialize()+"&school_id="+school_id+ "&grade_id="+grade;
	alert(value);
	sendDataByGet(value,"timetable_subject_settings_save.php");
	getSchoolData(school_id);
}

function getSubjectData()
{
	//alert($("#_from_grade").html());
	var fromto = $("#_from_grade").html();
	//alert(fromto);
	var data = fromto.split(" to ");
	//alert(data[1]);
	//alert(typeof data[0]);
	var start;
	if(data[0] == "R")
	{
		start = 0;
	}
	else{ 
		start = parseInt(data[0]);
	}
	var end = parseInt(data[1]);
	//for (i = data[0]; i <= data[1]; i++)
	$('#grade_id').html("");
	$('#student_grade').html("");
	$('#teacher_grade').html("");
	$('#teacher_grade').html("");
	//alert("But We are here");
	//alert(start);
	//alert(end);
	for (i = start; i <= end; i++)
	{
		var z = i;
		if(i == "0")
		{
			z = "R";
		}
		$('#grade_id').append(
			$('<option></option>').val(z).html(z)
		); 
			
		$('#student_grade').append(
			$('<option></option>').val(z).html(z)
		); 
		
		$('#teacher_grade').append(
			$('<option></option>').val(z).html(z)
		); 		
	}
	//alert("here 1");
	next();
	//alert("here 2");
}

function gradeSubjectSetting(data)
{
	$.each(data, function(i, item) {
		var grade_setting = item.grade_setting;
		$.each(item.subject_info,function(j,jitem){
			//alert(jitem);
			var jdata = jitem.split("=");
			//alert(jdata[1]);			
			$("#"+jdata[0]).val(jdata[1]);
			if(jdata[0].indexOf("color") > -1)
			{				
				if($.trim(jdata[1]) == "")
				{
					jdata[1] = "FFF677";
				}
				//alert(jdata[0] + " = " +jdata[1]);
				$("#the"+jdata[0]).val(jdata[1]);
				$("#btn"+jdata[0]).val(jdata[1]);	
				$("#_"+jdata[0]).val(jdata[1]);				
				$( "#"+jdata[0] ).focus();
				$( "#_"+jdata[0] ).focus();
				$( "#the"+jdata[0] ).focus();
				$( "#btn"+jdata[0] ).focus();
			}
			else{
				$("#_"+jdata[0]).html(jdata[1]);
				$( "#_"+jdata[0] ).focus();
			}
		});
		//alert(grade_setting);
		
		var gsettingdata = grade_setting.split(":");
		var data = gsettingdata[1].split(",");
		$.each(data,function(j,ditem){
			var ddata = ditem.split("=");
			$("#"+ddata[0]).val(ddata[1]);
			if(ddata[0].indexOf("color") > -1)
			{				
				if($.trim(ddata[1]) == "")
				{
					ddata[1] = "FFF677";
				}
				//alert(ddata[0] + " = " +ddata[1]);
				$("#the"+ddata[0]).val(ddata[1]);
				$("#btn"+ddata[0]).val(ddata[1]);	
				$("#_"+ddata[0]).val(ddata[1]);				
				$( "#"+ddata[0] ).focus();
				$( "#_"+ddata[0] ).focus();
				$( "#the"+ddata[0] ).focus();
				$( "#btn"+ddata[0] ).focus();
			}
			else{
				$("#_"+ddata[0]).html(ddata[1]);
				$( "#_"+ddata[0] ).focus();
			}
		});
		//$("#color").val(item.subject_info[0].color);
		
	});
}

function SaveStudentGrade()
{
	var numSelected = selectedStudents.length;
	//alert(numSelected);
	for(var i=0; i<numSelected; i++){
		//var rowIndex = $("#tblTeacheSetting").datagrid("getRowIndex", rows[i]);	
		//alert(rowIndex);		
		$('#students').datagrid('endEdit', selectedStudents[i]);	
	}
	
	var rows = $('#students').datagrid('getRows');
	
	for(var i=0; i<numSelected; i++){
		var user_id = rows[selectedStudents[i]].user_id;
		var grade_id = $("#student_grade").val();
		var school_id = getUrlParameter("school_id");
		if(school_id == undefined || school_id == "undefined")
		{
			school_id = $("#schools").val();
		}			
		var year_id = $("#student_year_id").val();
		var baseline = rows[selectedStudents[i]].baseline;
		var learner_average = rows[selectedStudents[i]].learner_average;
		var subject_choice = rows[selectedStudents[i]].subject_choice;
		var to_grade = rows[selectedStudents[i]].to_grade;
		var to_class = rows[selectedStudents[i]].to_class;
		var grade_title = rows[selectedStudents[i]].grade_title;
		var class_label = rows[selectedStudents[i]].class_label;
		//alert(subject_choice);
		//alert(learner_average);
		//alert(baseline);
		var param = "user_id="+user_id+"&grade_id="+grade_id+"&school_id="+school_id+"&baseline="+baseline+"&learner_average="+learner_average+
			"&subject_choice="+subject_choice+"&year_id="+year_id+"&number_of_leaners_per_class="+$("#number_of_learners").val()+
			"&next_grade="+to_grade+"&next_class="+to_class+"&current_grade="+grade_title+"&current_class="+class_label;
		//alert(param);
		sendDataByGet(param,"timetable_learner_settings_save.php");
	}
	
	numSelected = selectedStudents.length;
	var selectedrows = $('#students').datagrid('getSelections');
	while(selectedrows.length > 0)
	{
		var selectedrow = $('#students').datagrid('getSelected');
		var rowIndex = $("#students").datagrid("getRowIndex", selectedrow);
		//alert(rowIndex);
		$('#students').datagrid('deleteRow', rowIndex);
		selectedrows = $('#students').datagrid('getSelections');
	}	
	
	selectedStudents = [];
	
}

function SaveTeacherSetting()
{
	//var rows = $('#tblTeacheSetting').datagrid('getSelections');
	var numSelected = selectedTeachers.length;
	//alert(numSelected);
	for(var i=0; i<numSelected; i++){
		//var rowIndex = $("#tblTeacheSetting").datagrid("getRowIndex", rows[i]);	
		//alert(rowIndex);		
		$('#tblTeacheSetting').datagrid('endEdit', selectedTeachers[i]);	
	}
	
	var rows = $('#tblTeacheSetting').datagrid('getRows');
	for(var i=0; i<numSelected; i++){
		var row = rows[selectedTeachers[i]];
		var user_id = rows[selectedTeachers[i]].user_id;
		//alert(user_id);
		var school_id = getUrlParameter("school_id");
		if(school_id == undefined || school_id == "undefined")
		{
			school_id = $("#schools").val();
		}			
		var grade_id = $("#teacher_grade").val();
		var year_id = $("#year_id").val();
		var user_id = rows[selectedTeachers[i]].user_id;
		//alert(user_id);
		var substitute = rows[selectedTeachers[i]].substitute;
		//alert(substitute);
		var number_periods = rows[selectedTeachers[i]].number_periods;
		//alert(number_periods);
		var param = "school_id="+school_id+"&grade_id="+grade_id+"&year_id="+year_id+"&user_id="+user_id+"&substitute="+substitute+"&number_periods="+number_periods;
		param = param + "&subject_id="+$("#teacher_subject_id").val();
		//alert(param);
		sendDataByGet(param,"timetable_teacher_settings_save.php");
	}
		
	var selectedrows = $('#tblTeacheSetting').datagrid('getSelections');
	while(selectedrows.length > 0)
	{
		var selectedrow = $('#tblTeacheSetting').datagrid('getSelected');
		var rowIndex = $("#tblTeacheSetting").datagrid("getRowIndex", selectedrow);
		//alert(rowIndex);
		$('#tblTeacheSetting').datagrid('deleteRow', rowIndex);
		selectedrows = $('#tblTeacheSetting').datagrid('getSelections');
	}	
	selectedTeachers = [];
	
}

var selectedStudents = [];

function onStudentClickCell(index,row)
{
	var rows = $('#students').datagrid('getRows');
	var row = rows[index];
	//alert(rows[index].ck);
	//alert(rows[index].access_id);
	if (row.editing){
		$('#students').datagrid('endEdit', index);
	}
	else{
		$('#students').datagrid('beginEdit', index);
		selectedStudents.push(index);
	}
	
}

function onTeacherClickCell(index,row)
{
	var rows = $('#tblTeacheSetting').datagrid('getRows');
	var row = rows[index];
	if (row.editing){
		$('#tblTeacheSetting').datagrid('endEdit', index);		
	}
	else{
		$('#tblTeacheSetting').datagrid('beginEdit', index);
		selectedTeachers.push(index);
	}
}
var classSettings = [];
function onClassVenueClickCell(index,row)
{
	var rows = $('#tblClassVenueSetting').datagrid('getRows');
	var row = rows[index];
	if (row.editing){
		$('#tblClassVenueSetting').datagrid('endEdit', index);		
	}
	else{
		$('#tblClassVenueSetting').datagrid('beginEdit', index);
		classSettings.push(index);
	}
}

function SaveClassSetting()
{
	var numSelected = classSettings.length;
	//alert(numSelected);
	for(var i=0; i<numSelected; i++){
		//var rowIndex = $("#tblTeacheSetting").datagrid("getRowIndex", rows[i]);	
		//alert(rowIndex);		
		$('#tblClassVenueSetting').datagrid('endEdit', classSettings[i]);	
	}
	var rows = $('#tblClassVenueSetting').datagrid('getRows');
	for(var i=0; i<numSelected; i++){
		var school_id = getUrlParameter("school_id");
		if(school_id == undefined || school_id == "undefined")
		{
			school_id = $("#schools").val();
		}			
		var year_id = $("#class_year_id").val(); 
		var params = "school_id="+school_id+"&year_id="+year_id;
		if(whorotates == "Learner Rotates")
		{
			var user_id = rows[classSettings[i]].user_id;
			params = params + "&user_id="+user_id;
		}
		else{
			var class_id = rows[classSettings[i]].class_id;
			params = params + "&class_id="+class_id;
		}
		var room_id = rows[classSettings[i]].room_id;
		params = params+ "&room_id="+room_id;
		alert(params);
		sendDataByGet(params,"timetable_class_settings_save.php");
	}
	//alert(whorotates);
}

function btnViewTimeTable()
{
	viewTimeTable(0);
	next();
}

function viewTimeTable(timetable_label)
{
	var user_type = getUrlParameter("user_type");
	
	//alert(timetable_label);
	 
	if(timetable_label == undefined || timetable_label == "undefined")
	{
		timetable_label = 0;
	}
	var school_id = getUrlParameter("school_id");
	if(school_id == undefined || school_id == "undefined")
	{
		school_id = $("#schools").val();
	}
	alert("action=TIMETABLESELECT&school_id="+school_id+"&user_type="+user_type+"&timetable_label="+timetable_label);
	var timetableid = getJasonData("action=TIMETABLESELECT&school_id="+school_id+"&user_type="+user_type+"&timetable_label="+timetable_label);
	alert(timetableid);
	timetableid =  jQuery.parseJSON(timetableid);
	$('#timetable_id').html("");
	$.each(timetableid, function(i, item) {
		//alert(timetableid[i].timetabl_id);
		$('#timetable_id').append(
                $('<option></option>').val(item.timetabl_id).html(item.timetable_label)
            ); 
	});
	
	//next();
}

function newLearner()
{
	 addStudentsSetup();
	$('#newLearnerDlg').dialog({title: 'New Learner'});
	$('#newLearnerDlg').dialog('open').dialog('center');	
}

function newTeahcer()
{
	//addStudentsSetup();
	$("#user_id").val("");
	$("#teacher_id").val("");
	$("#teacher_initials").val("");
	$("#teacher_surname").val("");
	$('#newTeacherDlg').dialog({title: 'New Teacher'});
	$('#newTeacherDlg').dialog('open').dialog('center');	
}

function addStudentsSetup()
{
	$("#user_id").val("");
	$("#learner_id").val("");
	$("#name").val("");
	$("#surname").val("");
	$("#current_grade").val("");
	var letters = $("#_classletters").html();
	var classes = getJasonData("action=GETSTUDENTCLASS2&letters="+letters);
	classes = jQuery.parseJSON(classes);
	//alert(classes);
	var fromto = $("#_from_grade").html();
	//alert(fromto);
	var data = fromto.split(" to ");
	var start = parseInt(data[0]);
	if(typeof data[0] == "string" && start == "R")
	{
		start = "0";
	}
	var end = parseInt(data[1]);
	//alert("action=GETGRADES&from="+start+"&to="+end);
	var grades = getJasonData("action=GETGRADES&from="+start+"&to="+end);
	grades=  jQuery.parseJSON(grades)	;
	//alert(grades);
	$('#current_grade').html("");
	$('#current_grade').append(
		$('<option></option>').val("").html("")
	); 
	
	$('#next_grade').html("");
	$('#next_grade').append(
		$('<option></option>').val("").html("")
	); 
	
	
	
	$.each(grades, function(i, item) {
		$('#current_grade').append(
			$('<option></option>').val(item.grade_id).html(item.grade_title)
		); 
		
		$('#next_grade').append(
			$('<option></option>').val(item.grade_id).html(item.grade_title)
		); 
		
	});
	$('#current_class').html("");
	$('#current_class').append(
			$('<option></option>').val("").html("")
		); 
		
		$('#next_class').html("");
		$('#next_class').append(
			$('<option></option>').val("").html("")
		); 
	
	$.each(classes, function(i, item) {
		$('#current_class').append(
			$('<option></option>').val(item.class_id).html(item.class_label)
		); 
		
		$('#next_class').append(
			$('<option></option>').val(item.class_id).html(item.class_label)
		); 
	});
	
	
}

function getSchoolID()
{
	var school_id = getUrlParameter("school_id");
	if(school_id == undefined || school_id == "undefined")
	{
		school_id = $("#schools").val();
	}	
	return school_id;
}

function SaveNewLearner()
{
	//alert($("#user_id").val());
	var params = "";
	
	var school_id = getUrlParameter("school_id");
	if(school_id == undefined || school_id == "undefined")
	{
		school_id = $("#schools").val();
	}		
	if($("#user_id").val() == "")
	{
		//alert("save");
		params = $("#frmSaveLearner").serialize()+"&save_type=new_learner&school_id="+school_id+"&year_id="+$("#student_year_id").val();
	}
	else
	{
		//alert("Edit");
		params = $("#frmSaveLearner").serialize()+"&save_type=update_learner&school_id="+school_id+"&year_id="+$("#student_year_id").val();
	}
	params = params + "&number_of_learners="+$("#number_of_learners").val();
	//alert(params);
	
	$('#newLearnerDlg').dialog('close');
	sendDataByGet(params,"timetable_save.php");
	setStudentSettings();
	//$("#frmSaveLearner").reset();
	//$('#frmSaveLearner :input').each(function(){this.val("");});;
	alert("Student Information saved");
}

function SaveNewTeacher()
{
	//alert($("#year_id").val());
	var school_id = getUrlParameter("school_id");
	if(school_id == undefined || school_id == "undefined")
	{
		school_id = $("#schools").val();
	}			
	var params = "";
	if($("#user_id").val() == "")
	{
		params = $("#frmSaveTeacher").serialize()+"&save_type=new_teacher&school_id="+school_id+"&year_id="+$("#year_id").val();
	}
	else{
		params = $("#frmSaveTeacher").serialize()+"&save_type=update_teacher&school_id="+school_id+"&year_id="+$("#year_id").val();
	}
	params = params + "&subject_id="+$("#teacher_subject_id").val()+"&grade_id="+$("#teacher_grade").val();
	//alert(params);
	sendDataByGet(params,"timetable_save.php");
	getTeacherSettings();
	alert("Teacher Information saved");
	$('#newTeacherDlg').dialog('close');
	
}

function getClassList(class_id)
{
	//alert("action=GETCLASSLIST&class_id="+class_id+"&year_id="+$("#teachertimetable_year_id").val());
	var school_id = getUrlParameter("school_id");
	if(school_id == undefined || school_id == "undefined")
	{
		school_id = $("#schools").val();
	}	
	var classes = getJasonData("action=GETCLASSLIST&class_id="+class_id+"&year_id="+$("#teachertimetable_year_id").val()+"&school_id="+school_id);
	alert("action=GETCLASSLIST&class_id="+class_id+"&year_id="+$("#teachertimetable_year_id").val()+"&school_id="+school_id);
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
	//alert(class_id);
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
	
}

function openWindow(url)
{
	window.open(url,'1453910749489','width=700,height=500,toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,left=0,top=0');
}