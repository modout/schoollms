<?php 
global $data;
//var_dump($data);

//include('data_db_mysqli.inc');

// old mysql extension (it has been deprecated as of PHP 5.5.0 and will be removed in the future)

// define database host (99% chance you won't need to change this value)
$db_host = 'localhost';

// define database name, user name and password
$db_name = 'school_lms_dev_support';
$db_user = 'root';
//$db_pwd  = '12_s5ydw3ll1979';
$db_pwd  = '12_s5ydw3ll1979';
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
	$db_name = 'school_lms_dev_support';
	$db_user = 'root';
	//$db_pwd  = '12ssydwell';
	$db_pwd  = '12_s5ydw3ll1979';
	$db_host = 'localhost';
	$db_conn = mysqli_connect($db_host, $db_user, $db_pwd,$db_name);
	//mysql_select_db($db_name, $db_conn);
	//global $db_conn;
	// execute query	
	//echo "<br/>$sql<br/>";
	$db_result = mysqli_query($db_conn,$sql );
	// if db_result is null then trigger error
	if ($db_result === null) {
		trigger_error(mysql_errno() . ": " . mysql_error() . "\n");
		exit();
	}
	// prepare result array
	$resultSet = Array();
	// if resulted array isn't true and that is in case of select statement then open loop
	// (insert / delete / update statement will return true on success) 
	if ($db_result !== true) {
		// loop through fetched rows and prepare result set
		while ($row = mysqli_fetch_array($db_result, MYSQL_NUM)) {
			// first column of the fetched row $row[0] is used for array key
			// it could be more elements in one table cell
			$resultSet[$row[0]][] = $row;
		}
	}
	// return result set
	return $resultSet;
}


// commit transaction
function sqlCommit() {
	global $db_conn;
	mysqli_query($db_conn,'commit' );
	mysqli_close($db_conn);
}

//Prepare School Time Table View for user;
function prepareTableView($user_id, $time_table_id){
    
    global $timetable_id;
    //Get User Details For Table Retrieval
    
    //Get USER TIMETABLE ID
    $timetable_id = $time_table_id;        
    
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
        $sql = "select * from schoollms_schema_userdata_school_timetable_items where timetabl_id = $timetable_id and day_id =  $timetable_day order by period_id asc";
        $timetable_items = sqlQuery($sql);
        
        $col = 1;
        $item_count = count($timetable_items);
        //echo "TTITEMS = $item_count SQL $sql <br>";
        
        foreach ($timetable_items as $items) {
            
            foreach ($items as  $item){
                //$item_count = count($items);

                //echo "ITEMS = $item_count<br>";

                $period_id = $item[2];
                $room_id = $item[3];
                $subject_id = $item[4];
                $teacher_id = $item[5];
                $substitute_id = $item[6];

                //echo "TT $timetable_id,D $timetable_day,P $period_id,R $room_id,S $subject_id,T $teacher_id,SUB $substitute_id <br>";
                $slot_details = items($timetable_id, $timetable_day, $period_id, $room_id, $subject_id, $teacher_id, $substitute_id); 

                //$pos = "$row"."_$period_id";
                $sql = "insert into schoollms_schema_userdata_school_timetable_view values ($timetable_id,'$date_stamp',$row,$period_id,'s-$subject_id','$slot_details')";
                $result = sqlQuery($sql);
            }
        }
        
        $row++;
    }
    
}

function print_days($time_table_id = 22, $user_type = 0){
    
    $today = date('D:d M:m Y H:i:s');
	
    
    //echo "today : $today";
    
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
		
        print_day($day_string, $key+1,$time_table_id);
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

function periods (){
    
    $periods = sqlQuery('select *  from schoollms_schema_userdata_school_timetable_period order by period_id');
    
    foreach ($periods as $period) {
    
        $id = $period[0][0];
        $start = $period[0][1];
        $label = $period[0][3];
        $end = $period[0][2];
        
        print "<td class=\"redips-mark dark\"><b>$label </b><br> $start-$end </td>";
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

function items($timetable_id, $day_id, $period_id, $room_id, $subject_id, $teacher_id, $substitute_id){
    
    global $timetable_items;
    
    //Get TimeTable Item Fields (IF NOT initialized)
    if ($timetable_items === null){
    
        $sql = "select * from schoollms_schema_userdata_school_timetable_items_form";
        $timetable_items = sqlQuery($sql);
        
    }
    
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
            
            case 'period_id':
            
                break;
            
            
            case 'room_id':
                
                $sql = "select $display_field from $tablename where $select_field = '$room_id'";
                $result = sqlQuery($sql);
                foreach ($result as $value) {
                    $room = $value[0][0];
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
                    $name = $value[0][3];
                    $surname = $value[0][4];
                }
                $teacher = "$name $surname";

                //Get Teacher Details
                break;
            
            case 'substitude_id':
                $sql = "select $display_field from $tablename where $select_field = '$substitute_id'";
                $result = sqlQuery($sql);
                foreach ($result as $value) {
                    $substitute = $value[0][0];
                }
                break;
            
            default:
                break;
        }
    }
    
    return @"<b>$subject</b><br>$teacher<br>$room<br>$substitute";
   
}

// create timetable row
function print_day($day, $row,$time_table_id) {
	global $rs;
        global $user_id;
        global $timetable_id;
     //echo "id = $timetable_id<br/>";
	// if $rs is null than query database (this should be only first time)
	//var_dump($rs);
	if ($rs === null) {
                $result = sqlQuery("truncate schoollms_schema_userdata_school_timetable_view");
                prepareTableView($user_id,$time_table_id);
		// first column of the query is used as key in returned array
		$rs = sqlQuery("select concat(tbl_row,'_',tbl_col) as pos, timetabl_id, slot_code, slot_details
						from schoollms_schema_userdata_school_timetable_view
						where timetabl_id = $time_table_id");
		//unset($rs);				
	}
	
	$periods = sqlQuery('select *  from schoollms_schema_userdata_school_timetable_period order by period_id');
	//var_dump($periods);
	print '<tr>';
	print '<td class="mark dark">' . $day . '</td>';
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
				$param = str_replace("\r","",$elements[$i][3]);
				$datas = explode("<br>",$param);
				$result="";
				if(count($datas) == 4)
				{
					$result.= "<table border=1 id=tblpopup name=tblpopup>";
					//$result.= "<tr><td>Subject</td><td>$data[0]</td></tr>";
					$result.= "<tr><td id=subjectColumn name=subjectColumn>Subject</td><td>:SUBJECT</td></tr>";
					$result.= "<tr><td id=teacherColumn name=teacherColumn>Teacher</td><td>$datas[1]</td></tr>";
					$result.= "<tr><td id=classroomColumn name=classroomColumn>Classroom</td><td>$datas[2]</td></tr>";
					$result.= "<tr><td id=subsColumn name=subsColumn>Substitutes</td><td>$datas[3]</td></tr>";
					$result.= "<tr><td id=dayColumn name=dayColumn>Day</td><td>$day</td></tr>";
					//echo $i;
					//var_dump($periods[1][0][1]);
					$start = $periods[$col][0][1];
					$end = $periods[$col][0][2];
					$result.= "<tr><td>Period Time</td><td>$start - $end </td></tr>";
					$result.= "</table>";
				}
                $class = $elements[$i][2];
				//$class = substr($id, 0, 2); // class name is only first 2 letters from ID
				print "<div id=\"$id\" class=\"drag $class\"  onclick=\"viewTimeTableSlot('$result','$datas[0]')\">$name</div>";
			}
		}
		// close table cell
		print '</td>';
	}
	print "</tr>\n";
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
