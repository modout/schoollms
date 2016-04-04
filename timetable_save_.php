<?php

// include config with database definition
include('config_mysql.php');

//DRUPAL CONNECTION
//$path = $_SERVER['DOCUMENT_ROOT'];
//chdir($path."/drupal");
//define('DRUPAL_ROOT', getcwd()); //the most important line
//require_once './includes/bootstrap.inc';
//drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

//WORKING FROM DRUPAL DIRECTORY
//define('DRUPAL_ROOT', getcwd());
//require_once './includes/bootstrap.inc';
//drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
//// Go to drupal path.. 
//   $drupal_path = "/var/www/html/";  // <---- change this one to fit yours
//   $cdir = getcwd();
//   chdir($drupal_path);
//
//// include needed files
//   include('includes/bootstrap.inc');
//   include('includes/database.inc');
//   include('includes/database.mysql.inc');
//
//// Launch drupal start: configuration and database bootstrap
//   conf_init();
//   drupal_bootstrap(DRUPAL_BOOTSTRAP_CONFIGURATION);
//   drupal_bootstrap(DRUPAL_BOOTSTRAP_DATABASE);
//
//// Once bootstrapped.. go bak to this directory..
//   chdir($cdir);
//
//// Now you can use drupal database with drupal's dbal:
//// Unlock user admin if blocked
//   db_query("UPDATE {users} set status = 1 where uid = 1");

$save_type = isset($_REQUEST['save_type']) ? $_REQUEST['save_type'] : 0;

