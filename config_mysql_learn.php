<?php 
global $data;
include("db.inc");
//var_dump($data);

//include('data_db_mysqli.inc');

// old mysql extension (it has been deprecated as of PHP 5.5.0 and will be removed in the future)

// define database host (99% chance you won't need to change this value)
$db_host = 'localhost';

// define database name, user name and password
$db_name = 'school_lms_dev_support';
$db_user = 'root';
//$db_pwd  = '12_s5ydw3ll1979';
$db_pwd  = '$0W3t0';
// reset record set to null ($rs is used in timetable function)
$rs = null;
$timetable_items = null;
$user_id = 1;
$timetable_id = 0;
$periods = array();
// open database connection and select database
//$link = mysql_connect('localhost', 'root', '');
$db_conn = mysqli_connect($db_host, $db_user, $db_pwd,$db_name);
//mysqli_select_db($db_name, $db_conn);

// function executes SQL statement and returns result set as Array
function sqlQuery($sql) {
    global $db_conn;
	global $data;
	$resultSet = Array();
    // execute query	
    /*$db_result = mysql_query($sql, $db_conn);
    // if db_result is null then trigger error
    if ($db_result === null) {
        trigger_error(mysql_errno() . ": " . mysql_error() . "\n");
        exit();
    }
    // prepare result array
   
    // if resulted array isn't true and that is in case of select statement then open loop
    // (insert / delete / update statement will return true on success) 
    if ($db_result !== true) {
        // loop through fetched rows and prepare result set
        while ($row = mysql_fetch_array($db_result, MYSQL_NUM)) {
            // first column of the fetched row $row[0] is used for array key
            // it could be more elements in one table cell
            $resultSet[$row[0]][] = $row;
        }
    }*/
	$mysql_num = "";
	if(function_exists("mysql_connect") )
	{
		$mysql_num = MYSQL_NUM;
	}
	else{
		$mysql_num = MYSQLI_NUM;
	}
	if(strpos(strtoupper($sql),"INSERT") > -1 or strpos(strtoupper($sql),"UPDATE") > -1)
	{
		$data->execNonSql($sql);
	}
	else{
		$data->execSQL($sql);
		while($row = $data->getRow("array",$mysql_num))
		{
			$resultSet[$row[0]][] = $row;
		}
	}	
    // return result set
    return $resultSet;
}

function sqlEscapeString($string){
    global $db_conn;
    return mysqli_real_escape_string($db_conn, $string);
}

// commit transaction
function sqlCommit() {
	global $db_conn;
	mysqli_query($db_conn,'commit' );
	mysqli_close($db_conn);
}

