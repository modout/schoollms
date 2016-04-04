<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// include config with database definition
include('config_mysql.php');

$school_id = $_REQUEST['school_id'];
$days = $_REQUEST['days'];
$periods = $_REQUEST['periods'];
$period_time = $_REQUEST['period_time'];
$break_time = $_REQUEST['break_time'];
$from_grade = $_REQUEST['from_grade'];
$to_grade = $_REQUEST['to_grade'];
$classletters = $_REQUEST['classletters'];
$rotation_type = $_REQUEST['rotation_type'];
$number_of_breaks = isset($_REQUEST['number_of_breaks']) ? $_REQUEST['number_of_breaks']: 1; 
$school_start_time = isset($_REQUEST['school_start_time']) ? $_REQUEST['school_start_time']: '07:30'; 
$class_start_time = isset($_REQUEST['class_start_time']) ? $_REQUEST['class_start_time']: '08:00';

$break_times = "";
$count_breaks = 1;

while ($count_breaks <= $number_of_breaks){
    $time = isset($_REQUEST["break_time_$count_breaks"])? $_REQUEST["break_time_$count_breaks"]: "11:00"; 
    $length = isset($_REQUEST["break_length_$count_breaks"])? $_REQUEST["break_length_$count_breaks"]: 30;
    $break_times .= "break_$count_breaks!$time!$length*";
	//$break_times .= strpos($break_times, '*') !== FALSE ? "*break_$count_breaks!$time!$length" : "break_$count_breaks!$time!$length";
    $count_breaks++;
}
$break_times = substr($break_times,0,strlen($break_times)-1);

$settings_string = "days=$days|periods=$periods|period_time=$period_time|number_of_breaks=$number_of_breaks|break_times=$break_times|from_grade=$from_grade|to_grade=$to_grade|classletters=$classletters|rotation_type=$rotation_type|school_start_time=$school_start_time|class_start_time=$class_start_time";

echo "School ID $school_id SETTINGS $settings_string";

timetable_settings_save($school_id, $settings_string, 'general');

if(strtoupper(str_replace(" ","",$classletters)) == "A-Z")
{
	$classletters = "A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
}

$classes = explode(",",$classletters);
if($from_grade == "R")$from_grade= "0";
if($to_grade == "R")$to_grade= "0";
$year_id = 0;
$sql = "select max(year_id) year_id from schoollms_schema_userdata_school_year";
$data->execSQL($sql);

if($row = $data->getRow())
{
	
	$year_id = $row->year_id;
	echo "<br/>$sql   $row->year_id;";
}


for($i =$from_grade;$i <= $to_grade;$i++)
{
	for($z =0;$z <count($classes);$z++)
	{
		$sql = "select * from schoollms_schema_userdata_school_classes where school_id = '$school_id'  
		and grade_id = $i and class_label like '%CLASS%$i".$classes[$z]."%' and year_id = $year_id";
		$data->execSQL($sql);
		if($data->numrows < 1)
		{
			$sql = "insert into schoollms_schema_userdata_school_classes(school_id,grade_id,class_label, year_id)
			values( '$school_id','$i','CLASS $i".$classes[$z]."','$year_id')";
			echo "<br/>$sql</br>";
			$data->execNonSql($sql);
		}
	}
}



//header("Location: timetable_settings.php?school_id=$school_id");
//http_redirect("timetable_settings.php", array("school_id" => $school_id), true, HTTP_REDIRECT_PERM);