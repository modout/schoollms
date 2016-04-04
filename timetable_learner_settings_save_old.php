<?php

// include config with database definition
include('config_mysql.php');

$school_id = $_REQUEST['school_id'];
$grade_id = $_REQUEST['grade_id'];
$user_id = $_REQUEST['user_id'];
$year_id = $_REQUEST['year_id'];
$baseline = $_REQUEST['baseline'];
$learner_average = $_REQUEST['learner_average'];
$subject_choice = $_REQUEST['subject_choice'];
$grade_entry_average= isset($_REQUEST['grade_entry_average']) ? $_REQUEST['grade_entry_average'] : 0;
$number_of_learners= isset($_REQUEST['number_of_learners']) ? $_REQUEST['number_of_learners'] : 0;

//Must get existing grade_settings to be able to compare and modify where required.
//$current_settings = timetable_settings_get($school_id, "learner");

$learner_settings = "learner_settings#user_id=$user_id:baseline=$baseline,learner_average=$learner_average,subject_choice=$subject_choice";

$settings_string = "grade_id=$grade_id<number_of_learners=$number_of_learners|year_id=$year_id|$learner_settings";

//echo "BEFORE SETTINGS $settings_string <br />";

$settings_string = timetable_settings_update($school_id, $settings_string, 'learner', $grade_id);

//echo "AFTER SETTINGS $settings_string <br />";

timetable_settings_save($school_id, $settings_string, 'learner');

//header("Location: timetable_settings.php?school_id=$school_id");
//http_redirect("timetable_settings.php", array("school_id" => $school_id), true, HTTP_REDIRECT_PERM);