switch ($save_type) {
    case 'learn_timetable_slot':
        
        break;
    
    case 'teach_timetable_slot':
        $school_id = isset($_REQUEST['school_id']) ? $_REQUEST['school_id'] : 0;
        $year_id = isset($_REQUEST['year_id']) ? $_REQUEST['year_id'] : 0;
        $grade_id = isset($_REQUEST['grade_id']) ? $_REQUEST['grade_id'] : 0;
        $class_id = isset($_REQUEST['class_id']) ? $_REQUEST['class_id'] : 0;
        $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
        $subject_id = isset($_REQUEST['subject_id']) ? $_REQUEST['subject_id'] : 0;
        $room_id = isset($_REQUEST['room_id']) ? $_REQUEST['room_id'] : 0;
        $change_type = isset($_REQUEST['change_type']) ? $_REQUEST['change_type'] : 0;
        $change_value = isset($_REQUEST['change_value']) ? $_REQUEST['change_value'] : 0;
        $date = isset($_REQUEST['date']) ? $_REQUEST['date'] : 0;
        $lesson_id = isset($_REQUEST['lesson_id']) ? $_REQUEST['lesson_id'] : 0;
        break;
    
    case 'admin_timetable_slot':
        $school_id = isset($_REQUEST['school_id']) ? $_REQUEST['school_id'] : 0;
        $year_id = isset($_REQUEST['year_id']) ? $_REQUEST['year_id'] : 0;
        $grade_id = isset($_REQUEST['grade_id']) ? $_REQUEST['grade_id'] : 0;
        $class_id = isset($_REQUEST['class_id']) ? $_REQUEST['class_id'] : 0;
        $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
        $day = isset($_REQUEST['day']) ? $_REQUEST['day'] : 0;
        $period = isset($_REQUEST['period']) ? $_REQUEST['period'] : 0;
        $subject_id = isset($_REQUEST['subject_id']) ? $_REQUEST['subject_id'] : 0;
        $room_id = isset($_REQUEST['room_id']) ? $_REQUEST['room_id'] : 0;
        $change_type = isset($_REQUEST['change_type']) ? $_REQUEST['change_type'] : 0;
        $change_value = isset($_REQUEST['change_value']) ? $_REQUEST['change_value'] : 0;
        $date = isset($_REQUEST['date']) ? $_REQUEST['date'] : 0;
        $lesson_id = isset($_REQUEST['lesson_id']) ? $_REQUEST['lesson_id'] : 0;
        
        if ($day == 0 && $period == 0){
            //ERROR
        } else {
            //Get Day ID - USING school_id and year_id and day_label
            $q = "SELECT day_id FROM schoollms_schema_userdata_school_timetable_days WHERE day_label = '$day'";
            $result = sqlQuery($q);
            $day_id = 0;
   
            foreach ($result as $setting_item) {
                //print_r($setting_item);
                $day_id = $setting_item[0][0];
                break;
            }
            
            //Get Period ID - USING school_id, year_id and day_id and period_times
            $period_times = explode("-", $period);
            $period_start = $period_times[0];
            $period_end = $period_times[1];
            
            $q = "SELECT period_id FROM schoollms_schema_userdata_school_timetable_period WHERE period_start = '$period_start' AND period_end = '$period_end'";
            $result = sqlQuery($q);
            $period_id = 0;
   
            foreach ($result as $setting_item) {
                //print_r($setting_item);
                $period_id = $setting_item[0][0];
                break;
            }
            
            $save_string = "school_id=$school_id,year_id=$year_id,grade_id=$grade_id,class_id=$class_id,user_id=$user_id,day_id=$day_id,period_id=$period_id,subject_id=$subject_id,room_id=$room_id,change_type=$change_type,change_value=$change_value";
            timetable_save($save_type, $save_string);
            //USING TIMETABLE ID 
            //$q = "select * from schoollms_schema_userdata_school_timetable_items where timetable_id = $timetable_id and grade_id =  $grade_id and class_id = $class_id and day_id = $day_id and period_id = $period_id";
        }
        break;
    
    case 'learner_timetable_settings':
        $school_id = $_REQUEST['school_id'];
        $grade_id = $_REQUEST['grade_id'];
        $user_id = $_REQUEST['user_id'];
        $year_id = $_REQUEST['year_id'];
        $baseline = $_REQUEST['baseline'];
        $learner_average = $_REQUEST['learner_average'];
        $subject_choice = $_REQUEST['subject_choice'];
        $grade_entry_average= isset($_REQUEST['grade_entry_average']) ? $_REQUEST['grade_entry_average'] : 0;
        $number_of_learners= isset($_REQUEST['number_of_learners']) ? $_REQUEST['number_of_learners'] : 0;
        $current_grade= isset($_REQUEST['current_grade']) ? $_REQUEST['current_grade'] : 0;
        $current_class= isset($_REQUEST['current_class']) ? $_REQUEST['current_class'] : 0;
        $next_grade= isset($_REQUEST['next_grade']) ? $_REQUEST['next_grade'] : 0;
        $next_class= isset($_REQUEST['next_class']) ? $_REQUEST['next_class'] : 0;
        //Must get existing grade_settings to be able to compare and modify where required.
        //$current_settings = timetable_settings_get($school_id, "learner");

        $learner_settings = "learner_settings#user_id=$user_id:baseline=$baseline,learner_average=$learner_average,subject_choice=$subject_choice,current_grade=$current_grade,current_class=$current_class,next_grade=$next_grade,next_class=$next_class";

        $settings_string = "grade_id=$grade_id<number_of_learners=$number_of_learners|year_id=$year_id|$learner_settings";

        //echo "BEFORE SETTINGS $settings_string <br />";

        $settings_string = timetable_settings_update($school_id, $settings_string, 'learner', $grade_id);

        //echo "AFTER SETTINGS $settings_string <br />";

        timetable_settings_save($school_id, $settings_string, 'learner');
        break;
    
    case 'update_learner':
        $year_id = isset($_REQUEST['year_id']) ? $_REQUEST['year_id'] : 0;
        $school_id = isset($_REQUEST['school_id']) ? $_REQUEST['school_id'] : 0;
        $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
        $name = isset($_REQUEST['name']) ? $_REQUEST['name'] : "";
        $surname = isset($_REQUEST['surname']) ? $_REQUEST['surname'] : "";
        $baseline = isset($_REQUEST['baseline']) ? $_REQUEST['baseline'] : 0;
        $learner_average = $_REQUEST['learner_average'];
        $subject_choice = $_REQUEST['subject_choice'];
        $grade_entry_average= isset($_REQUEST['grade_entry_average']) ? $_REQUEST['grade_entry_average'] : 0;
        $number_of_learners= isset($_REQUEST['number_of_learners']) ? $_REQUEST['number_of_learners'] : 0;
        $current_grade= isset($_REQUEST['current_grade']) ? $_REQUEST['current_grade'] : 0;
        $current_class= isset($_REQUEST['current_class']) ? $_REQUEST['current_class'] : 0;
        $next_grade= isset($_REQUEST['next_grade']) ? $_REQUEST['next_grade'] : 0;
        $next_class= isset($_REQUEST['next_class']) ? $_REQUEST['next_class'] : 0;

        $parent_id_1 = isset($_REQUEST['parent_id_1']) ? $_REQUEST['parent_id_1'] : 0;
        $parent_name_1 = isset($_REQUEST['parent_name_1']) ? $_REQUEST['parent_name_1'] : "";
        $parent_surname_1 = isset($_REQUEST['parent_surname_1']) ? $_REQUEST['parent_surname_1'] : "";
        $parent_id_2 = isset($_REQUEST['parent_id_2']) ? $_REQUEST['parent_id_2'] : 0;
        $parent_name_2 = isset($_REQUEST['parent_name_2']) ? $_REQUEST['parent_name_2'] : "";
        $parent_surname_2 = isset($_REQUEST['parent_surname_2']) ? $_REQUEST['parent_surname_2'] : "";
        
        $q = "UPDATE schoollms_schema_userdata_access_profile SET school_id = $school_id, access_id = '$learner_id', name = '$name', surname= '$surname' WHERE user_id = $user_id";
        break;
    
    case 'update_teacher':
        
        break;
    
    
    case 'new_learner':
        $year_id = isset($_REQUEST['year_id']) ? $_REQUEST['year_id'] : 0;
        $school_id = isset($_REQUEST['school_id']) ? $_REQUEST['school_id'] : 0;
        $learner_id = isset($_REQUEST['learner_id']) ? $_REQUEST['learner_id'] : 0;
        $name = isset($_REQUEST['name']) ? $_REQUEST['name'] : "";
        $surname = isset($_REQUEST['surname']) ? $_REQUEST['surname'] : "";
        $baseline = isset($_REQUEST['baseline']) ? $_REQUEST['baseline'] : 0;
        $learner_average = $_REQUEST['learner_average'];
        $subject_choice = $_REQUEST['subject_choice'];
        $grade_entry_average= isset($_REQUEST['grade_entry_average']) ? $_REQUEST['grade_entry_average'] : 0;
        $number_of_learners= isset($_REQUEST['number_of_learners']) ? $_REQUEST['number_of_learners'] : 0;
        $current_grade= isset($_REQUEST['current_grade']) ? $_REQUEST['current_grade'] : 0;
        $current_class= isset($_REQUEST['current_class']) ? $_REQUEST['current_class'] : 0;
        $next_grade= isset($_REQUEST['next_grade']) ? $_REQUEST['next_grade'] : 0;
        $next_class= isset($_REQUEST['next_class']) ? $_REQUEST['next_class'] : 0;

        $parent_id_1 = isset($_REQUEST['parent_id_1']) ? $_REQUEST['parent_id_1'] : 0;
        $parent_name_1 = isset($_REQUEST['parent_name_1']) ? $_REQUEST['parent_name_1'] : "";
        $parent_surname_1 = isset($_REQUEST['parent_surname_1']) ? $_REQUEST['parent_surname_1'] : "";
        $parent_id_2 = isset($_REQUEST['parent_id_2']) ? $_REQUEST['parent_id_2'] : 0;
        $parent_name_2 = isset($_REQUEST['parent_name_2']) ? $_REQUEST['parent_name_2'] : "";
        $parent_surname_2 = isset($_REQUEST['parent_surname_2']) ? $_REQUEST['parent_surname_2'] : "";

        //CREATE USER ACCOUNT ON SCHOOLLMS PORTALS
        //-- Learner Portal
        //-- Teacher Portal
        //-- Parent Portal
        //-- Admin Portal
        //-- Support Portal
        //-- Training Portal
        //-- VAS Portal
        
        //Get All User IDS from the portals to INSERT IN THE SUPPORT DB MASTER TABLE
        //SAVE LEARNER
        if ($user_id > 0){
            $q = "SELECT * FROM schoollms_schema_userdata_access_profile WHERE user_id = $user_id";
        } else {
            $q = "SELECT * FROM schoollms_schema_userdata_access_profile WHERE access_id = $learner_id";
        }
        echo "We are here";
        $result = sqlQuery($q);
        
        if (!empty($result)){               
            //UPDATE USER DATA
            $q = "";
        } else {
            //INSERT INTO schoollms_schema_userdata_acces_profile
            $q = "INSERT INTO schoollms_schema_userdata_access_profile (school_id,access_id,type_id,name,surname) VALUES ($school_id, '$learner_id',2,'$name','$surname')";
        }
        $result = sqlQuery($q);
        
        //SAVE PARENTS
        $q = "SELECT * FROM schoollms_schema_userdata_access_profile WHERE access_id = $parent_id_1";
        $result = sqlQuery($q);
        if (!empty($result)){               
            //UPDATE USER DATA
            $q = "";
        } else {
            //INSERT INTO schoollms_schema_userdata_acces_profile
            $q = "INSERT INTO schoollms_schema_userdata_access_profile (school_id,access_id,type_id,name,surname) VALUES ($school_id, '$parent_id_1',3,'$parent_name_1','$parent_surname_1')";
                     
        }
        $result = sqlQuery($q);
        
        $q = "SELECT * FROM schoollms_schema_userdata_access_profile WHERE access_id = $parent_id_2";
        $result = sqlQuery($q);
        if (!empty($result)){               
            //UPDATE USER DATA
            $q = "";
        } else {
            //INSERT INTO schoollms_schema_userdata_acces_profile
            $q = "INSERT INTO schoollms_schema_userdata_access_profile (school_id,access_id,type_id,name,surname) VALUES ($school_id, '$parent_id_2',3,'$parent_name_2','$parent_surname_2')";
        }
        $result = sqlQuery($q);
        
        //STORE LEARNER PARENT DETAILS
        $q = "SELECT user_id FROM schoollms_schema_userdata_access_profile WHERE access_id = '$learner_id'";
        $result = sqlQuery($q);
        
        $user_id = 0;
        foreach ($result as $setting_item) {
            //print_r($setting_item);
            $user_id = $setting_item[0][0];
            break;
        }
        
        if ($user_id > 0){
            $q = "SELECT user_id FROM schoollms_schema_userdata_access_profile WHERE access_id = '$parent_id_1'";
            $result = sqlQuery($q);
         
            $parent_user_id_1 = 0;
            foreach ($result as $setting_item) {
                //print_r($setting_item);
                $parent_user_id_1 = $setting_item[0][0];
                break;
            }
            
            $q = "SELECT user_id FROM schoollms_schema_userdata_access_profile WHERE access_id = '$parent_id_2'";
            $result = sqlQuery($q);
         
            $parent_user_id_2 = 0;
            foreach ($result as $setting_item) {
                //print_r($setting_item);
                $parent_user_id_2 = $setting_item[0][0];
                break;
            }
            
            $q = "INSERT INTO schoollms_schema_userdata_learner_parent VALUES ($user_id,'parent_1:$parent_user_id_1,parent_2:$parent_id_2')";
            $result = sqlQuery($q);
            
            $learner_settings = "learner_settings#user_id=$user_id:baseline=$baseline,learner_average=$learner_average,subject_choice=$subject_choice,current_grade=$current_grade,current_class=$current_class,next_grade=$next_grade,next_class=$next_class";

            $settings_string = "grade_id=$grade_id<number_of_learners=$number_of_learners|year_id=$year_id|$learner_settings";

            //echo "BEFORE SETTINGS $settings_string <br />";

            $settings_string = timetable_settings_update($school_id, $settings_string, 'learner', $grade_id);

            //echo "AFTER SETTINGS $settings_string <br />";

            timetable_settings_save($school_id, $settings_string, 'learner');
        }
        //PREPARE DEVICE TABLE ENTRY
        //SAVE IDENTITIES IN ALL OTHER PORTALS LINKED TO THE MASTER USER_ID
        
        break;
        
    case 'new_teacher':
        $year_id = isset($_REQUEST['year_id']) ? $_REQUEST['year_id'] : 0;
        $school_id = isset($_REQUEST['school_id']) ? $_REQUEST['school_id'] : 0;
        $teacher_id = isset($_REQUEST['teacher_id']) ? $_REQUEST['teacher_id'] : 0;
        $name = isset($_REQUEST['teacher_initials']) ? $_REQUEST['teacher_initials'] : "";
        $surname = isset($_REQUEST['teacher_surname']) ? $_REQUEST['teacher_surname'] : "";
        
        $q = "SELECT * FROM schoollms_schema_userdata_access_profile WHERE access_id = $teacher_id";
        $result = sqlQuery($q);
        if (!empty($result)){               
            //UPDATE USER DATA
            
        } else {
            //INSERT INTO schoollms_schema_userdata_acces_profile
            $q = "INSERT INTO schoollms_schema_userdata_access_profile (school_id,access_id,type_id,name,surname) VALUES ($school_id, '$teacher_id',4,'$name','$surname')";
        }
        $result = sqlQuery($q);
        break;

    default:
        break;
}
