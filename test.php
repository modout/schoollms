
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="keywords" content="jquery,ui,easy,easyui,web">
	<meta name="description" content="easyui help you build your web page easily!">
	<title>DataGrid inline editing - jQuery EasyUI Demo</title>
	<link rel="stylesheet" type="text/css" href="http://www.jeasyui.com/easyui/themes/default/easyui.css">
	<link rel="stylesheet" type="text/css" href="http://www.jeasyui.com/easyui/themes/icon.css">
	<link rel="stylesheet" type="text/css" href="http://www.jeasyui.com/easyui/demo/demo.css">
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.1.min.js"></script>
	<script type="text/javascript" src="http://www.jeasyui.com/easyui/jquery.easyui.min.js"></script>
</head>
<body>
	<h2>Editable DataGrid Demo</h2>
	<div class="demo-info">
		<div class="demo-tip icon-tip">&nbsp;</div>
		<div>Click the edit button on the right side of row to start editing.</div>
	</div>
	
	<div style="margin:10px 0">
		<a href="javascript:void(0)" class="easyui-linkbutton" onclick="insert()">Insert Row</a>
	</div>
	
	<table id="tblTeacheSetting"></table>
	
	<script>
		var products = [
		    {productid:'FI-SW-01',name:'Koi'},
		    {productid:'K9-DL-01',name:'Dalmation'},
		    {productid:'RP-SN-01',name:'Rattlesnake'},
		    {productid:'RP-LI-02',name:'Iguana'},
		    {productid:'FL-DSH-01',name:'Manx'},
		    {productid:'FL-DLH-02',name:'Persian'},
		    {productid:'AV-CB-01',name:'Amazon Parrot'}
		];
		$(function(){
			$('#tblTeacheSetting').datagrid({
				title:'Editable DataGrid',
				iconCls:'icon-edit',
				width:860,
				height:250,
				singleSelect:true,
				idField:'userid',
				url:'process.php?action=GETPERSONNEL&type_id=4',
				columns:[[
					{field:'userid',title:'User ID',width:60,hidden:true},
					{field:'access_id',width:150, title:'Teacher ID'},
					{field:'name',width:200,title:'Name'},
					{field:'surname',width:200,align:'right',title:'Surname',editor:'text'},
					{field:'productid',title:'Product',width:100,
						formatter:function(value){
							alert(products.length);
							for(var i=0; i<products.length; i++){
								if (products[i].productid == value) return products[i].name;
							}
							return value;
						},
						editor:{
							type:'combobox',
							options:{
								valueField:'productid',
								textField:'name',
								data:products,
								required:true
							}
						}
					},
					{field:'number_periods',title:'Number of Periods',width:80,align:'right',editor:'numberbox'},
					{field:'action',title:'Action',width:80,align:'center',
						formatter:function(value,row,index){
							if (row.editing){
								var s = '<a href="javascript:void(0)" onclick="saverow(this)">Save</a> ';
								var c = '<a href="javascript:void(0)" onclick="cancelrow(this)">Cancel</a>';
								return s+c;
							} else {
								var e = '<a href="javascript:void(0)" onclick="editrow(this)">Edit</a> ';
								var d = '<a href="javascript:void(0)" onclick="deleterow(this)">Delete</a>';
								return e+d;
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
		});
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
	</script>
	
</body>
</html>