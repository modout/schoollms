<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// include config with database definition
include('config_mysql.php');

$school_id = $_REQUEST['school_id'];
$subject_id = $_REQUEST['subject_id'];
$subject_color = $_REQUEST['subject_color'];
$grade_subject_color = $_REQUEST['grade_subject_color'];
$period_type = $_REQUEST['period_type'];
$period_times = $_REQUEST['period_times'];
$notional_time = $_REQUEST['notional_time'];
$grade_id = $_REQUEST['grade_id'];
$periods_cycle = $_REQUEST['periods_cycle'];
$minimum_learners = $_REQUEST['minimum_learners'];
$subject_type = $_REQUEST['subject_type'];

//Must get existing grade_settings to be able to compare and modify where required.


$grade_settings = "grade_setting#grade_id=$grade_id:grade_subject_color=$grade_subject_color,notional_time=$notional_time,period_cycle=$periods_cycle,subject_type=$subject_type,minimum_learners=$minimum_learners";

$settings_string = "subject_id=$subject_id<subject_color=$subject_color|period_type=$period_type|period_times=$period_times|$grade_settings";

//echo "BEFORE SETTINGS $settings_string <br />";

$settings_string = timetable_settings_update($school_id, $settings_string, 'subject', $subject_id);

//echo "AFTER SETTINGS $settings_string <br />";

timetable_settings_save($school_id, $settings_string, 'subject');

header("Location: timetable_settings.php?school_id=$school_id");
//http_redirect("timetable_settings.php", array("school_id" => $school_id), true, HTTP_REDIRECT_PERM);