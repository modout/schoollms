<?php

include('data_db_mysqli.inc');

extract($_GET);
extract($_POST);
if(isset($db))
{
	$data->db = $db;
}

if(strtoupper($action) == "SQLEXEC")
{
	$sql;
	$data->execNonSql($sql);
}
if(strtoupper($action) == "GETTABLESTRUCTURE")
{
	$sql = "desc $table";
	echo $sql;
	$data->execSQL($sql);
	$result = array();
	while($row1 = $data->getRow())
	{
		$result[] = $row1;
	}
	
	echo json_encode($result);
}

if(strtoupper($action) == "GETTABLEDATA")
{
	$primarykey = "";
	
	$sql = "show index from $table
		where key_name = 'PRIMARY'";
	$data->execSQL($sql);
	$result = array();
	if($row = $data->getRow())
	{
		$primarykey  = $row->Column_name;
	}
	
	
	$sql = "SELECT * FROM  $table WHERE $primarykey >= $id";
	echo $sql;
	
	$data->execSQL($sql);
	$result = array();
	while($row1 = $data->getRow())
	{
		$result[] = $row1;
	}
	
	echo json_encode($result);
}


?>