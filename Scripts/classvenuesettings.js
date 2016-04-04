var venues = [
				{room_id:'1',room_label:'Grade Choice'},
				{room_id:'2',room_label:'Learner Choice'}
			];		
			 
	
	venues = getJasonData("action=GETVENUES&type=1");
	venues =  jQuery.parseJSON(venues);
	
	
	
	
	function SettingsPerRotation()
	{
		var rotation = $("#rotation_type").val();
		//alert("Roratation :" + whorotates);
		$("#_selected_rotation").html("<strong>"+whorotates+"</strong>");
		
		if(whorotates == "Learner Rotates")
		{
			StudentRotation();
		}
		else{
			TeacherRotation();
		}
		
	}
	
	function TeacherRotation()
	{
		var school_id = getUrlParameter("school_id");
		if(school_id == undefined || school_id == "undefined")
		{
			school_id = $("#schools").val();
		}	
		var daurl = "process.php?action=GETCLASSES&school_id="+school_id;
		//alert(daurl);
		$('#tblClassVenueSetting').datagrid({
			title:'Class Settings (Teacher Rotation)',
			iconCls:'icon-edit',
			width:860,
			height:250,
			singleSelect:false,
			idField:'userid',
			url:daurl,
			onCheck:function(index,row){onClassVenueClickCell(index,row);},
			columns:[[
				{field:'class_id',title:'Class ID',width:60,hidden:true},
				{field:'ck',checkbox:true},
				{field:'class_label',width:150, title:'Class'},
				
				{field:'room_id',title:'Venue',width:100,
					formatter:function(value){
						for(var i=0; i<venues.length; i++){
							if (venues[i].room_id == value) return venues[i].room_label;
						}							
						return value;
					},
					editor:{
						type:'combobox',
						options:{
							valueField:'room_id',
							textField:'room_label',
							data:venues,
							required:true
						}
					}
				}
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
	
	function StudentRotation()
	{
		var school_id = getUrlParameter("school_id");
		if(school_id == undefined || school_id == "undefined")
		{
			school_id = $("#schools").val();
		}	
		var daurl = "process.php?action=GETPERSONNEL&type_id=4&school_id="+school_id;
		//alert(daurl);
		$('#tblClassVenueSetting').datagrid({
			title:'Class Settings (Learner Rotation)',
			iconCls:'icon-edit',
			width:860,
			height:250,
			singleSelect:false,
			idField:'userid',
			url:daurl,
			onCheck:function(index,row){onClassVenueClickCell(index,row);},
			columns:[[
				{field:'userid',title:'User ID',width:60,hidden:true},
				{field:'ck',checkbox:true},
				{field:'access_id',width:150, title:'Teacher ID'},
				{field:'name',width:150,title:'Name'},
				{field:'surname',width:150,align:'right',title:'Surname'},
				{field:'room_id',title:'Venue',width:100,
					formatter:function(value){
						for(var i=0; i<venues.length; i++){
							if (venues[i].room_id == value) return venues[i].room_label;
						}							
						return value;
					},
					editor:{
						type:'combobox',
						options:{
							valueField:'room_id',
							textField:'room_label',
							data:venues,
							required:true
						}
					}
				}
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
	
	
	/*$(function(){
		
	
	});*/