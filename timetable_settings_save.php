<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// include config with database definition
include('config_mysql.php');
extract($_POST);
extract($_GET);

$school_id = $_REQUEST['school_id'];
$days = $_REQUEST['days'];
$periods = $_REQUEST['periods'];
$period_time = $_REQUEST['monday_period_time'];
$break_time = $_REQUEST['break_time_1'];
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

$settings_string = "days=$days|periods=$periods|period_time=$period_time|number_of_breaks=$number_of_breaks|break_times=$break_times|from_grade=$from_grade|to_grade=$to_grade|classletters=$classletters|rotation_type=$rotation_type|school_start_time=$school_start_time|class_start_time=$class_start_time|has_registration=$has_registration|registration_length=$registration_length";

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

echo "<hr/> $from_grade <= $to_grade";



for($i =$from_grade;$i <= $to_grade;$i++)
{
	for($z =0;$z <count($classes);$z++)
	{
		$sql = "select * from schoollms_schema_userdata_school_classes where school_id = '$school_id'  
		and grade_id = $i and class_label like '%CLASS%$i".$classes[$z]."%' and year_id = $year_id";
		
		$data->execSQL($sql);
		
		echo "<hr/>$sql <br/>$data->numrows";
		
		
		if($data->numrows < 1)
		{
			echo "<hr>Adding $data->numrows -- '%CLASS%$i".$classes[$z]."%'";
			
			$g = $i;
			if($i == 0)
			{
				$g = "R";
			}
			$sql = "insert into schoollms_schema_userdata_school_classes(school_id,grade_id,class_label, year_id)
			values( '$school_id','$i','CLASS $g".$classes[$z]."','$year_id')";
			echo "<br/>$sql</br>";
			$data->execNonSql($sql);
			$timetable_type_item_id = $data->insertid;
			
			$sql = "select * from schoollms_schema_userdata_school_timetable 
			where school_id = $school_id and timetable_label like '%CLASS%$g".$classes[$z]."%' 
			and timetable_type_id in 
				(  select type_id from schoollms_schema_userdata_school_timetable_type
					where type_title = 'class')";

			$data->execSQL($sql);
			if($data->numrows < 1)
			{			
				echo "<hr>Adding $data->numrows -- '%CLASS%$i".$classes[$z]."%'";
				
				$ins = "insert into schoollms_schema_userdata_school_timetable (school_id, timetable_type_id, timetable_type_item_id,timetable_label)
				select '$school_id',type_id,'$timetable_type_item_id','CLASS $g".$classes[$z]."'
				from schoollms_schema_userdata_school_timetable_type
				where type_title = 'class'
				limit 0,1";
				$data->execNonSql($ins);
				//die();
			}
		}
	}
}



$firstperiodstart = $school_start_time;
$day = date("Y-m-d ");
if(strtoupper($has_registration) == "YES")
{
	$sql = "select * from schoollms_schema_userdata_school_timetable_period_labels where school_id = $school_id and period_label = 'Registration'";
	echo "<br/> $sql <br/>";
	$data->execSQL($sql);
	if($data->numrows < 1)
	{
		$sql = "insert into schoollms_schema_userdata_school_timetable_period_labels (school_id,period_label)
		values ($school_id,'Registration')";
		echo "<br/> $sql <br/>";
		$data->execNonSql($sql);
		$period_label_id = $data->insertid;
		$day = date("Y-m-d ");
		$starttime = "$day $school_start_time";
		
		$starttime = new DateTime($starttime);
		$starttime->add(new DateInterval('PT' . $registration_length . 'M'));

		$endtime = $starttime->format('H:i');
		$firstperiodstart = $endtime;
		
		$sql = "insert into schoollms_schema_userdata_school_timetable_period (week_day_id,school_id,period_start, period_end,period_label_id )
		select week_day_id,'$school_id','$school_start_time','$endtime','$period_label_id'
		from schoollms_schema_userdata_school_timetable_weekdays";
		$data->execNonSql($sql);
	}
	else{
		$row = $data->getRow();
		$period_label_id = $row->period_label_id;
		$sql = "select * from schoollms_schema_userdata_school_timetable_weekdays";
		$data->execSQL($sql);
		while($row1=$data->getRow())
		{
			$sql = "select * from schoollms_schema_userdata_school_timetable_period 
				where week_day_id = $row1->week_day_id
				and period_label_id = $period_label_id
				and school_id= $school_id";
			$data1->execSQL($sql);
			
			$starttime = "$day $school_start_time";
				
			$starttime = new DateTime($starttime);
			$starttime->add(new DateInterval('PT' . $break_length_1 . 'M'));

			$endtime = $starttime->format('H:i');
			$firstperiodstart = $endtime;
			
			if($data1->numrows < 1)
			{	
				$sql = "insert into schoollms_schema_userdata_school_timetable_period (week_day_id,school_id,period_start, period_end,period_label_id )
				values($row1->week_day_id,'$school_id','$school_start_time','$endtime','$period_label_id')";
				$data1->execNonSql($sql);
				echo "<br/> $sql <br/>";
			}
		}
	}
}