//Prepare School Time Table View for user;
function prepareTableView($user_id, $time_table_id,$user_type, $year_id){
    
    global $timetable_id;
    
    
    //if ($year_id == null){
         $today = date('D:d M:m Y H:i:s');
        
         //echo "TODAY $today\n";
        $day_tokens = explode(" ", $today);

        //$q = "SELECT  FROM "
        $year = $day_tokens[2];
        
        $q = "SELECT year_id FROM schoollms_schema_userdata_school_year WHERE year_label = '$year'";
        
        $result = sqlQuery($q);
        
        foreach ($result as $year_data){
            $year_id = $year_data[0][0];
            break;
        }    
        
        
    //}
    
    //Get User Details For Table Retrieval
    switch ($user_type){
        
        case 2:
            $q = "SELECT * FROM schoollms_schema_userdata_learner_timetable WHERE user_id = $user_id AND year_id = $year_id";
            break;
        
        case 4:
            $q = "SELECT timetabl_id FROM schoollms_schema_userdata_teacher_timetable WHERE user_id = $user_id AND year_id = $year_id";
            break;
        
        case 5:
        
        case 6:
            $q = "SELECT timetabl_id FROM schoollms_schema_userdata_school_timetable_items WHERE timetabl_id = $time_table_id";
            break;
    }
    
    //echo "Q $q \n";
   
    //Get USER TIMETABLE ID
    $timetable_result = sqlQuery($q);
    $item_count = count($timetable_result);
    if ($item_count > 0){
        foreach ($timetable_result as $items) {
            $timetable_id = $items[0][0];
            
            break;
        }
    } else {
        switch ($user_type){
        
            case 2:
                $timetable_id = $time_table_id;
                break;
            
            case 4:
                //echo "BUILD TEACHER TITMETBAE <br>";
                $timetable_id = build_teacher_timetable($user_id, $year_id);
                break;
            
            case 5:
            
            case 6:
                $timetable_id = build_class_timetable($time_table_id, $year_id);
                break;
        }
    }

    $result = sqlQuery("delete from schoollms_schema_userdata_school_timetable_view where timetabl_id = $timetable_id");
    //echo "TIMETABLE $timetable_id \n";
    //IF FALSE Get User Time Table According to Today
    $today = date('D:d M:m Y H:i:s');
    
    //Get TimeDays From Today
    $days = days($today);
    
    //REMOVE PREVIOUS TIMETABLE LOAD IF FIRST DATE IS DIFFERENT TO TODAY
    
    //FOR EACH DAY LOAD USER TIMETABLE
    $row = 1;
    
    foreach ($days as $day) {
        
        $timetable_day = $day['timetable_day'];
        $timetable_day_date = $day['timetable_day_date'];
        
        $tokens = explode(" ", $timetable_day_date);
        
        $year = $tokens[2];
        $month_items = explode(":", $tokens[1]);
        $day_items = explode(":", $tokens[0]);
        $clock_items = explode(":", $tokens[3]);
        
        $date_stamp = "$day_items[1]-$month_items[1]-$year";
        //GET DAY ITEMS
        //switch ($user_type){
        
            //case 2:
        $sql = "select * from schoollms_schema_userdata_school_timetable_items where timetabl_id = $timetable_id and day_id =  $timetable_day order by period_label_id asc";
              //  break;
            
            //case 4:
            //    $sql = "select * from schoollms_schema_userdata_school_timetable_items where teacher_id = $user_id and day_id =  $timetable_day order by period_label_id asc";
          //      break;
            
        //}
        
        $timetable_items = sqlQuery($sql);
        
        $col = 1;
        $item_count = count($timetable_items);
        //echo "TTITEMS = $item_count SQL $sql <br>";
        
        $slot_details = "";
        
        foreach ($timetable_items as $items) {
            
            foreach ($items as  $item){
                
                $item_count = count($items);

                //echo "ITEMS = $item_count<br>";

                $period_label_id = $item[2];
                $grade_id = $item[3];
                $class_id = $item[4];
                $room_id = $item[5];
                $subject_id = $item[6];
                $teacher_id = $item[7];
                $substitute_id = $item[8];
                
                //echo "USER TYPE $user_type <br>";
                
                switch ($user_type) {
                    case 1:
                    case 2:
                    case 3:
                    case 5:
                    case 6:
                        //echo "I GET HERE <br>";
                        $slot_details = items($timetable_id, $timetable_day, $date_stamp, $period_label_id, $grade_id, $class_id, $room_id, $subject_id, $teacher_id, $substitute_id, $user_type, $user_id); 
                        break;

                    case 4:
                        //echo "if ($teacher_id == $user_id) <br>";
                        if ($teacher_id == $user_id){
                            $slot_details = items($timetable_id, $timetable_day, $date_stamp, $period_label_id, $grade_id, $class_id, $room_id, $subject_id, $teacher_id, $substitute_id, $user_type, $user_id); 
                        } else {
                            $slot_details = "";
                        }
                        break;

                    default:
                        break;
                }
               
                //echo "SLOT $slot_details <br />";
                
                //echo "TT $timetable_id,D $timetable_day,P $period_id,R $room_id,S $subject_id,T $teacher_id,SUB $substitute_id <br>";
                $slot_details = sqlEscapeString($slot_details);

                //$pos = "$row"."_$period_id"
                $sql = "insert into schoollms_schema_userdata_school_timetable_view values ($timetable_id,'$date_stamp',$row,$period_label_id,'s-$subject_id','$slot_details')";
                //echo "SQL $sql <br />";
                $result = sqlQuery($sql);
            }
        }
        
        $row++;
    }
    
    return $timetable_id;
}

function build_class_timetable($timetable_id, $year_id){

    $q = "SELECT timetable_type_item_id FROM schoollms_schema_userdata_school_timetable WHERE timetabl_id = $timetable_id AND timetable_type_id = 3";
    
    $result = sqlQuery($q);
    
    $seen_timetable_id = 0;
    foreach ($result as $items) {
        $class_id = $items[0][0];
        
        $q = "SELECT * FROM schoollms_schema_userdata_school_timetable_items WHERE class_id = $class_id ORDER BY timetabl_id, day_id, period_label_id ASC";
        
        $result2 = sqlQuery($q);
        
        foreach ($result2 as $items2){
            
            $timetabl_id = $items2[0][0];
            
            if ($seen_timetable_id == 0){
                $q = "SELECT * FROM schoollms_schema_userdata_school_timetable_items WHERE timetabl_id = $timetabl_id ORDER BY day_id, period_label_id ASC";

                $result3 = sqlQuery($q);
                
                $count_rows = count($result3[$timetabl_id]);
                if ($count_rows !== 80){
                    continue;
                } else {
                    $seen_timetable_id = $timetabl_id;
                    $result2 = $result3[$seen_timetable_id];
           
                    break;
                }
            }
            
        }
            
        if ($seen_timetable_id !== 0){
            foreach ($result2 as $items2){

                $day_id = $items2[1];
                $period_label_id = $items2[2];
                $grade_id = $items2[3];
                //$class_id = $items2[$key][4];
                $room_id = $items2[5];
                $subject_id = $items2[6];
                $teacher_id = $items2[7];
                $substitute_id = $items2[8];

                $q = "SELECT * FROM schoollms_schema_userdata_school_timetable_items WHERE timetabl_id = $timetable_id AND day_id = $day_id AND period_label_id = $period_label_id";

                $result3 = sqlQuery($q);

                $count_result = count($result3[$timetable_id]);

                if ($count_result > 0){

                } else {
                    $q = "insert into schoollms_schema_userdata_school_timetable_items values ($timetable_id, $day_id, $period_label_id, $grade_id, $class_id, $room_id, $subject_id, $teacher_id, $substitute_id)";

                    $result3 = sqlQuery($q);
                }
              
            }
            break;
        }
    }
    
    return $timetable_id;
}

