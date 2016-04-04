<?php

//CONNECT TO DB
//include 'sphs_school_data_db.inc';

include("data_db_mysqli.inc");
$data = new data();
$data->username = "root";
$data->password = "$0W3t0";
$data->host = "localhost";
$data->db = "school_lms_prod_schools_sphs_teach";

if (!isset($_SERVER["HTTP_HOST"])) {
  parse_str($argv[1], $_GET);
  parse_str($argv[1], $_POST);
}

extract($_GET);
extract($_POST);

switch ($action){
    
    case 'get_subject_link':
        
        break;
    
    case 'get_subject_terms':
        
        $q = "select n.title, n.nid, n2.title, quiz_nid, alias from node n "
            . "join opigno_quiz_app_quiz_sort o on o.gid = n.nid "
            . "join node n2 on n2.nid = quiz_nid "
            . "join url_alias u on u.source like concat('%',quiz_nid) "
            . "where upper(n.title) like upper('%$subject_name%$grade_no%$term_no');";
        //echo $q;
        $data->execSQL($q);
        $subject_terms = array ();
        if($row = $data->getRow()){
            do {
				//echo "WTF";
                $subject_terms[] = $row;
            } while($row = $data->getRow());
        } else {
			
            //CREATE SUBJECT TERMS AND RETURN NEW IDS
           /* define('DRUPAL_ROOT', getcwd());
            require_once DRUPAL_ROOT.'/includes/bootstrap.inc';
            drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
            global $user;
            $account = user_authenticate($_REQUEST["user"], $_REQUEST["passwd"]);
            $user = user_load($account, TRUE);
            drupal_session_regenerate();
            
            $node = new stdClass();
            $node->type = 'course';
            node_object_prepare($node);

            $node->title = "$key - ".date('YmdHis'); //. date('c');
            $node->language = LANGUAGE_NONE;
            
            /*
             * field_data_course_pretest_ref                          |
            | field_data_course_pretest_ref_sync                     |
            | field_data_course_quota                                |
            | field_data_course_quota_sync                           |
            | field_data_course_required_course_ref                  |
            | field_data_course_required_course_ref_sync             |
            | field_data_course_required_quiz_ref                    |
            | field_data_course_required_quiz_ref_sync               |
            | field_data_opigno_class_courses                        |
            | field_data_opigno_class_courses_sync                   |
            | field_data_opigno_course_categories                    |
            | field_data_opigno_course_categories_sync               |
            | field_data_opigno_course_image                         |
            | field_data_opigno_course_image_sync                    |
            | field_data_opigno_course_tools                         |
            | field_data_opigno_course_tools_sync
             * 
             *
            $node->opigno_course_tools[$node->language][0]['field']['tool'] = '';
            $node->field_track_position[$node->language][0]['field']['lon'] = (double) $longi;
            $node->field_track_position[$node->language][0]['field']['map_height'] = $height;
            $node->field_track_position[$node->language][0]['field']['map_width'] = $width;
            $node->field_track_position[$node->language][0]['field']['zoom'] = $zoom;
            $node->field_track_position[$node->language][0]['field']['name'] = "$key - ".date('YmdHis');*/
        }
        
        echo json_encode($subject_terms);
        break;
        
       
        
}