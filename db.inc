<?php

if(function_exists("mysql_connect") )
{
	include("data_db.inc");
}
else{
	include("data_db_mysqli.inc");
}

/*$data = new data();
$data->username = "root";
$data->password = "12_s5ydw3ll1979";
$data->host = "localhost";
$data->db = "mehluko";
*/

$data = new data();
$data->username = "root";
//$data->password = "12_s5ydw3ll1979";
$data->password = "$0W3t0";
$data->host = "localhost";
$data->db = "school_lms_dev_support";

$data1 = new data();
$data1->username = "root";
$data1->password = "$0W3t0";
$data1->host = "localhost";
$data1->db = "school_lms_dev_support";


$imagedb = new data();
$imagedb->username = "root";
$imagedb->password = "$0W3t0";
$imagedb->host = "localhost";
$imagedb->db = "school_lms_dev_support";
?>