function build_learner_timetable($user_id, $year_id){
    
}

function build_teacher_timetable($user_id, $year_id){

    //EVERY PERSON ON ACCESS PROFILE MUST EXIST ONCE - IF THEY MOVE SCHOOL CHANGE SCHOOL_ID
    $q = "SELECT school_id, name, surname FROM schoollms_schema_userdata_access_profile WHERE user_id = $user_id";
    
    //echo "Q $q <br>\n";
    
    $result = sqlQuery($q);
    
    foreach ($result as $items) {
        $school_id = $items[0][0];
        $name = $items[0][1];
        $surname = $items[0][2];
        //$surname = $items[0][1];
        break;
    } 
    
    
   
    $q = "INSERT INTO schoollms_schema_userdata_school_timetable VALUES (NULL, $school_id, 2, $user_id,'$name $surname');";

    //echo "Q $q <br>\n";

    $result = sqlQuery($q);
    
    $q = "SELECT timetabl_id FROM schoollms_schema_userdata_school_timetable WHERE timetable_type_id = 2 AND timetable_type_item_id = $user_id AND timetable_label = '$name $surname'";

    //echo "Q $q <br>\n";
    
    $result = sqlQuery($q);
    
    foreach ($result as $items) {
        $timetable_id = $items[0][0];
        break;
    }

    //echo "TIMETABLE ID $timetable_id <br>";
    
    $q  = "insert into schoollms_schema_userdata_teacher_timetable values ($user_id, $timetable_id, $year_id, 'NEW')";

    //echo "Q $q <br>\n";

    $result = sqlQuery($q);

    $q = "select * from schoollms_schema_userdata_school_timetable_items where teacher_id = $user_id order by day_id, period_label_id asc";

    //echo "Q $q <br>\n";

    $timetable_items = sqlQuery($q);

    foreach ($timetable_items as $items) {

        foreach ($items as  $item){

            $item_count = count($items);

            //echo "ITEMS = $item_count<br>";
            $day_id = $item[1];
            $period_label_id = $item[2];
            $grade_id = $item[3];
            $class_id = $item[4];
            $room_id = $item[5];
            $subject_id = $item[6];
            //$teacher_id = $item[7];
            $substitute_id = $item[8];

            $q = "SELECT * FROM schoollms_schema_userdata_teacher_schooldetails WHERE user_id = $user_id AND school_id = $school_id AND grade_id = $grade_id AND class_id = $class_id AND subject_id = $subject_id AND year_id = $year_id";
    
            $schooldetails = sqlQuery($q);

            if (count($schooldetails) == 0){
                //WORK ON TABLE TO KEEP TRACK OF USER YEAR ON ACCESS PROFILE
               $q = "INSERT INTO schoollms_schema_userdata_teacher_schooldetails VALUE ($user_id, $school_id,$grade_id, $class_id, $subject_id, $year_id)"; 
            
               $result = sqlQuery($q);
            }
            
            $q = "SELECT * FROM schoollms_schema_userdata_school_timetable_items WHERE timetabl_id = $timetable_id AND day_id = $day_id AND period_label_id = $period_label_id AND grade_id =  $grade_id AND class_id = $class_id AND room_id = $room_id AND subject_id = $subject_id AND teacher_id = $user_id";
            
            $result = sqlQuery($q);
            
            if (count($result) == 0){
                $q = "insert into schoollms_schema_userdata_school_timetable_items values ($timetable_id, $day_id, $period_label_id, $grade_id, $class_id, $room_id, $subject_id, $user_id, $substitute_id)";

                //echo "Q $q <br>\n";

                $result = sqlQuery($q);
            }
        }
    }
//    } else {
//        $timetable_id = 0;
//    }
    
    return $timetable_id;
}


function print_days($time_table_id = 22, $user_type, $user_id, $year_id, $school_id){
    
    $today = date('D:d M:m Y H:i:s');
	
    
   // echo "SCHOOL_ID : $school_id";
    
    //Get TimeDays From Today
    $days = days($today);
    //var_dump($days);
    //REMOVE PREVIOUS TIMETABLE LOAD IF FIRST DATE IS DIFFERENT TO TODAY
    
    //FOR EACH DAY LOAD USER TIMETABLE
    foreach ($days as $key=>$day) {
        
        $timetable_day = $day['timetable_day'];
        $timetable_day_date = $day['timetable_day_date'];
        $tokens = explode(" ", $timetable_day_date);
        
        $year = $tokens[2];
        $month_items = explode(":", $tokens[1]);
        $day_items = explode(":", $tokens[0]);
        $clock_items = explode(":", $tokens[3]);
        $day_string = "<b>Day $timetable_day</b><br>$day_items[0]<br>$day_items[1]-$month_items[0]-$year";
        //GET DAY ITEMS
		
        print_day($day_string, $key+1,$time_table_id, $user_type, $user_id, $year_id, $school_id);
    }
}

