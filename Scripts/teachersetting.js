var products = [
		    {productid:'FI-SW-01',name:'Koi'},
		    {productid:'K9-DL-01',name:'Dalmation'},
		    {productid:'RP-SN-01',name:'Rattlesnake'},
		    {productid:'RP-LI-02',name:'Iguana'},
		    {productid:'FL-DSH-01',name:'Manx'},
		    {productid:'FL-DLH-02',name:'Persian'},
		    {productid:'AV-CB-01',name:'Amazon Parrot'}
		];
var selectedTeachers = [];		
		
var substitution = [
			{subid:'1',value:'Yes'},
			{subid:'2',value:'No'}
		];		
		
		$(function(){
			getTeacherSettings();
			
		});
		
		function getTeacherSettings()
		{
			var school_id = getUrlParameter("school_id");
			if(school_id == undefined || school_id == "undefined")
			{
				school_id = $("#schools").val();
			}	
			var daurl = "process.php?action=GETPERSONNEL&type_id=4&school_id="+school_id;
			
			$('#tblTeacheSetting').datagrid({
				title:'Teacher Settings',
				iconCls:'icon-edit',
			    toolbar:'#teacher_toolbar',
				width:860,
				height:250,
				singleSelect:false,
				idField:'userid',
				url:daurl,
				onCheck:function(index,row){onTeacherClickCell(index,row);},
				onDblClickRow:function(index,row){
					//open your dialog
					//alert(row.user_id);
					$("#user_id").val(row.user_id);
					$("#teacher_id").val(row.access_id);
					$("#teacher_initials").val(row.name);
					$("#teacher_surname").val(row.surname);
					$('#newTeacherDlg').dialog({title: 'Update Teacher'});
					$('#newTeacherDlg').dialog('open');	
					
				},
				columns:[[
					{field:'userid',title:'User ID',width:60,hidden:true},
					{field:'ck',checkbox:true},
					{field:'access_id',width:150, title:'Teacher ID'},
					{field:'name',width:150,title:'Name'},
					{field:'surname',width:150,align:'right',title:'Surname'},
					{field:'substitute',title:'Substitution',width:100,
						formatter:function(value){
							for(var i=0; i<substitution.length; i++){
								if (substitution[i].subid == value) return substitution[i].value;
							}							
							return value;
						},
						editor:{
							type:'combobox',
							options:{
								valueField:'subid',
								textField:'value',
								data:substitution,
								required:true
							}
						}
					},
					{field:'number_periods',title:'Number of Periods',width:120,align:'right',editor:'numberbox'}
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
		
		function updateActions(index){
			$('#tblTeacheSetting').datagrid('refreshRow', index);
			//var rows = $('#tblTeacheSetting').datagrid('getRows');    // get current page rows
			//var row = rows[index];
			//alert(row.productid);
		}
		function getRowIndex(target){
			var tr = $(target).closest('tr.datagrid-row');
			return parseInt(tr.attr('datagrid-row-index'));
		}
		function editrow(target){
			$('#tblTeacheSetting').datagrid('beginEdit', getRowIndex(target));
		}
		function rowSelected(target)
		{
			var index= getRowIndex(target);
			var rows = $('#tblTeacheSetting').datagrid('getRows');
			var row = rows[index];
			if (row.editing){
				$('#tblTeacheSetting').datagrid('endEdit', getRowIndex(target));
			}
			else{
				$('#tblTeacheSetting').datagrid('beginEdit', getRowIndex(target));
			}
		}
		function deleterow(target){
			$.messager.confirm('Confirm','Are you sure?',function(r){
				if (r){
					$('#tblTeacheSetting').datagrid('deleteRow', getRowIndex(target));
				}
			});
		}
		function saverow(target){
			$('#tblTeacheSetting').datagrid('endEdit', getRowIndex(target));
		}
		function cancelrow(target){
			$('#tblTeacheSetting').datagrid('cancelEdit', getRowIndex(target));
		}
		function insert(){
			var row = $('#tblTeacheSetting').datagrid('getSelected');
			if (row){
				var index = $('#tblTeacheSetting').datagrid('getRowIndex', row);
			} else {
				index = 0;
			}
			$('#tblTeacheSetting').datagrid('insertRow', {
				index: index,
				row:{
					status:'P'
				}
			});
			$('#tblTeacheSetting').datagrid('selectRow',index);
			$('#tblTeacheSetting').datagrid('beginEdit',index);
		}