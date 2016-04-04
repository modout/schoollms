<?php

include("data_db.inc");
$data = new data();
$data->username = "root";
$data->password = "$0W3t0";
$data->host = "localhost";
$data->db = "school_lms_dev_schools_sphs_teach";

if (!isset($_SERVER["HTTP_HOST"])) {
  parse_str($argv[1], $_GET);
  parse_str($argv[1], $_POST);
}

extract($_GET);
extract($_POST);

switch ($action){

    case 'open_link':
        define('DRUPAL_ROOT', getcwd());
        require_once DRUPAL_ROOT.'/includes/bootstrap.inc';
        drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
        module_load_include('module', 'entity', $name= NULL);
        module_load_include('module', 'node', $name= NULL);
        module_load_include('inc', 'node', 'node.pages');
        module_load_include('inc', 'pathauto', $name= NULL);
        module_load_include('module', 'field', $name= NULL);
        module_load_include('module', 'geofield', $name= NULL);
        module_load_include('install', 'geofield', $name= NULL);
        module_load_include('module', 'ctools', $name= NULL);
        module_load_include('module', 'menu', $name= NULL);
          
        global $user;
        $account = user_authenticate($username, $passwd);
        $user = user_load($account, TRUE);
        drupal_session_regenerate();
        drupal_goto($q);
        break;
    
    case 'save_new_class':
        $subject_tokens = explode(",", $subject_nids);
        define('DRUPAL_ROOT', getcwd());
        require_once DRUPAL_ROOT.'/includes/bootstrap.inc';
        drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
        module_load_include('module', 'entity', $name= NULL);
        module_load_include('module', 'node', $name= NULL);
        module_load_include('inc', 'node', 'node.pages');
        module_load_include('inc', 'pathauto', $name= NULL);
        module_load_include('module', 'field', $name= NULL);
        module_load_include('module', 'geofield', $name= NULL);
        module_load_include('install', 'geofield', $name= NULL);
        module_load_include('module', 'ctools', $name= NULL);
        module_load_include('module', 'menu', $name= NULL);
          
        global $user;
        $account = user_authenticate($username, $passwd);
        $user = user_load($account, TRUE);
        drupal_session_regenerate();
        
        $node = new stdClass();
        $node->type = 'class';
        node_object_prepare($node);
        
        $node->title = "Grade $grade_no"."$class_label"; //. date('c');
        $node->language = 'und';
        $node->uid = $user->uid; 
        $node->status = 0; //(1 or 0): published or not
        $node->revision = FALSE;
        $node->promote = 0; //(1 or 0): promoted to front page
        $node->comment = 0; // 0 = comments disabled, 1 = read only, 2 = read/write
        $node->sticky = 0;
        $node->log = NULL;
        $node->created  = time() - (rand( 1,240) * 60);
        $node->field_group_group[$node->language][]['field']['group_group_value'] = 1;
        $node->field_group_access[$node->language][]['field']['group_access_value'] = 2;
        $node->field_requires_validation[$node->language][]['field']['requires_validation_value'] = 1;
        $node->field_anomymous_visibility[$node->language][]['field']['anomymous_visibility_value'] = 0;
        $node->field_catalogue_visibility[$node->language][]['field']['catalogue_visibility_value'] = 0;
        //FOREACH CORE SUBJECT IN grade_no
        $node->field_opigno_class_courses[$node->language][]['opigno_class_courses_target_id'] = $subject_nid;
        //$node->field_opigno_class_courses[$node->language][]['target_type'] = "course";
        $node->field_class_quota[$node->language][]['field']['class_quota_value'] = -1;
        //$node->opigno_course_tools[$node->language][5]['field']['columns']['tool'] = "";
        //$node->opigno_course_tools[$node->language][0]['field']['tool'] = '';
        $node = node_submit($node);
        node_save($node);
        break;
    
    case 'save_new_subject':
        define('DRUPAL_ROOT', getcwd());
        require_once DRUPAL_ROOT.'/includes/bootstrap.inc';
        drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
        module_load_include('module', 'entity', $name= NULL);
        module_load_include('module', 'node', $name= NULL);
        module_load_include('inc', 'node', 'node.pages');
        module_load_include('inc', 'pathauto', $name= NULL);
        module_load_include('module', 'field', $name= NULL);
        module_load_include('module', 'geofield', $name= NULL);
        module_load_include('install', 'geofield', $name= NULL);
        module_load_include('module', 'ctools', $name= NULL);
        module_load_include('module', 'menu', $name= NULL);
          
        global $user;
        $account = user_authenticate($username, $passwd);
        $user = user_load($account, TRUE);
        drupal_session_regenerate();
      
        $term_no = 1;
        $subject_nid = array ();
        while ($term_no <= 4){  
            //echo "ADD $add_new_subject SUBJECT $subject_name Grade $grade_no - Term $term_no";
            $node = new stdClass();
            $node->type = 'course';
            node_object_prepare($node);
//
            $node->title = "$subject_name Grade $grade_no - Term $term_no"; //. date('c');
            $node->language = 'und';
            $node->uid = $user->uid; 
            $node->status = 0; //(1 or 0): published or not
            $node->revision = FALSE;
            $node->promote = 0; //(1 or 0): promoted to front page
            $node->comment = 0; // 0 = comments disabled, 1 = read only, 2 = read/write
            $node->sticky = 0;
            $node->log = NULL;
            $node->created  = time() - (rand( 1,240) * 60);
            $node->field_group_group[$node->language][]['field']['group_group_value'] = 1;
            $node->field_opigno_commerce_price[$node->language][]['field']['opigno_commerce_price_amount'] = 0;
            $node->field_opigno_commerce_price[$node->language][]['field']['opigno_commerce_price_currency_code'] = 'ZAR';
            $node->field_opigno_commerce_price[$node->language][]['field']['opigno_commerce_price_data'] = 'a:1:{s:10:"components";a:0:{}}';
            $node->field_group_access[$node->language][]['field']['group_access_value'] = 2;
            $node->field_requires_validation[$node->language][]['field']['requires_validation_value'] = 1;
            $node->field_anomymous_visibility[$node->language][]['field']['anomymous_visibility_value'] = 0;
            $node->field_opigno_course_categories[$node->language][]['field']['opigno_course_categories_tid'] = 23;//MUST FIND WAY TO ASSOCIATE GRADE DATA
            $node->field_field_school_term[$node->language][]['field']['field_school_term'] = "Term $term_no";
            $node->field_opigno_course_tools[$node->language][]['field']['opigno_course_tools_tool'] = 'quiz';
            $node->field_opigno_course_tools[$node->language][]['field']['opigno_course_tools_tool'] = 'quiz_import';
            $node->field_opigno_course_tools[$node->language][]['field']['opigno_course_tools_tool'] = 'tft';
            $node->field_opigno_course_tools[$node->language][]['field']['opigno_course_tools_tool'] = 'video';
            $node->field_opigno_course_tools[$node->language][]['field']['opigno_course_tools_tool'] = 'poll';
            $node->field_opigno_course_tools[$node->language][]['field']['opigno_course_tools_tool'] = 'forum';
            $node->field_opigno_course_tools[$node->language][]['field']['opigno_course_tools_tool'] = 'opigno_group_statistics';
            $node->field_opigno_course_tools[$node->language][]['field']['opigno_course_tools_tool'] = 'in_house';
            $node->field_opigno_course_tools[$node->language][]['field']['opigno_course_tools_tool'] = 'audio';
            $node->field_course_quota[$node->language][]['field']['course_quota_value'] = -1;
            //$node->opigno_course_tools[$node->language][5]['field']['columns']['tool'] = "";
            //$node->opigno_course_tools[$node->language][0]['field']['tool'] = '';
            $node = node_submit($node);
            node_save($node);

            $q = "SELECT nid FROM node WHERE title = '$subject_name Grade $grade_no - Term $term_no'";

            $subject_nid["$subject_name Grade $grade_no - Term $term_no"] = db_query($q)->fetchField();

            $term_no++;
        }

        echo json_encode($subject_nid);
        break;
    
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
            | field_data_course_quota                                |
            | field_data_course_required_course_ref                  |
            | field_data_course_required_quiz_ref                    |
            | field_data_opigno_class_courses                        |
            | field_data_opigno_course_categories                    |
            | field_data_opigno_course_image                         |
            | field_data_opigno_course_tools                         |
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