// print subjects
function subjects() {
	// returned array is compound of nested arrays
	$subjects = sqlQuery('select subject_id, subject_title from schoollms_schema_userdata_school_subjects order by subject_title');
	// print_r($subjects);
	foreach ($subjects as $subject) {
		$id   = $subject[0][0];
		$name = $subject[0][1];
		print "<tr><td class=\"dark\"><div id=\"$id\" class=\"redips-drag redips-clone $id\">$name</div><input id=\"b_$id\" class=\"$id\"type=\"button\" value=\"\" onclick=\"report('$id')\" title=\"Show only $name\"/></td></tr>\n";
	}
}

function periods ($school_id){
    
    //$periods = sqlQuery('select *  from schoollms_schema_userdata_school_timetable_period order by period_id');
    //echo "SCHOOL_ID $school_id";
    
    $period_labels = sqlQuery("select * from schoollms_schema_userdata_school_timetable_period_labels where school_id = $school_id order by period_label_id ASC");
    
    //var_dump($period_labels);
    
    foreach ($period_labels as $period_label) {
    
        $id = $period_label[0][0];
        //$start = $period[0][3];
        //$label_id = $period[0][5];
        $label = $period_label[0][2];//sqlQuery("select period_label from schoollms_schema_userdata_school_timetable_period_labels where period_label_id = $label_id");
//        foreach ($period_label as $period_label_data) {
//            $label = $period_label_data[0][0];
//            break;
//        }
//        $end = $period[0][4];
        
        print "<td class=\"redips-mark dark\"><b>$label </b></td>";
        //print "<td class=\"redips-mark dark\"><b>$label </b><br> $start-$end </td>";
    }
    
}

function days($today){
    
    //Get Today Items
    $today_items = explode(" ", $today);
    //echo "today Items : $today_items";
    $year = $today_items[2];
    $month_items = explode(":", $today_items[1]);
    $day_items = explode(":", $today_items[0]);
    $clock_items = explode(":", $today_items[3]);
    
    //Get Year Start Date
    $year_start_date = sqlQuery("select start_time from schoollms_schema_userdata_school_year_calendar where calendar_type = 1 and year_id in (select year_id from schoollms_schema_userdata_school_year where year_label = '$year')");
    foreach ($year_start_date as $start_date) {
        $start = $start_date[0][0];
    }
    
    $year_start_date_items = explode("|", $start);
    
    
    //Fix Date Format to YYYY-MM-DD
    //Find Todays Timetable Day
    $today_token = "$year-$month_items[1]-$day_items[1]";
    $start_date_token = "$year_start_date_items[0]-$year_start_date_items[1]-$year_start_date_items[2]";
    
    //echo "<br> T $today_token S $start_date_token";
    //Count days between start date and today
    $num_days = getSchoolDays($start_date_token, $today_token);
    //$num_days = removeHolidays($num_days,)
    //Get School Days
    $timetable_days = sqlQuery("select * from schoollms_schema_userdata_school_timetable_days");
    
    $num_timetable_days = count($timetable_days);
    
   // echo "NUM TABLE DAYS $num_timetable_days";
    
    $today_day = getTodayTimeTableDay($num_days, $num_timetable_days);
    
    //echo "TODAY $today DAY $today_day <br>";
    
    //Get Days with Date  - From today to the last timetable day
    return getDays($today, $today_day, $num_timetable_days);
    
}

function getSchoolDays($start_date_token, $today_token){
    //Remove weekends and Holidays from $date_1 to $date_2
    $count_days = 1;
    $new_num_days = 0;
    $last_day = $start_date_token;
    
    //Get Num Days
    $num_days = dateDifference($start_date_token, $today_token, '%a');
    
    //echo "NUM DAYS $num_days";
    
    //echo "<br> START DAY $last_day";
    //Weekends
    $weekends = array ("Sat", "Sun");
    
    while ($count_days <= $num_days){
        $next_day =  add_date($last_day,1,0,0);
        $next_day_tokens = explode(" ", $next_day);
        
        //echo "<br> NEXT DAY $next_day";
        
        $year = $next_day_tokens[2];
        $month_items = explode(":", $next_day_tokens[1]);
        $day_items = explode(":", $next_day_tokens[0]);
        $clock_items = explode(":", $next_day_tokens[3]);
    
        //Check Weekends
        if (in_array($day_items[0], $weekends)){
            //Do Nothing
        } elseif (isHoliday($next_day)) {//Check Holidays
            //Do Nothing
        } else {
            $new_num_days++;
        }

        $next_day = "$year-$month_items[1]-$day_items[1]";
        $last_day = $next_day;
        $count_days++;
    }
    
    //echo "<br> NUM SCHOOL DAYS $new_num_days";
    
    return $new_num_days;
    
    
}


