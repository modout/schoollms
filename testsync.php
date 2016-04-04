<?php
include('data_db_mysqli.inc');
include('util.php');

$sql = "show tables";
$results = $data->exec_sql($sql,"array");
$databasebame =  "Tables_in_".$data->db;
for($i = 0;$i < count($results);$i++)	
{
	echo "\n\r";
	
	//check if tables exists
	$url = "http://localhost:8080/admin/serversync.php";
	$table = $results[$i][0];
	$pars= "action=GETTABLESTRUCTURE&table=$table";
	echo "$url?$pars";
	$tablestructure = do_post_request($url,$pars);
	//$res = json_decode($tablestructure);
	var_dump($tablestructure);	
	
	
	
	
	/*
	$sql1 = "desc ".$results[$i][0];	
	$data1->execSQL($sql1);
	while($row1 = $data1->getRow())
	{
		$sqlToSend = "CREATE TABLE IF NOT EXISTS ";
	}
	$alters = array();
	$insert = "CREATE TABLE IF NOT EXISTS  ";
	while($row1 = $data1->getRow())
	{
		echo "Description of ".$results[$i][0]."<br/>";
		$alter = "alter table ".$results[$i][0]." add $row1->Field $row1->Type ";;
		
		echo "$row1->Field , $row1->Type, $row1->Null, $row1->Key, $row1->Default, $row1->Extra <br/>";		
	}*/
}

?>