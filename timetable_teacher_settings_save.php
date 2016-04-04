<?php
// include config with database definition
include('config_mysql.php');

$school_id = $_REQUEST['school_id'];
$grade_id = $_REQUEST['grade_id'];
$subject_id = $_REQUEST['subject_id'];
$user_id = $_REQUEST['user_id'];
$year_id = $_REQUEST['year_id'];
$substitute = $_REQUEST['substitute'];
$number_periods = isset($_REQUEST['number_periods'])? $_REQUEST['number_periods']: 1;
$number_teachers= isset($_REQUEST['number_teachers'])? $_REQUEST['number_teachers']: 0;


//Must get existing grade_settings to be able to compare and modify where required.
//$current_settings = timetable_settings_get($school_id, "learner");
$subject_settings = "subject_id=$subject_id%grade_id=$grade_id!number_periods=$number_periods";

$teacher_settings = "teacher_settings#user_id=$user_id:$subject_settings,substitute=$substitute";

$settings_string = "year_id=$year_id<number_teachers=$number_teachers|$teacher_settings";

echo "BEFORE SETTINGS $settings_string <br />";

$settings_string = timetable_settings_update($school_id, $settings_string, 'teacher', $year_id);

echo "AFTER SETTINGS $settings_string <br />";

timetable_settings_save($school_id, $settings_string, 'teacher');

//header("Location: timetable_settings.php?school_id=$school_id");
//http_redirect("timetable_settings.php", array("school_id" => $school_id), true, HTTP_REDIRECT_PERM);
?>