function isHoliday($day){

    $result = FALSE;
    $day_tokens = explode(" ", $day);
  
    $year = $day_tokens[2];
    $month_items = explode(":", $day_tokens[1]);
    $day_items = explode(":", $day_tokens[0]);
    $clock_items = explode(":", $day_tokens[3]);
    
    $day_test_token = "$year|$month_items[1]|$day_items[1]|00|00|00";
    
    //Get Holidays
    $year_holidays = sqlQuery("select start_time from schoollms_schema_userdata_school_year_calendar where calendar_type = 6 and year_id in (select year_id from schoollms_schema_userdata_school_year where year_label = '$year')");
    //$year_start_date_items = explode("|", $year_start_date[0][0][0]);
    foreach ($year_holidays as $holidays) {
        $holiday = $holidays[0][0];
        
        //echo "IF HOLIDAY $holiday = DAY TEST $day_test_token <br>";
        
        if (strcmp($holiday, $day_test_token) == 0){
            $result = TRUE;
            break;
        }
    }
    
    return $result;
}


function getDays($today, $today_day, $num_timetable_days){
     
    //Weekends
    $weekends = array ("Sat", "Sun");
    
    $days = array ();
    $day_store = array ();
    $day_store['timetable_day'] = $today_day;
    $day_store['timetable_day_date'] = $today;
    array_push($days, $day_store);
    
    //Get Today Items
    $today_items = explode(" ", $today);
    
    $year = $today_items[2];
    $month_items = explode(":", $today_items[1]);
    $day_items = explode(":", $today_items[0]);
    $clock_items = explode(":", $today_items[3]);
    
    $today_token = "$year-$month_items[1]-$day_items[1]";
    $last_day = $today_token;
    
    $next_day_day = $today_day + 1;
    
    //echo "TODAY $today DAY $today_day <br>";
    $count_days = 1;
    while ($count_days <= $num_timetable_days){
        
        $next_day =  add_date($last_day,1,0,0);
        $next_day_tokens = explode(" ", $next_day);
        
        $year = $next_day_tokens[2];
        $month_items = explode(":", $next_day_tokens[1]);
        $day_items = explode(":", $next_day_tokens[0]);
        $clock_items = explode(":", $next_day_tokens[3]);
    
        //Check Weekends
        if (in_array($day_items[0], $weekends)){
            //Do Nothing
        } elseif (isHoliday($next_day)) {//Check Holidays
            //Do Nothing
        } else {
            if ($next_day_day > $num_timetable_days){
                $next_day_day = 1;
            }
    
            //echo "DATE $next_day DAY $next_day_day COUNT $count_days <br>";
            
            $day_store['timetable_day'] = $next_day_day;
            $day_store['timetable_day_date'] = $next_day;
            array_push($days, $day_store);
            $next_day_day++;
            $count_days++;
        }
        
        
        $next_day = "$year-$month_items[1]-$day_items[1]";
        $last_day = $next_day;
        
    }
    
    return $days;
}

function add_date($givendate,$day=0,$mth=0,$yr=0) {
    $cd = strtotime($givendate);
    $newdate = date('D:d M:m Y H:i:s', mktime(date('h',$cd),
    date('i',$cd), date('s',$cd), date('m',$cd)+$mth,
    date('d',$cd)+$day, date('Y',$cd)+$yr));
    return $newdate;
}

function getTodayTimeTableDay($num_days, $num_timetable_days){
    
    if ($num_days > $num_timetable_days){
        $today_day = $num_days - $num_timetable_days;
        while ($today_day > $num_timetable_days){
            $today_day = $today_day - $num_timetable_days;
        }
    } else {
        $today_day = $num_timetable_days - $num_days;
    }
    
    if ($today_day == 0){
        $today_day = 1;
    }
    
    return $today_day;
}

function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
{
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);
    
    $interval = date_diff($datetime1, $datetime2);
    
    return $interval->format($differenceFormat);
    
}

