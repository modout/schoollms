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

$settings_string = "days=$days|periods=$periods|period_time=$period_time|break_time=$break_time|from_grade=$from_grade|to_grade=$to_grade|classletters=$classletters|rotation_type=$rotation_type";

//echo "School ID $school_id SETTINGS $settings_string";

timetable_settings_save($school_id, $settings_string, 'general');

header("Location: timetable_settings.php?school_id=$school_id");
//http_redirect("timetable_settings.php", array("school_id" => $school_id), true, HTTP_REDIRECT_PERM);