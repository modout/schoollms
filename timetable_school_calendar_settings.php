<?php

//CONNECT TO DB
//include 'sphs_school_data_db.inc';

include("data_db_mysqli.inc");
$data = new data();
$data->username = "root";
$data->password = "$0W3t0";
$data->host = "localhost";
$data->db = "school_lms_dev_support";

if (!isset($_SERVER["HTTP_HOST"])) {
  parse_str($argv[1], $_GET);
  parse_str($argv[1], $_POST);
}

extract($_GET);
extract($_POST);

switch ($action){
    
    case 'get_subject_link':
        
        break;
    
    case 'get_current_term':
        
        $today = date('D:d M:m Y H:i:s');
        
        $day_tokens = explode(" ", $day);

        //$q = "SELECT  FROM "
        $year = $day_tokens[2];
        $month_items = explode(":", $day_tokens[1]);
        $day_items = explode(":", $day_tokens[0]);
        $clock_items = explode(":", $day_tokens[3]);

        $today_token = "$year-$month_items[1]-$day_items[1] 00:00:00";
        
        $q = "SELECT year_id FROM schoollms_schema_userdata_school_year WHERE year_label = '$year'";
        
        $result = $data->exec_sql($q);
        
        $year_id = $result[0]->year_id;
        
        $calendar_types = array (2, 3, 4, 5);
        
        foreach ($calendar_types as $key => $type_id) {
            $q = "SELECT * FROM schoollms_schema_userdata_school_year_calendar WHERE $today_token BETWEEN REPLACE(SUBSTR(";
        }
        echo json_encode($subject_terms);
        break;
        
       
        
}