function items($timetable_id, $day_id, $date_stamp, $period_label_id, $grade_id, $class_id, $room_id, $subject_id, $teacher_id, $substitute_id, $user_type, $user_id){
    
    global $timetable_items;

    //Get TimeTable Item Fields (IF NOT initialized)
    if ($timetable_items === null) {

        $sql = "select * from schoollms_schema_userdata_school_timetable_items_form";
        $timetable_items = sqlQuery($sql);
    }
    
    //$count = count($timetable_items);
    
    //echo "COUNT ITEMS $count <br>";
    //Get TimeTable Details
    foreach ($timetable_items as $items) {
        
        $field = $items[0][1];
        //$field_data_link = $items[0][8];
        $field_references = explode("#", $items[0][8]);
                
        $tablename = $field_references[0];
        $select_field = $field_references[1];
        $display_field = $field_references[2];
        
        switch ($field) {
            case 'timetabl_id':
                
                break;

            case 'day_id':


                break;
            
            case 'period_label_id':
            
                break;
            
            
            case 'room_id':
                
                $sql = "select $display_field from $tablename where $select_field = '$room_id'";
                $result = sqlQuery($sql);
                foreach ($result as $value) {
                    $room = $value[0][0];
                }
                
                break;
           
             case 'class_id':
                $sql = "select $display_field from $tablename where $select_field = '$class_id'";
                $result = sqlQuery($sql);
                foreach ($result as $value) {
                    $class = $value[0][0];
                }
                break;
                
             case 'grade_id':
                $sql = "select $display_field from $tablename where $select_field = '$grade_id'";
                $result = sqlQuery($sql);
                foreach ($result as $value) {
                    $grade = $value[0][0];
                }
                break;
                
            case 'subject_id':
                $sql = "select $display_field from $tablename where $select_field = '$subject_id'";
                $result = sqlQuery($sql);
                foreach ($result as $value) {
                    $subject = $value[0][0];
                }

                break;
            
            case 'teacher_id':
                $sql = "select * from $tablename where $select_field = '$teacher_id'";
                $result = sqlQuery($sql);
                foreach ($result as $value) {
                    $name = $value[0][4];
                    $surname = $value[0][5];
                    $teacher = "$name $surname";
                    break;
                }
                

                //Get Teacher Details
                break;
            
            case 'substitude_id':
                switch ($user_type) {
                    case 1://STAFF
                    case 2://LEARN
                    case 3://PARENT
                    case 5://MANAGE
                    case 6://SUPPORT 
                        $sql = "select $display_field from $tablename where $select_field = '$substitute_id'";
                        $result = sqlQuery($sql);
                        foreach ($result as $value) {
                            $substitute = $value[0][0];
                        }
                        break;
                        
                    case 4://TEACHER
                        $sql = "SELECT class_label FROM schoollms_schema_userdata_school_classes WHERE class_id IN (SELECT class_id FROM schoollms_schema_userdata_school_timetable_items WHERE timetabl_id = $timetable_id AND day_id = $day_id AND period_label_id = $period_label_id AND teacher_id = $teacher_id)";
                        $result = sqlQuery($sql);
                        foreach ($result as $value) {
                            $substitute = $value[0][0];
                        }
                        //echo "CLASS $substitute <br>";
                        break;
                }
                break;
            
            default:
                break;
        }
    }
    
    switch ($user_type) {
        case 2:
            
        case 4:
            $links = get_timetable_slot_links($timetable_id, $date_stamp, $day_id, $period_label_id, $class_id, $user_type, $user_id);
            break;
        
        case 5:
            
        case 6:
            $links = "";
            break;
        
        default:
            break;
    }
    
    //echo "<b>$subject</b><br>$teacher<br>$room<br>$substitute<br>$links";
    return @"<b>$subject</b><br>$teacher<br>$room<br>$substitute <br> $links";
   
    
}

//get time table links
function get_timetable_slot_links($timetable_id, $date_stamp, $day_id, $period_label_id, $class_id, $user_type, $user_id){
    
    $links = "";
    
    $today = date('D:d M:m Y H:i:s');
    
    $today_items = explode(" ", $today);
    
    $year = $today_items[2];
    $month_items = explode(":", $today_items[1]);
    $day_items = explode(":", $today_items[0]);
    $clock_items = explode(":", $today_items[3]);
    
    $today_token1 = "$day_items[1]-$month_items[0]-$year";
    
    $today_token2 = "$day_items[1]-$month_items[1]-$year";
    
    
    //echo "if ($date_stamp == $today_token2){<br>";
    
    //if (strcmp($date_stamp,$today_token2) == 0){
        
        
        $q = "SELECT lesson_url,lesson_title FROM schoollms_schema_userdata_school_timetable_subject_lessons WHERE day_id = $day_id AND period_label_id = $period_label_id AND lesson_date = '$today_token1'";

        $result = sqlQuery($q);

        if (count($result) > 0){
            $links .= "<b><i> LESSON(S):</i> </b> <br>";
            $lessons = array ();
            foreach ($result as $value) {
                $lesson_title = $value[0][1];
                $lesson_url = $value[0][0];
                $lessons["$lesson_url"] = $lesson_title;
                //$teacher = "$name $surname";
            }

            //echo "LESSON TITLE $lesson_title URL $lesson_url <br>";
            //Get USER DETAILS
            $q = "SELECT name, surname FROM schoollms_schema_userdata_access_profile WHERE user_id = $user_id";

            $result = sqlQuery($q);

            foreach ($result as $items) {
                $name = $items[0][0];
                $surname = $items[0][1];
                break;
            }


            switch ($user_type) {
                case 2:
                    $q = "SELECT timetable_label FROM schoollms_schema_userdata_school_timetable WHERE timetabl_id = $timetable_id";
                    $result = sqlQuery($q);

                    foreach ($result as $items) {
                        $timetable_label = $items[0][0];
                        //$surname = $items[0][1];
                        break;
                    }
                    
                    //if ($timetable_label !== "$surname $name"){
                        $username = $timetable_label;
                   // }
                        
                    $passwd = "learn123";
                    $url = "http://172.16.0.9/learnqa/local_timetable_schoollms_link.php";
                    
                    foreach ($lessons as $lesson_url => $lesson_title) {
                        $pars = "action=open_link&q=$lesson_url&username=$username&passwd=$passwd";
                        $links .= "<a href='$url?$pars' target='_blank'> $lesson_title </a> <br><br>";
                    }
                    

                    break;

                case 4:
                    $passwd = "teach123";
                    $url = "http://172.16.0.9/teachqa/local_timetable_schoollms_link.php";
                    $username="$name $surname";
                    
                    foreach ($lessons as $lesson_url => $lesson_title) {
                        $pars = "action=open_link&q=$lesson_url&username=$username&passwd=$passwd";
                        $links .= "<a href='$url?$pars' target='_blank'> $lesson_title </a> <br><br>";
                    }
                    //$pars = "action=open_link&q=$lesson_url&username=$name $surname&passwd=$passwd";

                    break;

                default:
                    break;
            }
            $links .= "<b><i> RESOURCE LIBRARY:</i> </b> <br> <a href='http://172.16.0.3/#content_Resource~~~Library' target='_blank'> TYB </a> <br><br>";
            return $links;
        } else {
            return $links;
        }
//    } else {
//        return "";
//    }
//    $contents = do_post_request($url,$pars);
//    //echo "RESPONSE $contents PARA $pars<br>\n";
//    $remote_response = json_decode($contents, TRUE);
    
}

