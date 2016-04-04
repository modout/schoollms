
function setStudentSettings()
{
	var letters = $("#_classletters").html();
	var ltrs = letters.split(',');
	var grade_id = $("#student_grade").val();
	if(typeof grade_id == "string" && grade_id  == "R")grade_id ="0";
	/*var classes =[];
	for(i=0;i<ltrs.length;i++)
	{
		classes.push("{'class_id':'"+ltrs[i]+"','class_label':'"+ltrs[i]+"'}");
	}
	
	//classes = jQuery.parseJSON(classes);
	alert(classes);*/
	var classes = getJasonData("action=GETSTUDENTCLASS2&letters="+letters);
	classes = jQuery.parseJSON(classes);
	//alert(classes);
	var fromto = $("#_from_grade").html();
	//alert(fromto);
	var data = fromto.split(" to ");
	
	//var start = parseInt(data[0]);
	var start;
	var end = parseInt(data[1]);
	if(typeof data[0] == "string")
	{
		start = 0;
	}
	else{ 
		start = parseInt(data[0]);
	}
	
	if(typeof data[1] == "string")
	{
		end = 12;
	}
	else{
		end = parseInt(data[1]);
	}
	
	
	var grades = getJasonData("action=GETGRADES&from="+start+"&to="+end);
	//alert(grades);
	grades = jQuery.parseJSON(grades);
	//alert(grades);
	//var dataurl = "process.php?action=GETPERSONNEL2&type_id=2&grade_id="+$("#student_grade").val()+"&year_id="+$("#student_year_id").val();
	var school_id = getSchoolID();
	var dataurl = "process.php?action=GETPERSONNEL2&type_id=2&grade_id="+grade_id+"&year_id="+$("#student_year_id").val()+"&end_grade_id="+end+ "&school_id="+school_id;
	//alert(dataurl);
	$('#students').datagrid({
			title:'Grade Learners',
			iconCls:'icon-edit',
			toolbar:'#toolbar',
			width:1500,
			height:450,
			singleSelect:false,
			idField:'userid',
			sortname:'access_id',
			sortorder:'asc', 
			rownumbers:'true',
			pagination:'true',
			url:dataurl,
			onDblClickRow:function(index,row){
				//open your dialog
				//alert(row.user_id);
				var student = getJasonData("action=GETPERSONNEL3&type_id=2&user_id="+row.user_id);
				student = jQuery.parseJSON(student);
				//alert(student);
				//alert(item.name);
				$.each(student, function(i, item) {
					//alert(item.name);
					//alert(item.surname);
					
				});
				var grade = row.grade_title.split(" ");
				grade = String(grade[1]);
				//alert(grade);
				var theclass = row.class_label.split(" ");
				theclass = String(theclass[1]);
				//alert(theclass);
				theclass = theclass.replace(grade,"");		
				theclass.trim();
				
				//alert(theclass + " " + theclass.length);
				if(theclass.length == 3)
				{
					theclass = theclass.substring(2,2);
				}
				if(theclass.length == 4)
				{
					theclass = theclass.substring(3,3);
				}
				
				//alert(theclass + " " + theclass.length);
				addStudentsSetup();
				$("#user_id").val(row.user_id);
				$("#learner_id").val(row.access_id);
				$("#name").val(row.name);
				$("#surname").val(row.surname);
				$("#current_grade").val(row.surname);
				$('#newLearnerDlg').dialog({title: 'New Learner'});
				$('#newLearnerDlg').dialog('open');	
				
			},
			columns:[[
				{field:'ck',checkbox:true,sortable:'true'},
			{field:'userid',hidden:true,sortable:'true'},
				{field:'access_id',width:150,sortable:'true',title:'Learner ID'},
				{field:'name',width:190,sortable:'true',title:'Name'},
				{field:'surname',width:130,align:'right',sortable:'true',title:'Surname'},
				{field:'baseline',width:110,align:'right',editor:'numberbox',title:'Potential Score'},
				{field:'learner_average',width:120,align:'right',editor:'numberbox',title:'Learner Average'},
				{field:'subject_choice',width:120,align:'right',editor:{
											type:'combobox',
											options:{
												valueField:'selectionid',
												textField:'value',
												data:subjectChoice,
												required:true
											}
										},
								formatter:function(value){								   
									for(var i=0; i<subjectChoice.length; i++){
										if (subjectChoice[i].selectionid == value) return subjectChoice[i].value;
									}							
									return value;
								},title:'Subject Choice'},
				{field:'grade_title',width:90,align:'right',sortable:'true',title:'Current Grade'},
				{field:'class_label',width:90,align:'right',sortable:'true',title:'Current Class'},
				{field:'to_grade',width:100,align:'right',editor:{
											type:'combobox',
											options:{
												valueField:'grade_id',
												textField:'grade_title',
												data:grades,
												required:true
											}
										},
								formatter:function(value){	
									//alert('Alert');
									for(var i=0; i<grades.length; i++){
										if (grades[i].grade_id == value) return grades[i].grade_title;
									}							
									return value;
								},title:'Next Grade'},
				{field:'to_class',width:120,align:'right',editor:{
											type:'combobox',
											options:{
												valueField:'class_id',
												textField:'class_label',
												data:classes,
												required:true
											}
										},
								formatter:function(value){	
									//alert('Alert');
									for(var i=0; i<classes.length; i++){
										if (classes[i].class_id == value) return classes[i].class_label;
									}							
									return value;
								},title:'Next Class'}
								
				]],
			onBeforeEdit:function(index,row){
				row.editing = true;
				updateActions(index);
			},
			onAfterEdit:function(index,row){
				row.editing = false;
				updateActions(index);
			},
			onCancelEdit:function(index,row){
				row.editing = false;
				updateActions(index);
			}
		});
	
}