//add break
$breakstart = "";
$breakendtime = "";

$sql = "select * from schoollms_schema_userdata_school_timetable_period_labels where school_id = $school_id and period_label = 'Break'";
$data->execSQL($sql);
if($data->numrows < 1)
{
	$sql = "insert into schoollms_schema_userdata_school_timetable_period_labels (school_id,period_label)
	values ($school_id,'Break')";
	$data->execNonSql($sql);
	$period_label_id = $data->insertid;
	
	$starttime = "$day $break_time_1";
	$breakstart = "$day $break_time_1";
	
	$starttime = new DateTime($starttime);
	$starttime->add(new DateInterval('PT' . $break_length_1 . 'M'));

	$endtime = $starttime->format('H:i');
	$breakendtime = $endtime;
	
	$sql = "insert into schoollms_schema_userdata_school_timetable_period (week_day_id,school_id,period_start, period_end,period_label_id )
	select weekday_id,'$school_id','$break_time_1','$endtime','$period_label_id'
	from schoollms_schema_userdata_school_timetable_weekdays";
	$data->execNonSql($sql);
}
else{
	echo "<br/>We are here <br/>";
	$row = $data->getRow();
	$period_label_id = $row->period_label_id;
	$sql = "select * from schoollms_schema_userdata_school_timetable_weekdays";
	$data->execSQL($sql);
	while($row1=$data->getRow())
	{
		$sql = "select * from schoollms_schema_userdata_school_timetable_period 
			where week_day_id = $row1->week_day_id
			and period_label_id = $period_label_id
			and school_id= $school_id";
		$data1->execSQL($sql);
		if($data1->numrows < 1)
		{
			$starttime = "$day $break_time_1";
			$breakstart = "$day $break_time_1";
			
			$starttime = new DateTime($starttime);
			$starttime->add(new DateInterval('PT' . $break_length_1 . 'M'));

			$endtime = $starttime->format('H:i');
			$breakendtime = $endtime;
			
			$sql = "insert into schoollms_schema_userdata_school_timetable_period (week_day_id,school_id,period_start, period_end,period_label_id )
			values($row1->week_day_id,'$school_id','$break_time_1','$endtime','$period_label_id')";
			$data1->execNonSql($sql);
			echo "<br/> $sql <br/>";
		}
	}	
}

$periodstarttime = $firstperiodstart;	
	
$sql = "select * from schoollms_schema_userdata_school_timetable_weekdays";
$data->execSQL($sql);
while($row=$data->getRow())
{
	$periodstarttime = $firstperiodstart;
	$period_length = 0;
	$checkedbreak = false;
	switch($row->week_day_label)
	{
		case "Monday":
			$period_length = $monday_period_time;
			break;
		case "Tuesday":
			$period_length = $tuesday_period_time;
			break;
		case "Wednesday":
			$period_length = $wednesday_period_time;
			break;
		case "Thursday":
			$period_length = $thursday_period_time;
			break;
		case "Friday":
			$period_length = $friday_period_time;
			break;
		case "Saturday":
			$period_length = $saturday_period_time;
			break;
		case "Sunday":
			$period_length = $sunday_period_time;
			break;				
	}
	echo "<br/> $row->week_day_label period length $period_length <br/>";
	
	for($i =1;$i<= $periods;$i++)
	{
		$period_label_id = 0;
		$starttime = new DateTime("$day $periodstarttime");
		$starttime->add(new DateInterval('PT' . $period_length . 'M'));
		$endtime = $starttime->format('H:i');
		
		$sql = "select * from schoollms_schema_userdata_school_timetable_period_labels where school_id = $school_id and period_label = 'P$i'";
		$data1->execSQL($sql);
		if($data1->numrows < 1)
		{
			$sql1 = "insert into schoollms_schema_userdata_school_timetable_period_labels (school_id,period_label)
			values ($school_id,'P$i')";
			$data1->execNonSql($sql1);
			$period_label_id = $data1->insertid;
		}
		else{
			$row1 = $data1->getRow();
			$period_label_id = $row1->period_label_id;
		
		
			$sql = "select * from schoollms_schema_userdata_school_timetable_period where week_day_id = $row->week_day_id
				and school_id = $school_id and period_label_id = $period_label_id";
			$data1->execSQL($sql);
			if($data1->numrows <1)
			{
				$sql = "insert into schoollms_schema_userdata_school_timetable_period (week_day_id,school_id,period_start, period_end,period_label_id )
				values('$row->week_day_id','$school_id','$periodstarttime','$endtime','$period_label_id ')";
				$data1->execNonSql($sql);
				echo "<br/> $sql <br/>";
			}
		}
		
		$periodstarttime = $endtime;
		
		if($checkedbreak == false)
		{
			$seconds  = strtotime("$day $periodstarttime") - strtotime("$day $breakstart");
			if($seconds >= 0)
			{
				$periodstarttime = $breakendtime;
				$checkedbreak  = true;
			}
		}
		
	}
}






//header("Location: timetable_settings.php?school_id=$school_id");
//http_redirect("timetable_settings.php", array("school_id" => $school_id), true, HTTP_REDIRECT_PERM);