// create timetable row
function print_day($day, $row,$time_table_id,$user_type, $user_id, $year_id, $school_id) {
	global $rs;
        //global $user_id;
        global $timetable_id;
        //echo "SCHOOL id = $school_id<br/>";
	// if $rs is null than query database (this should be only first time)
	//var_dump($rs);
	if ($rs === null) {
                $time_table_id = prepareTableView($user_id,$time_table_id,$user_type, $year_id);
                
                
//                $dir = "/var/www/html/school-lms/prod/prod/main/home";
//                $base_url = "http://www.schoollms.net";
//                chdir($dir);
//                //$base_url = 'http://url/to/drupal/root';
//                require_once './includes/bootstrap.inc';
//                define('DRUPAL_ROOT', getcwd());
//                drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
//
//                global $user;
                
		// first column of the query is used as key in returned array
		$rs = sqlQuery("select concat(tbl_row,'_',tbl_col) as pos, timetabl_id, slot_code, slot_details
						from schoollms_schema_userdata_school_timetable_view
						where timetabl_id = $time_table_id");
		//unset($rs);				
	}
	
        //GET PERIOD TIMES PER WEEKDAY
        //$day_string = "<b>Day $timetable_day</b><br>$day_items[0]<br>$day_items[1]-$month_items[0]-$year";
        $day_tokens = explode("<br>", $day);
        $weekday = $day_tokens[1];
        $period_labels = sqlQuery('select *  from schoollms_schema_userdata_school_timetable_period_labels order by period_label_id');
        
        $weekday_tokens = sqlQuery("SELECT * FROM schoollms_schema_userdata_school_timetable_weekdays WHERE week_day_label LIKE '%$weekday%' ");
        $week_day_id = $weekday_tokens[0][0][0];
        
        $periods = sqlQuery("SELECT * FROM schoollms_schema_userdata_school_timetable_period WHERE school_id = $school_id AND week_day_id = $week_day_id ORDER BY period_label_id");
	
	//var_dump($periods);
	print '<tr>';
	print '<td class="mark dark">' . $day . '</td>';
	// column loop starts from 1 because column 0 is for hours
	$result = "";
	for ($col=1; $col <= 8; $col++) {
		// create table cell
		print '<td>';
		// prepare position key in the same way as the array key looks
		$pos = $row . '_' . $col;
		// if content for the current position exists
		
		if (array_key_exists($pos, $rs)) {
			// prepare elements for defined position (it could be more than one element per table cell)
			$elements = $rs[$pos];
		
			// open loop for each element in table cell
			for ($i=0; $i < count($elements); $i++) {
				// id of DIV element will start with sub_id and followed with 'b' (because cloned elements on the page have 'c') and with tbl_id
				// this way content from the database will not be in collision with new content dragged from the left table and each id stays unique
				$id = $elements[$i][2] . 'b' . $elements[$i][1];
				$name = $elements[$i][3];
				$param = str_replace("\r","",$elements[$i][3]);
				$datas = explode("<br>",$param);
				$result="";
                                $display = "";
				if(count($datas) == 4)
				{
					$result.= "<table border=1 id=tblpopup name=tblpopup>";
					//$result.= "<tr><td>Subject</td><td>$data[0]</td></tr>";
					$result.= "<tr><td id=subjectColumn name=subjectColumn>Subject</td><td>:SUBJECT</td></tr>";
                                        
                                        switch ($user_type){
                                            case 1:
                                            case 2:
                                            case 3:
                                            case 5:
                                            case 6:
                                                $result.= "<tr><td id=teacherColumn name=teacherColumn>Teacher</td><td>$datas[1]</td></tr>";
                                                $result.= "<tr><td id=classroomColumn name=classroomColumn>Classroom</td><td>$datas[2]</td></tr>";
                                                $result.= "<tr><td id=subsColumn name=subsColumn>Substitutes</td><td>$datas[3]</td></tr>";
                                                break;
                                            
                                            case 4:
                                                $result.= "<tr><td id=classroomColumn name=classroomColumn>Classroom</td><td>$datas[2]</td></tr>";
                                                $result.= "<tr><td id=subsClassColumn name=subsClassColumn>Class</td><td>$datas[3]</td></tr>";
                                                break;
                                            
                                            
                                        }
					
					
					$result.= "<tr><td id=dayColumn name=dayColumn>Day</td><td>$day</td></tr>";
					//echo $i;
					//var_dump($periods[1][0][1]);
					$start = $periods[$col][0][3];
					$end = $periods[$col][0][4];
					$result.= "<tr><td>Period Time</td><td>$start - $end </td></tr>";
					$result.= "</table>";
                                        echo "RESULT $result <br> USER_TYPE $user_type <br>";
				}
                                
                                $class = $elements[$i][2];
                                $start = $periods[$col][0][4];
				$end = $periods[$col][0][5];
                                
				//$class = substr($id, 0, 2); // class name is only first 2 letters from ID
				//print "<div id=\"$id\" class=\"drag $class\"  onclick=\"viewTimeTableSlot('$result','$datas[0]')\">$name</div>";
				$dd = explode("CLASS",$name);
				$num = count($dd);
				//var_dump($dd) ;
				$class1 = "NOCLASS";
				if(count($dd) ==2)
				{
					//$class1 = "CLASS ".str_replace("<br>","",trim($dd[1]));
					$class1 = str_replace("<br>","",trim($dd[1]));
					$class1 = str_replace("\n","",$class1 );
					$class1 = str_replace("\r","",$class1 );
                                        
				}
                //echo strlen ($class1);
				$dclass = "";
				for($i = 0;$i < strlen ($class1);$i++)
				{
                                        if ($class1[$i] == '<'){
                                            break;
                                        }
                                        
					if(!empty(trim($class1[$i])))
					{
						//echo $class1[$i]."<br/>";
						$dclass .=$class1[$i];
					}
				}
                                //$dclass_tokens = explode("<", $dclass1);
                                //$dclass = $dclass_tokens[0];
                                //echo "$class1 <br>\n";
				//$onclick = "viewTimeTableSlot('$result','$datas[0]','$class1','$start-$end~$day')";
				//onclick=\"viewTimeTableSlot('$result','$datas[0]','$class1','$start-$end~$day')\"
				//echo "$onclick  <br/>";
				//echo "into endtehrswehhbfdsmhfj$class1"."gsdhjgfhsdmjfhdsjhfmdshgfhdsgfnhsgdfhngsdnfhgsdnfhvdsnbvfbsndbvfnsdbvfnsdbvfnsdbvfnsdbvfnbsdvfndsbvfndsvbn <br />";
				print "<div id=\"$id\" class=\"drag $class\" onClick=\"viewTimeTableSlot('$result','$datas[0]','CLASS $dclass','$start-$end~$day')\" >$name</div>";
			}
		}
		// close table cell
		print '</td>';
	}
	print "</tr>\n";
        
        //echo "$result";
}

// create timetable row
function timetable($hour, $row) {
	global $rs;
	// if $rs is null than query database (this should be only first time)
	if ($rs === null) {
		// first column of the query is used as key in returned array
		$rs = sqlQuery("select concat(t.tbl_row,'_',t.tbl_col) as pos, t.tbl_id, t.sub_id, s.sub_name
						from redips_timetable t, redips_subject s
						where t.sub_id = s.sub_id");
	}
	print '<tr>';
	print '<td class="mark dark">' . $hour . '</td>';
	// column loop starts from 1 because column 0 is for hours
	for ($col=1; $col <= 8; $col++) {
		// create table cell
		print '<td>';
		// prepare position key in the same way as the array key looks
		$pos = $row . '_' . $col;
		// if content for the current position exists
		if (array_key_exists($pos, $rs)) {
			// prepare elements for defined position (it could be more than one element per table cell)
			$elements = $rs[$pos];
			// open loop for each element in table cell
			for ($i=0; $i < count($elements); $i++) {
				// id of DIV element will start with sub_id and followed with 'b' (because cloned elements on the page have 'c') and with tbl_id
				// this way content from the database will not be in collision with new content dragged from the left table and each id stays unique
				$id = $elements[$i][2] . 'b' . $elements[$i][1];
				$name = $elements[$i][3];
				$class = substr($id, 0, 2); // class name is only first 2 letters from ID
				print "<div id=\"$id\" class=\"drag $class\">$name</div>";
			}
		}
		// close table cell
		print '</td>';
	}
	print "</tr>\n";
}

?>
