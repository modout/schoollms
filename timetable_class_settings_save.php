<?php

// include config with database definition
include('config_mysql.php');

$school_id = $_REQUEST['school_id'];
$room_id = $_REQUEST['room_id'];
$user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
$class_id = isset($_REQUEST['class_id']) ? $_REQUEST['class_id'] : 0;
$year_id = $_REQUEST['year_id'];
$year_theme = "none";
//$learner_average = $_REQUEST['learner_average'];
//$subject_choices = $_REQUEST['subject_choices'];
//$grade_entry_average= $_REQUEST['grade_entry_average'];


//Must get existing grade_settings to be able to compare and modify where required.
//$current_settings = timetable_settings_get($school_id, "learner");

if ($user_id !== 0){
    $teacher_settings = "teacher_settings#user_id=$user_id:room_id=$room_id";
    $settings_string = "year_id=$year_id<year_theme=$year_theme|$teacher_settings";
} else {
    $class_settings = "class_settings#class_id=$class_id:room_id=$room_id";
    $settings_string = "year_id=$year_id<year_theme=$year_theme|$class_settings";
}

//echo "BEFORE SETTINGS $settings_string <br />";

$settings_string = timetable_settings_update($school_id, $settings_string, 'class', $grade_id);

//echo "AFTER SETTINGS $settings_string <br />";

timetable_settings_save($school_id, $settings_string, 'class');

//header("Location: timetable_settings.php?school_id=$school_id");
//http_redirect("timetable_settings.php", array("school_id" => $school_id), true, HTTP_REDIRECT_PERM);