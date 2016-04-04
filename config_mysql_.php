<?php

// old mysql extension (it has been deprecated as of PHP 5.5.0 and will be removed in the future)
// define database host (99% chance you won't need to change this value)
$db_host = 'localhost';

// define database name, user name and password
$db_name = 'school_lms_dev_support';
$db_user = 'root';
$db_pwd = '12_s5ydw3ll1979';
//$db_pwd = '';
// reset record set to null ($rs is used in timetable function)
$rs = null;
$timetable_items = null;
$user_id = 1;
$timetable_id = 0;
// open database connection and select database
//$link = mysql_connect('localhost', 'root', '');
$db_conn = mysql_connect($db_host, $db_user, $db_pwd);
mysql_select_db($db_name, $db_conn);

// function executes SQL statement and returns result set as Array
function sqlQuery($sql) {
    global $db_conn;
    // execute query	
    $db_result = mysql_query($sql, $db_conn);
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
        while ($row = mysql_fetch_array($db_result, MYSQL_NUM)) {
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
    mysql_query('commit', $db_conn);
    mysql_close($db_conn);
}

//Prepare School Time Table View for user;
function prepareTableView($user_id) {

    global $timetable_id;
    //Get User Details For Table Retrieval
    //Get USER TIMETABLE ID
    $timetable_id = 22;

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

            foreach ($items as $item) {
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

function print_days() {

    $today = date('D:d M:m Y H:i:s');

    //echo "$today";
    //Get TimeDays From Today
    $days = days($today);

    //REMOVE PREVIOUS TIMETABLE LOAD IF FIRST DATE IS DIFFERENT TO TODAY
    //FOR EACH DAY LOAD USER TIMETABLE
    foreach ($days as $key => $day) {

        $timetable_day = $day['timetable_day'];
        $timetable_day_date = $day['timetable_day_date'];
        $tokens = explode(" ", $timetable_day_date);

        $year = $tokens[2];
        $month_items = explode(":", $tokens[1]);
        $day_items = explode(":", $tokens[0]);
        $clock_items = explode(":", $tokens[3]);
        $day_string = "<b>Day $timetable_day</b><br>$day_items[0]<br>$day_items[1]-$month_items[0]-$year";
        //GET DAY ITEMS
        print_day($day_string, $key + 1);
    }
}

// print subjects
function subjects() {
    // returned array is compound of nested arrays
    $subjects = sqlQuery('select subject_id, subject_title from schoollms_schema_userdata_school_subjects order by subject_title');
    // print_r($subjects);
    foreach ($subjects as $subject) {
        $id = $subject[0][0];
        $name = $subject[0][1];
        print "<tr><td class=\"dark\"><div id=\"$id\" class=\"redips-drag redips-clone $id\">$name</div><input id=\"b_$id\" class=\"$id\"type=\"button\" value=\"\" onclick=\"report('$id')\" title=\"Show only $name\"/></td></tr>\n";
    }
}

function periods() {

    $periods = sqlQuery('select *  from schoollms_schema_userdata_school_timetable_period order by period_id');

    foreach ($periods as $period) {

        $id = $period[0][0];
        $start = $period[0][1];
        $label = $period[0][3];
        $end = $period[0][2];

        print "<td class=\"redips-mark dark\"><b>$label </b><br> $start-$end </td>";
    }
}

function days($today) {

    //Get Today Items
    $today_items = explode(" ", $today);

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

function getSchoolDays($start_date_token, $today_token) {
    //Remove weekends and Holidays from $date_1 to $date_2
    $count_days = 1;
    $new_num_days = 0;
    $last_day = $start_date_token;

    //Get Num Days
    $num_days = dateDifference($start_date_token, $today_token, '%a');

    //echo "NUM DAYS $num_days";
    //echo "<br> START DAY $last_day";
    //Weekends
    $weekends = array("Sat", "Sun");

    while ($count_days <= $num_days) {
        $next_day = add_date($last_day, 1, 0, 0);
        $next_day_tokens = explode(" ", $next_day);

        //echo "<br> NEXT DAY $next_day";

        $year = $next_day_tokens[2];
        $month_items = explode(":", $next_day_tokens[1]);
        $day_items = explode(":", $next_day_tokens[0]);
        $clock_items = explode(":", $next_day_tokens[3]);

        //Check Weekends
        if (in_array($day_items[0], $weekends)) {
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

function isHoliday($day) {

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

        if (strcmp($holiday, $day_test_token) == 0) {
            $result = TRUE;
            break;
        }
    }

    return $result;
}

function getDays($today, $today_day, $num_timetable_days) {

    //Weekends
    $weekends = array("Sat", "Sun");

    $days = array();
    $day_store = array();
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
    while ($count_days <= $num_timetable_days) {

        $next_day = add_date($last_day, 1, 0, 0);
        $next_day_tokens = explode(" ", $next_day);

        $year = $next_day_tokens[2];
        $month_items = explode(":", $next_day_tokens[1]);
        $day_items = explode(":", $next_day_tokens[0]);
        $clock_items = explode(":", $next_day_tokens[3]);

        //Check Weekends
        if (in_array($day_items[0], $weekends)) {
            //Do Nothing
        } elseif (isHoliday($next_day)) {//Check Holidays
            //Do Nothing
        } else {
            if ($next_day_day > $num_timetable_days) {
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

function add_date($givendate, $day = 0, $mth = 0, $yr = 0) {
    $cd = strtotime($givendate);
    $newdate = date('D:d M:m Y H:i:s', mktime(date('h', $cd), date('i', $cd), date('s', $cd), date('m', $cd) + $mth, date('d', $cd) + $day, date('Y', $cd) + $yr));
    return $newdate;
}

function getTodayTimeTableDay($num_days, $num_timetable_days) {

    if ($num_days > $num_timetable_days) {
        $today_day = $num_days - $num_timetable_days;
        while ($today_day > $num_timetable_days) {
            $today_day = $today_day - $num_timetable_days;
        }
    } else {
        $today_day = $num_timetable_days - $num_days;
    }

    if ($today_day == 0) {
        $today_day = 1;
    }

    return $today_day;
}

function dateDifference($date_1, $date_2, $differenceFormat = '%a') {
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);

    $interval = date_diff($datetime1, $datetime2);

    return $interval->format($differenceFormat);
}

function items($timetable_id, $day_id, $period_id, $room_id, $subject_id, $teacher_id, $substitute_id) {

    global $timetable_items;

    //Get TimeTable Item Fields (IF NOT initialized)
    if ($timetable_items === null) {

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
                $sql = "select $display_field from $tablename where $select_field = '$teacher_id'";
                $result = sqlQuery($sql);
                foreach ($result as $value) {
                    $teacher = $value[0][0];
                }

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

    return "<b>$subject</b><br>$teacher<br>$room<br>$substitute";
}

// create timetable row
function print_day($day, $row) {
    global $rs;
    global $user_id;
    global $timetable_id;

    // if $rs is null than query database (this should be only first time)
    if ($rs === null) {
        $result = sqlQuery("truncate schoollms_schema_userdata_school_timetable_view");
        prepareTableView($user_id);
        // first column of the query is used as key in returned array
        $rs = sqlQuery("select concat(tbl_row,'_',tbl_col) as pos, timetabl_id, slot_code, slot_details
						from schoollms_schema_userdata_school_timetable_view
						where timetabl_id = $timetable_id");
    }
    print '<tr>';
    print '<td class="mark dark">' . $day . '</td>';
    // column loop starts from 1 because column 0 is for hours
    for ($col = 1; $col <= 8; $col++) {
        // create table cell
        print '<td>';
        // prepare position key in the same way as the array key looks
        $pos = $row . '_' . $col;
        // if content for the current position exists
        if (array_key_exists($pos, $rs)) {
            // prepare elements for defined position (it could be more than one element per table cell)
            $elements = $rs[$pos];
            // open loop for each element in table cell
            for ($i = 0; $i < count($elements); $i++) {
                // id of DIV element will start with sub_id and followed with 'b' (because cloned elements on the page have 'c') and with tbl_id
                // this way content from the database will not be in collision with new content dragged from the left table and each id stays unique
                $id = $elements[$i][2] . 'b' . $elements[$i][1];
                $name = $elements[$i][3];
                $class = $elements[$i][2];
                //$class = substr($id, 0, 2); // class name is only first 2 letters from ID
                print "<div id=\"$id\" class=\"drag $class\">$name</div>";
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
    for ($col = 1; $col <= 8; $col++) {
        // create table cell
        print '<td>';
        // prepare position key in the same way as the array key looks
        $pos = $row . '_' . $col;
        // if content for the current position exists
        if (array_key_exists($pos, $rs)) {
            // prepare elements for defined position (it could be more than one element per table cell)
            $elements = $rs[$pos];
            // open loop for each element in table cell
            for ($i = 0; $i < count($elements); $i++) {
                // id of DIV element will start with sub_id and followed with 'b' (because cloned elements on the page have 'c') and with tbl_id
                // this way content from the database will not be in collision with new content dragged from the left table and each id stays unique
                $id = $elements[$i][2] . 'b' . $elements[$i][1];
                $name = $elements[$i][3];
                $class = substr($id, 0, 2); // class name is only first 2 letters from ID
                print "<div id=\"$id\" class=\"drag $class\" onclick=popupView($pos)>$name</div>";
            }
        }
        // close table cell
        print '</td>';
    }
    print "</tr>\n";
}

function timetable_settings($school_id) {

    //$return_result = array ();
    
    $settings_found = FALSE;
    //Get School Details;
    $q = "select school_name from schoollms_schema_userdata_schools where school_id = $school_id";
    $result = sqlQuery($q);
    foreach ($result as $value) {
        $schoolname = $value[0][0];
    }
    
    //Given a School ID GET SETTINGS
    $settings = timetable_settings_get($school_id, 'general');
    
    $instructions = $settings['found'] ? "" : "The timetable settings are not captured";
    $days = isset($settings['days']) ? $settings['days'] : 0;
    $periods = isset($settings['periods']) ? $settings['periods'] : 0;
    $period_time = isset($settings['period_time']) ? $settings['period_time'] : 0;
    $number_breaks = isset($settings['number_breaks']) ? $settings['number_breaks'] : 0;
    $break_time = isset($settings['break_time']) ? $settings['break_time'] : 0;
    $from_grade = isset($settings['from_grade']) ? $settings['from_grade'] : "R";
    $to_grade = isset($settings['to_grade']) ? $settings['to_grade'] : "12";
    $classletters = isset($settings['classletters']) ? $settings['classletters'] : "A-Z";
  
    print "<form action=\"timetable_settings_save.php\">";
    print "<fieldset>";
    print "<legend>Timetable General Settings:</legend>";
    
    print "<table><tr><th> $instructions </th></tr></table>";
    
    print "<table><tr><th> $schoolname Timetable Settings </th></tr></table>";
    
    print "<table border=1><tr><th> Settings Variable</th><th> Current Value </th> <th> Select New Value </th> </tr>" .
            "<tr><th> Timetable Days </th> <td> $days </td> <td> <select name='days'> <option value=5> 5 </option> <option value=6> 6 </option> <option value=7> 7 </option> <option value=8> 8 </option> <option value=9> 9 </option> <option value=10> 10 </option> </select> days </td> </tr>" .
            " <tr><th> Periods </th> <td> $periods </td> <td> <select name='periods'> <option value=5> 5 </option> <option value=6> 6 </option> <option value=7> 7 </option> <option value=8> 8 </option> <option value=9> 9 </option> <option value=10> 10 </option> </select> </td> </tr>" .
            " <tr><th> Period Time </th> <td> $period_time </td> <td> <select name='period_time'> <option value=30> 30 </option> <option value=35> 35 </option> <option value=40> 40 </option> <option value=45> 45 </option> <option value=50> 50 </option> <option value=55> 55 </option> <option value=60> 60 </option></select> minutes</td> </tr>" .
            " <tr><th> Break Time </th> <td> $break_time </td> <td> <select name='break_time'> <option value=15> 15 </option> <option value=20> 20 </option> <option value=25> 25 </option><option value=30> 30 </option> <option value=35> 35 </option> <option value=40> 40 </option> <option value=45> 45 </option> <option value=50> 50 </option> <option value=55> 55 </option> <option value=60> 60 </option></select> minutes </td> </tr>" .
            " <tr><th> Grades </th> <td> $from_grade to $to_grade </td><td> From <select name='from_grade'> <option value=\"R\"> R </option> <option value=1> 1 </option> <option value=2> 2 </option> <option value=3> 3 </option><option value=4> 4 </option><option value=5> 5 </option><option value=6> 6 </option><option value=7> 7 </option><option value=8> 8 </option><option value=9> 9 </option><option value=10> 10 </option><option value=11> 11 </option><option value=12> 12 </option></select> To <select name='to_grade'> <option value=\"R\"> R </option> <option value=1> 1 </option> <option value=2> 2 </option> <option value=3> 3 </option><option value=4> 4 </option><option value=5> 5 </option><option value=6> 6 </option><option value=7> 7 </option><option value=8> 8 </option><option value=9> 9 </option><option value=10> 10 </option><option value=11> 11 </option><option value=12> 12 </option> </select></td>  </tr>" .
            " <tr><th> Classletters </th> <td> $classletters </td> <td> <input type=\"text\" name=\"classletters\" value=\"Type A-Z or preferred letters separated by comma\" size=60> </td> </tr>" .
            "</table>";
    print "<input type='hidden' name='school_id' value=$school_id>";
    print "<input type=\"submit\" value=\"Save Settings\">";
    print "</fieldset>";
    print "</form>"; 
    
    //    $return_result['found'] = $settings_found;
    //    $return_result['from_grade'] = $from_grade;
    //    $return_result['to_grade'] = $to_grade;
    
    //array_push($return_result, $settings_found);
    
    return $settings;
}

function timetable_settings_get($school_id, $type, $data=null, $more=null){

    $return_result = array ();
    
    $settings_found = FALSE;
    
    switch ($type) {
        
        case 'teacher_subject_settings':
            //$teacher_values = explode("*", $data[0]);
            $teacher_id_tokens = explode("=", $data[0]);
            $teacher_id = $teacher_id_tokens[1]; 
            $teacher_settings_tokens = $data[1];
                
            $teacher_subjects = array ();
            $teacher_subjects['subject_id'] = array ();
            $teacher_subjects['grade_data'] = array ();
            if (strpos($teacher_settings_tokens, "*")){
                $teacher_subject_tokens = explode("*", $teacher_settings_tokens);
                foreach ($test_subject_tokens as $key2 => $subject_values) {
                    $subject_values = explode("%", $subject_values);
                    array_push($teacher_subjects['subject_id'], $subject_values[0]);
                    array_push($teacher_subjects['grade_data'], $subject_values[1]);
                }
                   
            } else {
                //$copy_test_subject_tokens = array ();
                $subject_values = explode("%", $teacher_settings_tokens[0]);
                array_push($teacher_subjects['subject_id'], $subject_values[0]);
                array_push($teacher_subjects['grade_data'], $subject_values[1]);   
            }
            
            if (is_array($more[$teacher_id])){
                array_push($more[$teacher_id], $teacher_subjects);
            } else {
                $more[$teacher_id] = array ();
                array_push($more[$teacher_id], $teacher_subjects);
            }
            
            $return_result = $more;
            
            break;
        
        case 'general':
            $sql = "select settings from schoollms_schema_userdata_timetable_settings where school_id = $school_id";
            $timetable_settings = sqlQuery($sql);

            if (!empty($timetable_settings)) {
                $settings_found = TRUE;
                foreach ($timetable_settings as $value) {
                    $settings = $value[0][0];
                }

                if (strpos($settings, "|")){

                    $settings_tokens = explode("|", $settings);

                    foreach ($settings_tokens as $key => $token) {
                        $token_items = explode("=", $token);
                        switch ($token_items[0]){

                            case 'days':
                                //$days = $token_items[1];
                                $return_result['days'] = $token_items[1];
                                break;

                            case 'periods':
                                //$periods = $token_items[1];
                                $return_result['periods'] = $token_items[1];
                                break;

                            case 'period_time':
                                //$period_time = $token_items[1];
                                $return_result['period_time'] = $token_items[1];
                                break;

                            case 'break_times':
                                //$break_time = $token_items[1];
                                $return_result['break_times'] = $token_items[1];
                                break;
                            
                            case 'class_start_time':
                                //$break_time = $token_items[1];
                                $return_result['class_start_time'] = $token_items[1];
                                break;
                            
                            case 'school_start_time':
                                //$break_time = $token_items[1];
                                $return_result['class_start_time'] = $token_items[1];
                                break;

                            case 'number_of_breaks':
                                //$break_time = $token_items[1];
                                $return_result['number_of_breaks'] = $token_items[1];
                                break;
                            
                            case 'rotation_type':
                                //$break_time = $token_items[1];
                                $return_result['rotation_type'] = $token_items[1];
                                break;
                            
                            case 'from_grade':
                                //$from_grade = $token_items[1];
                                $return_result['from_grade'] = $token_items[1];
                                break;

                            case 'to_grade':
                                //$to_grade = $token_items[1];
                                $return_result['to_grade'] = $token_items[1];
                                break;

                            case 'classletters':
                                //$classletters = $token_items[1];
                                $return_result['classletters'] = $token_items[1];
                                break;

                            default:
                                break;
                        }

                    }
                }
            }

            $return_result['found'] = $settings_found;
            
            break;
        
        case 'subjects':
            $sql = "select * from schoollms_schema_userdata_timetable_subject_settings where school_id = $school_id";
            $timetable_settings = sqlQuery($sql);

            $found = FALSE;
            if (!empty($timetable_settings)){
                $found = TRUE;
                foreach ($timetable_settings as $setting_item) {
                    $id = $setting_item[0][0];
                    $item = $setting_item[0][1];
                    break;
                }
            }

            if (!$found){
                $return_result = "none";
            } else {
                $return_result = $item;
            }
            break;
        
        case 'learner':
            $sql = "select * from schoollms_schema_userdata_timetable_learner_settings where school_id = $school_id";
            $timetable_settings = sqlQuery($sql);

            $found = FALSE;
            if (!empty($timetable_settings)){
                $found = TRUE;
                foreach ($timetable_settings as $setting_item) {
                    $id = $setting_item[0][0];
                    $item = $setting_item[0][1];
                    break;
                }
            }

            if (!$found){
                $return_result = "none";
            } else {
                $return_result = $item;
            }
            break;

        case 'class':
            $sql = "select * from schoollms_schema_userdata_timetable_class_settings where school_id = $school_id";
            $timetable_settings = sqlQuery($sql);

            $found = FALSE;
            if (!empty($timetable_settings)){
                $found = TRUE;
                foreach ($timetable_settings as $setting_item) {
                    $id = $setting_item[0][0];
                    $item = $setting_item[0][1];
                    break;
                }
            }

            if (!$found){
                $return_result = "none";
            } else {
                $return_result = $item;
            }
            break;
            
        default:
            break;
    }
    
    return $return_result;
    
}

function timetable_generate_classlist($school_id){

    $settings = timetable_settings_get($school_id, 'general');
    
    $learner_settings = timetable_settings_get($school_id, 'learner');
    
    
    $classletters = $settings['classletters'];
    
    $from_grade = $settings['from_grade'];
    
    $to_grade = $settings['to_grade'];
    
    if (strcmp($learner_settings, "none") == 0){
        
    } else {
        // Following the requirements
        // --- Max Number of Learners per Class per Grade - grade_max_learners_class
        // --- Class Spread Start and Spread
        // --- (Optional) Potential Score
        // --- (Optional) Learner Average
        if (is_string($from_grade)){
            $count_grade = 0;
            $temp_grade = $from_grade;
        } else {
            $count_grade = $from_grade;
            $temp_grade = $from_grade;
        }
        
        if (strpos($classletters, "-")){
            //A-Z
            $letters = "A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
        } else {
            $letters = $classletters;
        }

        $classes = array ();

        $letter_tokens = explode(",", $letters);

        $class_labels = array ();
        foreach ($letter_tokens as $key => $letter) {
            while ($count_grade <= $to_grade){
                $label = "CLASS $count_grade"."$letter";
                $classes[$label] = array ();
                array_push($class_labels, $label);
                $count_grade++;
                //$temp_grade = $count_grade;
            }    
        }
        $classes_copy = $classes;
        
        $learner_settings_tokens = explode(";", $learner_settings);
        
        foreach ($learner_settings_tokens as $key => $learner_settings_items) {
            $grade_tokens = explode("<", $learner_settings_items);
            $grade_tokens_copy = $grade_tokens;
            $grade_data_tokens = explode("=", $grade_tokens[0]);
            
            $grade_id = $grade_data_tokens[1];
            
            //Get Rules for Adding to Classes
            
            $learner_settings_data_tokens = explode("|", $grade_tokens[1]);
            $learner_settings_data_tokens_copy = $learner_settings_data_tokens;
            foreach ($learner_settings_data_tokens as $index => $items) {
                if (strpos($items, "#")){
                    $item_tokens = explode("#", $items);
                    $item_tokens_copy = $item_tokens;
                    $learners_data = explode("+", $item_tokens[1]);
                    $learners_data_copy = $learners_data;
                    foreach ($learners_data as $entry => $learner_item) {
                        $learner_data_tokens = explode(":", $learner_item);
                        $learner_id_tokens = explode("=", $learner_data_tokens[0]);
                        $user_id = $learner_id_tokens[1];
                        $learner_data_tokens_copy = $learner_data_tokens;
                        $learner_base_tokens = explode(",", $learner_data_tokens[1]);
                        
                        //Using Baseline Data Decide on the Class to Add the Learner
                        
                        //Using Number of Learners Per Class Load Learner in Class
                        foreach ($class_labels as $key => $label) {
                            if (count($classes[$label]) == $number_of_learners){
                                continue;
                            } else {
                                array_push($classes[$label], $user_id);
                         
                                $q = "SELECT class_id FROM schoollms_schema_userdata_school_classes WHERE class_label = '$label'";
                                $result = sqlQuery($q);

                                $found = FALSE;
                                if (!empty($result)){
                                    $found = TRUE;
                                    foreach ($result as $setting_item) {
                                        $id = $setting_item[0][0];
                                        $class_id = $setting_item[0][1];
                                        break;
                                    }
                                }
                                
                                $learner_base_tokens_copy = $learner_base_tokens;
                                foreach ($learner_base_tokens as $key => $base_items) {
                                    $base_tokens = explode("=", $base_items);
                                    
                                    switch ($base_tokens[0]) {
                                        case 'next_grade':
                                            $base_tokens[1] = $grade_id;
                                            $base_items = implode("=", $base_tokens);
                                            
                                            break;
                                        
                                        case 'next_class':
                                            $base_tokens[1] = $class_id;
                                            $base_items = implode("=", $base_tokens);
                                            
                                            break;

                                        default:
                                            break;
                                    }
                                    
                                    $learner_base_tokens_copy[$key] = $base_items;
                                    $learner_data_tokens_copy[1] = implode(",", $learner_base_tokens_copy);
                                    $learner_item_copy = implode(":", $learner_data_tokens_copy);
                                    $learners_data_copy[$entry] = $learner_item_copy;
                                    $item_tokens_copy[1] = implode("+", $learners_data_copy);
                                    $items_copy = implode("#", $item_tokens_copy);
                                    $learner_settings_data_tokens_copy[$index] = $items_copy;
                                    $grade_tokens_copy[1] = implode("|", $learner_settings_data_tokens_copy);
                                    $learner_settings_items_copy = implode("<", $grade_tokens_copy);
                                    $settings_string = $learner_settings_items_copy;
                                    $settings_string = timetable_settings_update($school_id, $settings_string, 'learner', $grade_id);

                                    //echo "AFTER SETTINGS $settings_string <br />";

                                    timetable_settings_save($school_id, $settings_string, 'learner');
                                }
                                break;
                            }
                        }    
                    }
                    
                    //INSERT/UPDATE LEARNER CLASS DATA INTO THE LEARNER SCHOOL DETAILS TABLE
                    foreach ($classes as $label => $learners) {
                        //Check If CLASS EMPTY
                        if (empty($learners)){
                            continue;
                        }
                        
                        $q = "SELECT class_id FROM schoollms_schema_userdata_school_classes WHERE class_label = '$label'";
                        $result = sqlQuery($q);

                        $found = FALSE;
                        if (!empty($result)){
                            $found = TRUE;
                            foreach ($result as $setting_item) {
                                $id = $setting_item[0][0];
                                $class_id = $setting_item[0][1];
                                break;
                            }
                        }
                        
                        foreach ($learners as $key => $user_id) {
                            $q = "SELECT * FROM schoollms_schema_userdata_learner_schooldetails WHERE user_id = $user_id AND school_id = $school_id AND year_id = $year_id";
                            $result = sqlQuery($q);
                            if (!empty($result)){
                                $q = "UPDATE schoollms_schema_userdata_learner_schooldetails SET grade_id = $grade_id, class_id = $class_id WHERE user_id = $user_id AND school_id = $school_id AND year_id = $year_id";
                            } else {
                                $q = "INSERT INTO schoollms_schema_userdata_learner_schooldetails (user_id,school_id,grade_id,class_id,year_id) VALUES ($user_id, $school_id, $grade_id, $class_id, $year_id)";
                            }
                            $result = sqlQuery($q);
                        }
                        
                    }
                 
                } else {
                    $item_tokens = explode("=", $items);
                    
                    switch ($item_tokens[0]) {
                        case 'number_of_learners':
                            $number_of_learners = $item_tokens[1]; 
                            break;

                        case 'year_id':
                            $year_id = $item_tokens[1]; 
                            break;
                        
                        default:
                            break;
                    }
                }
            }
        }
    }     
}

function timetable_generate_timetable($school_id, $year_id, $output_type=null){
    
    //echo "School $school_id YEAR $year_id <br>";
    /* IN ORDER TO GENERATE TIMETABLE SLOTS
     * Given
     * - Timetable Days - Cycle
     * - Timetable Periods per Day
     * - Period Length
     * - Number of Breaks, Times and Length
     * With the above we can generate a timetable slot view in array
     * Foreach day -
     * --- Foreach period -
     * ------ Starting the class_start_time -- remember school_start_time and morning_class_time
     * ------ Set temp_time to class_start_time if not set
     * ------ Add period time to temp_time
     * ------ If new temp_time is less than temp break_time 
     * --------- save period_time
     * -------Otherwise add temp break_time, break_length to new period start time which is set to temp_time and get new break_time if exist
     * 
     * IN ORDER TO LOAD SUBJECTS IN THE TIMETABLE SLOTS PER GRADE PER CLASS - WITH TEACHERS AUTO LOADED AS PER SETTINGS
     * Given
     * - Timetable Slots 
     * - Subject Period Type
     * - Subject Period Times
     * - Per Grade - Notional Time, Periods/Cycle/Class
     * - For Grade 10 - 12 - If Subject is Choice - we must check minimum number of learners and entry average 
     * - ClassLists per Grade - Remember Class lists for Grade 10 - 12 are created using a random distribution of learners irrespective of subject choice
     * - 
     * FOREACH Grade
     * --- FOREACH Class
     * ------ FOREACH Day
     * ---------- FOREACH Period
     * -------------- FOREACH Subject 
     * 1----------------- Check number of teachers
     * 2----------------- Check if there EXISTS P1 in all other classes in D1 with subject
     * 3----------------- IF TRUE
     * 4------------------------- Check for P2:D1, P3:D1,...
     * 5----------------- Until there is an open SLOT
     * 6----------------- Otherwise goto D2, D3, D4,...  
     * 7----------------- Repeat 1 - 4
     * --------- FOREACH Timetable Slot
     * ------------ Find slot where Subject is not Taught in other Classlist Timetable slot and there is at least one teacher available If there is only one teacher for Subject
     * ------------ Otherwise foreach number of teachers save subject to classlist timetable
     * 
     * FOR GRADE 10 - 12 - THE SUBJECTS ARE LOADED PER LEARNER
     * - A combination of subject choices per learner group determines the keys - which enable learners with different subject choices to have one register class
     *  
     */
    
    $settings = timetable_settings_get($school_id, 'general');
    
    $learner_settings = timetable_settings_get($school_id, 'learner');
    
    $teacher_settings = timetable_settings_get($school_id, 'teacher');
    
    $class_settings = timetable_settings_get($school_id, 'class');
    
    $classletters = $settings['classletters'];
    
    $from_grade = $settings['from_grade'];
    
    $to_grade = $settings['to_grade'];
    
    //GENERATE TIMETABLE SLOTS
    $days = $settings['days'];
    
    $periods = $settings['periods'];
    
    $total_periods = $periods;
    $period_time = $settings['period_time'];
    
    $count_days = 1;
    
    $timetable_slots = array ();
    
//    echo "I GET HERE <br>";
    while ($count_days <= $days){
        $timetable_slots[$count_days] = array ();
        $count_periods = 1;
        $temp_time = 0;
        while ($count_periods <= $total_periods){
            $timetable_slots[$count_days][$count_periods] = array ();
            
            if ($temp_time == 0){
                $temp_time = $settings['class_start_time'];
            }
            
            $period_times = timetable_time_calculate('period_times', $temp_time, $settings, $timetable_slots, $count_days, $count_periods);
            
            if ($period_times['break_times'] == 0){
                $timetable_slots[$count_days][$count_periods]['time'] = $period_times['period_times'];
                $timetable_slots[$count_days][$count_periods]['type'] = 'class';
                $temp_time = $period_times['temp_time'];
            } else {
                $key = $period_times['break_key'];
                $timetable_slots[$count_days][$count_periods]["break_time_$key"] = $period_times['break_times'];
                $timetable_slots[$count_days][$count_periods]['type'] = 'break';
                $count_periods++;
                $timetable_slots[$count_days][$count_periods]['time'] = $period_times['period_times'];
                $timetable_slots[$count_days][$count_periods]['type'] = 'class';
                
                $temp_time = $period_times['temp_time'];
            }
            $count_periods++;
        }
        $count_days++;
    }
    
    //print_r($timetable_slots);
    
    //ADDING SUBJECTS TO TIMETABLE SLOTS
    $get_values = timetable_values_get('grade_from_to',  $settings);
    $count_grade = $get_values['count_grade'];
    $temp_grade = $get_values['temp_grade'];
    
    
    //GET CLASSES
    $classes = array ();
    while ($count_grade <= $to_grade){
        $classes[$count_grade] = array ();
        $q = "SELECT class_id FROM schoollms_schema_userdata_school_classes WHERE class_id IN (select distinct(class_id) from schoollms_schema_userdata_learner_schooldetails where school_id = $school_id and grade_id = $count_grade and year_id = $year_id)";
        $result = sqlQuery($q);
        if (!empty($result)){
            foreach ($result as $setting_item) {
                //print_r($setting_item);
                $class_id = $setting_item[0][0];
                //$class_id = $setting_item[0][1];
                //echo "COUNT GRADE  $count_grade ID $id CLASS ID $class_id <br >";
                array_push($classes[$count_grade], $class_id);
                //break;
            }
        }
        $count_grade++;
 
    }
    
    $timetable_slots_copy = $timetable_slots;
    
    $subjects = timetable_settings_get($school_id, 'subjects');
    
    //echo "SUBJECTS $subjects<br>";
    
    
    $subject_tokens = explode(";", $subjects);
//  
    //echo "SUBJECT TOKENS <br>";
    
    //print_r($subject_tokens);
    
    $get_values = timetable_values_get('grade_from_to',  $settings);
    $count_grade = $get_values['count_grade'];
    $temp_grade = $get_values['temp_grade'];


    $timetables = array ();
    $timetables_string = "";
    
    //GENERATE BLANK TIMETABLES
    //FOR EACH GRADE
    while ($count_grade <= $to_grade){
        $timetables[$count_grade] = array ();
        $temp_classes = $classes[$count_grade];
        //FOR EACH CLASS IN GRADE
        foreach ($temp_classes as $id => $class_id){
            $timetables[$count_grade][$class_id] = array ();
            //FOR EACH DAY FOR CLASS
            foreach($timetable_slots as $day => $period_data){
                $timetables[$count_grade][$class_id][$day] = array ();
                //FOR EACH PERIOD ON DAY
                foreach ($period_data as $period => $entries){
                    $timetables[$count_grade][$class_id][$day][$period] = array ();
                    $timetables[$count_grade][$class_id][$day][$period]['subject_id'] = 0;
                    $timetables_string .= "index:grade_id=$count_grade,class_id=$class_id,day_id=$day,period=$period#data:subject_id=0,teacher_id=0,venue_id=0,substitute_id=0|";
                    //print_r($timetables);
                }
            }
        }
        $count_grade++;
        //$temp_grade = $count_grade;
    }
    
    $timetables_string = substr($timetables_string, 0, strlen($timetables_string) - 1);
    
    $timetables_captured = $timetables;

    //LOAD NEW TIMETABLES
    $get_values = timetable_values_get('grade_from_to',  $settings);
    $count_grade = $get_values['count_grade'];
    $temp_grade = $get_values['temp_grade'];
    //FOR EACH GRADE
    while ($count_grade <= $to_grade){
        //$timetables[$count_grade] = array ();
     
        $temp_classes = $classes[$count_grade];
        
        //FOR EACH CLASS IN GRADE
        foreach ($temp_classes as $id => $class_id){
            //$timetables[$count_grade][$class_id] = array ();
            
            //FOR EACH DAY FOR CLASS
            foreach($timetable_slots as $day => $period_data){
                //$timetables[$count_grade][$class_id][$day] = array ();
                
                //FOR EACH PERIOD ON DAY
                foreach ($period_data as $period => $entries){
                    
                    //IF period_type IS NOT Break
                    if (strcmp($entries['type'],'break') == 0){
                        continue;
                    }
                    //$timetables[$count_grade][$class_id][$day][$period] = array ();
                    //$class_settings = timetable_settings_get($school_id, 'class');

                    //GET SUBJECTS FROM TIMETABLE SUBJECT SETTINGS
                    $subject_tokens_copy = $subject_tokens;
                    /* subject_id=1<subject_color=FFFFFF|period_type=random|period_times=even|grade_setting#grade_id=12:grade_subject_color=FFFFFF,notional_time=27.5,period_cycle=10,subject_type=Optional,minimum_learners=50+grade_id=9:grade_subject_color=FFFFFF,notional_time=27.5,period_cycle=10,subject_type=Optional,minimum_learners=50;
                     * subject_id=2<subject_color=FFFFFF|period_type=random|period_times=even|grade_setting#grade_id=12:grade_subject_color=FFFFFF,notional_time=27.5,period_cycle=10,subject_type=Optional,minimum_learners=50;
                     * subject_id=3<subject_color=FFFFFF|period_type=random|period_times=even|grade_setting#grade_id=12:grade_subject_color=FFFFFF,notional_time=27.5,period_cycle=10,subject_type=Optional,minimum_learners=50
                     */
                    //echo "SUBJECT TOKENS $subject_tokens <br>";
                    
                    foreach ($subject_tokens_copy as $key => $subject_data) {
                        //echo "SUBJECT DATA $subject_data <br>";
                        
                        $subject_data_tokens = explode("<", $subject_data);
                      
                        $subject_details = timetable_values_get('subject_details',  $subject_data_tokens[1]);
                       

                        $grade_details_tokens = $subject_details["grade_setting"];
                        
                        //echo "GRADE SETTINGS $grade_details_tokens <br>";
                        
                        $grades_data = explode("+", $grade_details_tokens);
                        $grade_found = FALSE;
                        foreach ($grades_data as $entry => $grade_settings) {

                            $grade_settings_data = explode(":", $grade_settings);

                            $grade_id_tokens = explode("=", $grade_settings_data[0]);

                            //GET SUBJECT TYPE
                            $grade_subject_settings = explode(",", $grade_settings_data[1]);

                            echo "if ($temp_grade == $grade_id_tokens[1]) <br>";
                            if ($temp_grade == $grade_id_tokens[1]){
//
                                echo "TRUE <br>";
                                
                                //print_r($grade_subject_settings);
                                
                                $grade_subject_settings = timetable_values_get('grade_subject_settings',  $grade_subject_settings);

                                $subject_type = $grade_subject_settings["subject_type"];
                                
                                echo "if ( $subject_type == 'Core') <br>";
                                
                                if ($subject_type == "Core"){
                                    //CHECK IF SUBJECT Core OR Optional
                                    $subject_id_tokens = explode("=", $subject_data_tokens[0]);
                                    $subject_id = $subject_id_tokens[0];

                                    //GET NUMBER OF TEACHERS THAT TEACH subject_id IN THIS  grade_id
                                    //$get_values = timetable_values_get('number_of_teachers',  $teacher_settings, $temp_grade);

                                    echo "timetable_find_slot($timetables, $temp_grade, $class_id, $day, $period,  $subject_id, $timetables_string)<br>";
                                    //CHECK IF subject_id IS ASSIGNED TO class_id FOR current day ON current period 
                                    //$timetables = timetable_find_slot($timetables, $temp_grade, $class_id, $day, $period,  $subject_id,$timetables_string); 
                                    //$timetables_captured = $timetables;
                                }
                                break;
//                                        //$grade_found = TRUE
                            }
                        }
                            //}
                        //}
                    }
                }
            }
        }
        $count_grade++;
        $temp_grade = $count_grade;
    }
    
    //Store/Display Time Tables
    if (is_null($output_type)){
        
    } else {
        switch ($output_type){
            
            case 'display':
                return $timetables;
                break;
            
            case 'store':
              
                break;
            default:
                break;
        }
    }
}

function timetable_find_slot($timetables, $temp_grade, $class_id, $day, $period,  $subject_id, $timetables_string){
    $timetables_copy = $timetables;
    
    
    foreach ($timetables_copy[$temp_grade] as $temp_class_id => $temp_slots){
        if ($class_id == $temp_class_id){
            continue;
        } else if ($temp_slots[$day][$period]['subject_id'] == $subject_id){
            $period_available = FALSE;
            foreach ($temp_slots[$day] as $temp_period => $period_data){
                if ($temp_period == $period){
                    continue;
                }
                $timetables = timetable_find_slot($timetables, $temp_grade, $class_id, $day, $temp_period,  $subject_id);
            }
        } else {
            $period_available = TRUE;
        }
    }
    
    if ($period_available){
        $timetables[$temp_grade][$class_id][$day][$period]['subject_id'] = $subject_id;
    } else {
        foreach ($timetables_copy[$temp_grade][$class_id] as $temp_day => $temp_slots){
            if ($temp_day == $day){
                continue;
            }
            $timetables = timetable_find_slot($timetables, $temp_grade, $class_id, $temp_day, $period,  $subject_id);
        }
    }
    
    return $timetables;
}

function timetable_values_get($type,  $settings, $temp_data = null){

    $result = array();
    switch ($type){
        case 'teacher_settings':
            foreach ($settings as $key => $value) {
                if (strpos($value, "#")){
                    $teacher_item_tokens = explode("#", $value);
                } else {
                    $teacher_item_tokens = explode("=", $value);
                }
                $index = $teacher_item_tokens[0];
                $value = $teacher_item_tokens[1];
                $result["$index"] = $value;
            }
            break;
        
        case 'subject_details':
            $subject_details = explode("|", $settings);
            foreach ($subject_details as $index => $value) {
                if (strpos($value, "#")){
                    $subject_item_tokens = explode("#", $value);
                } else {
                    $subject_item_tokens = explode("=", $value);
                }
                $index = $subject_item_tokens[0];
                $value = $subject_item_tokens[1];
                $result["$index"] = $value;
            }
            break;
        
        case 'grade_subject_settings':
            foreach($settings as $key=> $grade_subject_item){
                $grade_subject_item_tokens = explode("=", $grade_subject_item);
                $index = $grade_subject_item_tokens[0];
                $value = $grade_subject_item_tokens[1];
                $result["$index"] = $value;
            }
            break;
        
        case 'number_of_teachers':
            
            break;
        
        case 'grade_from_to':
            $from_grade = $settings['from_grade'];
    
            $to_grade = $settings['to_grade'];
            
            if (is_string($from_grade)){
                $count_grade = 0;
                $temp_grade = $from_grade;
            } else {
                $count_grade = $from_grade;
                $temp_grade = $from_grade;
            }
            
            $result['count_grade'] = $count_grade;
            $result['temp_grade'] = $temp_grade;
            break;
       
    }
    
    return $result;
}


function timetable_validate_selection($school_id, $settings){
    
}

function timetable_recursive_update($type, $school_id, $new_settings, $old_settings){
    
    
    switch ($type) {
        case 'teacher_settings':
            $update_setting = array ();
            $old_settings_copy = $old_settings;
            foreach ($old_settings as $index=>$old_settings_data){
                $temp_subject_id_tokens = explode("=", $old_settings_data['subject_id']);
                $temp_subject_id = $temp_subject_id_tokens[1];
                
                $new_subject_id_data = explode("%", $new_settings);
                $new_subject_id_tokens = explode("=", $new_subject_id_data[0]);
                
                $new_subject_id = $new_subject_id_tokens[1];
                $new_grade_id_items = explode("!", $new_subject_id_data[1]);
                $new_grade_id_tokens = explode("=", $new_grade_id_items[0]);
                $new_grade_id = $new_grade_id_tokens[1];
                
                if ($new_subject_id == $temp_subject_id){
                   //Compare Grade Data
                    $grade_found = FALSE;
                   if (strpos($old_settings_data['grade_data'], '&')){
                       $grade_data_tokens = explode("&", $old_settings_data['grade_data']);
                       $copy_grade_data_tokens = $grade_data_tokens; 
                       foreach ($grade_data_tokens as $key => $grade_items) {
                           $grade_items_tokens = explode("!", $grade_items);
                           $grade_id_items = explode("=", $grade_items_tokens[0]);
                           $old_grade_id = $grade_id_items[1];
                           if ($old_grade_id == $new_grade_id){
                               $grade_found = TRUE;
                                $grade_items_tokens[1] = $new_grade_id_items[1];
                                $copy_grade_data_tokens[$key] = implode("!", $grade_items_tokens);
                                $old_settings_copy[$index]['grade_data'] = implode("&", $copy_grade_data_tokens);
                               break;
                           }
                       }
                       
                       if (!$grade_found){
                           $old_settings_copy[$index]['grade_data'] .= "&$new_grade_id_items[1]";
                       }
                    } else {
                       $grade_data_tokens = explode("!", $old_settings_data['grade_data']);
                       $grade_items = explode("=", $grade_data_tokens[0]);
                       $old_grade_id = $grade_items[1];
                       
                       if ($old_grade_id == $new_grade_id){
                           $grade_found = TRUE;
                          $grade_data_tokens[1] = $new_grade_id_items[1];
                       } else {
                           $grade_data_tokens[1] .= "&".$new_grade_id_items[1];
                       }
                       //$old_settings_data['grade_data'] = implode(";", $grade_data_tokens);
                        $old_settings_copy[$index]['grade_data'] = implode("!", $grade_data_tokens);
                     
                   }
                   
                  
                   break;
                }
                
            }
            
            $temp_string = "";
            foreach ($old_settings_copy as $key => $value) {
                $temp_subject_id = $value['subject_id'];
                $temp_data = $value['grade_data'];
                echo "OLD SETTINGS COPY TEMP SUBJECT ID<br>";
                print_r($temp_subject_id);
                echo "OLD SETTINGS COPY GRADE DATA<br>";
                print_r($temp_data);
                $temp_string = "$temp_subject_id%$temp_data";
                array_push($update_setting, $temp_string);
            }
            $result = implode("*", $update_setting);
            break;

        default:
            break;
    }
    return $result;
}

function timetable_time_calculate($type, $temp_time, $settings, $timetable_slots, $count_days, $count_periods){

    $results = array ();
    switch($type){
        
        case 'period_times':
            //$selectedTime = "9:15";
            $temp_time_tokens = explode(":", $temp_time);
            
            $period_time = $settings['period_time'];
            $end_time = strtotime("+$period_time minutes", strtotime($temp_time));
            $end_time = date('H:i', $end_time);
            $end_time_tokens = explode(":", $end_time);
            
            //Check if Period End Time is Less Than any Break Time
            $break_times = $settings['break_times'];
            
            $break_time_tokens = explode("*", $break_times);
            
            foreach ($break_time_tokens as $key => $temp_break_time) {
                $results['break_times'] = 0;
                $temp_break_time_tokens = explode("!", $temp_break_time);
                $temp_break_tokens = explode(":", $temp_break_time_tokens[1]);
                $key++;
                
                if (isset($timetable_slots[$count_days][$count_periods]["break_time_$key"])){
                    $results['break_times'] = 0;
                    $results['period_times'] = "$temp_time-$end_time";
                    //$results['period_end_time'] = $end_time;
                    $results['temp_time'] = $end_time;
                    continue;
                }
                $results['break_times'] = 0;
                
                //echo "IF ($end_time_tokens[0] <= $temp_break_tokens[0] && $end_time_tokens[1] <= $temp_break_tokens[1]) <br>";
                if ($end_time_tokens[0] <= $temp_break_tokens[0] && $end_time_tokens[1] <= $temp_break_tokens[1]){
                    //echo "TRUE <br>";
                    //echo "if ($end_time_tokens[0] == $temp_break_tokens[0] && $end_time_tokens[1] == $temp_break_tokens[1]) <br>";
                    if ($end_time_tokens[0] == $temp_break_tokens[0] && $end_time_tokens[1] == $temp_break_tokens[1]){
                      //  echo "TRUE <br>";
                        $break_start_time = $temp_break_time_tokens[1];
                        $break_length = $temp_break_time_tokens[2];
                        $break_end_time = strtotime("+$break_length minutes", strtotime($break_start_time));
                        $break_end_time = date('H:i', $break_end_time);
                        $break_end_time_tokens = explode(":", $break_end_time);
                        $results['break_times'] = "$break_start_time-$break_end_time";
                        $temp_time = $break_end_time;
                        $end_time = strtotime("+$period_time minutes", strtotime($temp_time));
                        $end_time = date('H:i', $end_time);
                        $results['period_times'] = "$temp_time-$end_time";
                        //$results['period_end_time'] = $end_time;
                        $results['temp_time'] = $end_time;
                        $results['break_key'] = $key;
                    } else {
                        //echo "TRUE <br>";
                        $results['break_times'] = 0;
                        $results['period_times'] = "$temp_time-$end_time";
                        //$results['period_end_time'] = $end_time;
                        $results['temp_time'] = $end_time;
                    }
                    break;
                //} else if ($settings['number_of_breaks'] == 1){
                } else if ($end_time_tokens[0] <= $temp_break_tokens[0] && $end_time_tokens[1] > $temp_break_tokens[1]){
                    //echo "if ($end_time_tokens[0] <= $temp_break_tokens[0] && $end_time_tokens[1] > $temp_break_tokens[1]) TRUE <br>";
                    $break_start_time = $temp_break_time_tokens[1];
                    $break_length = $temp_break_time_tokens[2];
                    $break_end_time = strtotime("+$break_length minutes", strtotime($break_start_time));
                    $break_end_time = date('H:i', $break_end_time);
                    $break_end_time_tokens = explode(":", $break_end_time);

                    //echo "if ($temp_time_tokens[0] >= $break_end_time_tokens[0]) <br>";
                    if ($temp_time_tokens[0] >= $break_end_time_tokens[0]){
                        //echo "TRUE <br>";
                        //if ($temp_time_tokens[1] >= $break_end_time_tokens[1]){
                            $results['break_times'] = "$break_start_time-$break_end_time";
                            $temp_time = $break_end_time;
                            $end_time = strtotime("+$period_time minutes", strtotime($temp_time));
                            $end_time = date('H:i', $end_time);
                            $results['period_times'] = "$temp_time-$end_time";
                            //$results['period_end_time'] = $end_time;
                            $results['temp_time'] = $end_time;
                            $results['break_key'] = $key;
                            
                        //} else {

                        //}
                    } else {
                        //echo "FALSE <br>";
                        $results['break_times'] = 0;
                        $results['period_times'] = "$temp_time-$end_time";
                        //$results['period_end_time'] = $end_time;
                        $results['temp_time'] = $end_time;
                        //break;
                    }
                    break;
                } else if ($end_time_tokens[0] > $temp_break_tokens[0]){
//                    echo "if ($end_time_tokens[0] > $temp_break_tokens[0]) TRUE <br>";
//                    $break_start_time = $temp_break_time_tokens[1];
//                    $break_length = $temp_break_time_tokens[2];
//                    $break_end_time = strtotime("+$break_length minutes", strtotime($break_start_time));
//                    $break_end_time = date('H:i', $break_end_time);
//                    $break_end_time_tokens = explode(":", $break_end_time);
//                    
//                    echo "if ($temp_time_tokens[0] >= $break_end_time_tokens[0]) <br>";
//                    if ($temp_time_tokens[0] >= $break_end_time_tokens[0]){
//                            echo "TRUE <br>";
//                        //if ($temp_time_tokens[1] >= $break_end_time_tokens[1]){
//                            $results['break_times'] = "$break_start_time-$break_end_time";
//                            $temp_time = $break_end_time;
//                            $end_time = strtotime("+$period_time minutes", strtotime($temp_time));
//                            $end_time = date('H:i', $end_time);
//                            $results['period_times'] = "$temp_time-$end_time";
//                            //$results['period_end_time'] = $end_time;
//                            $results['temp_time'] = $end_time;
//                            $results['break_key'] = $key;
//                            //break;
//                        //} else {
//
//                        //}
//                    } else {
//                        echo "FALSE <br>";
                        $results['break_times'] = 0;
                        $results['period_times'] = "$temp_time-$end_time";
                        //$results['period_end_time'] = $end_time;
                        $results['temp_time'] = $end_time;
                        
//                    }
                    break;
                } else {
                    //echo "if ($end_time_tokens[0] > $temp_break_tokens[0]) ELSE <br> ";
                    //CHECK IF BREAK FOUND
//                    if (isset($timetable_slots[$count_days]['break_time_1'])){
//                        $results['period_times'] = "$temp_time-$end_time";
//                        //$results['period_end_time'] = $end_time;
//                        $results['temp_time'] = $end_time;
//                    } else {
                        //Get Break END TIME
                        $break_start_time = $temp_break_time_tokens[1];
                        $break_length = $temp_break_time_tokens[2];
                        $break_end_time = strtotime("+$break_length minutes", strtotime($break_start_time));
                        $break_end_time = date('H:i', $break_end_time);
                        $break_end_time_tokens = explode(":", $break_end_time);

                        //echo "if ($temp_time_tokens[0] >= $break_end_time_tokens[0]) <br>";
                        if ($temp_time_tokens[0] >= $break_end_time_tokens[0]){
                            //echo "TRUE <br>";
                            //if ($temp_time_tokens[1] >= $break_end_time_tokens[1]){
                                $results['break_times'] = "$break_start_time-$break_end_time";
                                $temp_time = $break_end_time;
                                $end_time = strtotime("+$period_time minutes", strtotime($temp_time));
                                $end_time = date('H:i', $end_time);
                                $results['period_times'] = "$temp_time-$end_time";
                                //$results['period_end_time'] = $end_time;
                                $results['temp_time'] = $end_time;
                                
                            //} else {

                            //}
                        } else {
                            //echo "FALSE <br>";
                            $results['break_times'] = 0;
                            $results['period_times'] = "$temp_time-$end_time";
                            //$results['period_end_time'] = $end_time;
                            $results['temp_time'] = $end_time;
                        }
                        break;
                    //}
//                } else {
//                    
                }
            }
            
            //
            
            break;
        
        default:
            break;
    }
    
    $results['timetable_slots'] = $timetable_slots;
    return $results;
}

function timetable_settings_update($school_id, $settings, $type, $type_id, $more=null){
    
    switch ($type) {

        case 'teacher_update': 
//          $subject_settings = "subject_id=$subject_id%grade_id=$grade_id-number_periods=$number_periods";
//          $teacher_settings = "teacher_settings#user_id=$user_id:$subject_settings,substitute=$substitute";
//          $settings_string = "year_id=$year_id<number_teachers=$number_teachers|$teacher_settings";
            $settings_tokens = explode("<", $settings);
            $test_tokens = explode("|", $settings_tokens[1]);
            
            echo "TYPE ID $type_id <br>";
            
            if (strpos($type_id, "|")){
                $teacher_tokens = explode("|", $type_id);
            } else {
                $year_id = $type_id;
            }
            $test_tokens = timetable_values_get('teacher_settings',  $test_tokens);
            
            $teacher_subject_settings = array ();
            
            $teacher_settings = $test_tokens['teacher_settings'];
            $search_test_tokens = explode(":", $teacher_settings);
            $teacher_subject_data = $search_test_tokens[1];
            $search_test_token = explode("=", $search_test_tokens[0]);
            $teacher_id = $search_test_token[1];
            echo "TEACHER ID $teacher_id <br>";
            
            echo "TEACHER SETTINGS $teacher_settings <br>";
            
            foreach ($teacher_tokens as $key => $value) {
                echo "KEY $key VALUE $value <br />";
                if (strpos($value, '#')){
                    $value_tokens = explode("#", $value);
                    $more .= "$value_tokens[0]#";
                    $search_test_tokens = explode("#", $teacher_tokens[$key]);
                      
                    $test_teacher = array ();
                    $test_subject = array ();
                    echo "FOUND TOKENS $search_test_tokens[1] <br />";
                    if (strpos($search_test_tokens[1], "+")){
                        echo "LOAD TEACHERS <br>";
                        $test_teacher_tokens = explode("+", $search_test_tokens[1]);
                        $copy_test_teacher_tokens = $test_teacher_tokens;
                        foreach ($test_teacher_tokens as $key2 => $teacher_values) {
                            $teacher_values = explode(":", $teacher_values);
                            array_push($test_teacher, $teacher_values[0]);
                            
                            
                            //Get Teacher Subject Settings
                            $teacher_subject_settings = timetable_settings_get($school_id, 'teacher_subject_settings', $teacher_values, $teacher_subject_settings);
                            
                        }
                    } else {
                        $copy_test_teacher_tokens = array ();
                        $teacher_values = explode(":", $search_test_tokens[1]);
                        array_push($copy_test_teacher_tokens, $teacher_values);
                        array_push($test_teacher, $teacher_values[0]);
                        $teacher_subject_settings = timetable_settings_get($school_id, 'teacher_subject_settings', $teacher_values, $teacher_subject_settings);
                    }
                    
                    echo "TEACHERS";
                    print_r($test_teacher);                    
                    $found = FALSE;
                    foreach ($test_teacher as $key2 => $value2) {
                        $value2_tokens = explode("=", $value2);
                        
                        echo "COMPARE $value2_tokens[1] === $teacher_id <br />";
                        if ($value2_tokens[1] == $teacher_id){
                            $found = TRUE;
                            
                            //UPDATE SUBJECTS
                            echo "NEW SETTINGS $teacher_subject_data OLD SETTINGS <br>";
                            print_r($teacher_subject_settings[$teacher_id]);
                            
                            $copy_test_teacher_tokens[$key2] = timetable_recursive_update('teacher_settings', $school_id, $teacher_subject_data, $teacher_subject_settings[$teacher_id]);
                            
                            echo "RESULT RECURSIVE $copy_test_teacher_tokens[$key2] <br>";
                            
                            $settings = implode("+", $copy_test_teacher_tokens);
                            break;
                            //$search_test_token[1];
                        }
                        
                    }
                    
                    if (!$found){
                        $teacher_settings .= "+$search_test_tokens[1]";
                        $settings = $teacher_settings;
                    }
//                    } else {
//                    
//                        foreach ($test_tokens as $key => $value) {
//    //                //echo "KEY $key VALUE $value <br />";
//                            if (strcmp($key, 'teacher_settings') == 0){
                                //$more .= "teacher_settings";
                                $settings = $more.$settings;
//                                break;
//                            } else {
//                                $more .= "$key=$value|";
//                            }
//                        }
//                    }
                    echo "IMPLODE SET $settings <br />";
                   // break;
                
                } else {
                    $entry = $teacher_tokens[$key];
                    //echo "ENTRY $entry  <br />";
                    $more .= "$entry|";
                }
            }
            break;
        
        case 'teacher':
            $settings = timetable_settings_process($school_id, 'schoollms_schema_userdata_timetable_teacher_settings', $settings, $type, $type_id);

            break;
        
        case 'class':
            $settings = timetable_settings_process($school_id, 'schoollms_schema_userdata_timetable_class_settings', $settings, $type, $type_id);
            break;
        
        case 'class_update':
            $settings = timetable_settings_process_update($school_id, $settings, $type, $type_id, $more);
            break;
        
        case 'learner':
            $settings = timetable_settings_process($school_id, 'schoollms_schema_userdata_timetable_learner_settings', $settings, $type, $type_id);
            break;
        
        case 'learner_update':
            $settings = timetable_settings_process_update($school_id, $settings, $type, $type_id, $more);
            break;
        
        case 'grade_update':
            //type_id = subject_color=|period_type=double|period_times=afternoons|grade_setting#grade_id=12:grade_subject_color=ab2567,notional_time=TEST,period_cycle=10,minimum_learners=TEST
            //settings = subject_id=18<subject_color=|period_type=double|period_times=afternoons|grade_setting#grade_id=12:grade_subject_color=ab2567,notional_time=TEST,period_cycle=10,minimum_learners=TEST
            $settings_tokens = explode("<", $settings);
            $test_tokens = explode("|", $settings_tokens[1]);
            $grade_tokens = explode("|", $type_id);
            
            foreach ($grade_tokens as $key => $value) {
                //echo "KEY $key VALUE $value <br />";
                if (strpos($value, '#')){
                    $value_tokens = explode("#", $value);
                    $more .= "$value_tokens[0]#";
                    $search_test_tokens = explode("#", $test_tokens[$key]);
                    $search_test_token = explode(":", $search_test_tokens[1]);
                    $search_test_token = explode("=", $search_test_token[0]);
                    $test_grades = array ();
                    
                    //echo "FOUND TOKENS $value_tokens[1] <br />";
                    if (strpos($value_tokens[1], "+")){
                        $test_grade_tokens = explode("+", $value_tokens[1]);
                        $copy_test_grade_tokens = $test_grade_tokens;
                        foreach ($test_grade_tokens as $key2 => $grade_values) {
                            $grade_values = explode(":", $grade_values);
                            array_push($test_grades, $grade_values[0]);
                        }
                    } else {
                        $copy_test_grade_tokens = array ();
                        $grade_values = explode(":", $value_tokens[1]);
                        array_push($copy_test_grade_tokens, $grade_values);
                        array_push($test_grades, $grade_values[0]);
                    }
                    
                    $found = FALSE;
                    foreach ($test_grades as $key2 => $value2) {
                        $value2_tokens = explode("=", $value2);
                        
                        //echo "COMPARE $value2_tokens[1] === $search_test_token[1] <br />";
                        if ($value2_tokens[1] === $search_test_token[1]){
                            $found = TRUE;
                            $copy_test_grade_tokens[$key2] = $search_test_tokens[1];
                            $settings = implode("+", $copy_test_grade_tokens);
                            break;
                            //$search_test_token[1];
                        }
                        
                    }
                    
                    if (!$found){
                        $value_tokens[1] .= "+$search_test_tokens[1]";
                        $settings = $value_tokens[1];
                    }
                    
                    $settings = $more.$settings;
                    //echo "IMPLODE SET $settings <br />";
                    break;
                
                } else {
                    $entry = $test_tokens[$key];
                    //echo "ENTRY $entry  <br />";
                    $more .= "$entry|";
                }
            }
            break;
        
        case 'subject':
            $sql = "select * from schoollms_schema_userdata_timetable_subject_settings where school_id = $school_id";
            $timetable_settings = sqlQuery($sql);

            $subject_id = $type_id;
            
            if (!empty($timetable_settings)){
                foreach ($timetable_settings as $setting_item) {
                    $id = $setting_item[0][0];
                    $item = $setting_item[0][1];
                    break;
                }
                
                if (strpos($item, ";")){
                    $item_tokens = explode(";", $item);
                } else {
                    $item_tokens = array ();
                    array_push($item_tokens, $item);
                }

                $count_item_tokens = count($item_tokens);
                $item_tokens_copy = $item_tokens;
                $found = FALSE;
                $subject_string_copy = "";
                foreach ($item_tokens as $key => $value) {
                    $value_tokens = explode("<", $value);
                    $subject_string_copy .= $value_tokens[0]."<";
                    $subject_tokens = explode("=", $value_tokens[0]);

                    if ($subject_tokens[1] === $subject_id){
                        $found = TRUE;
                        //echo "UPDATE GRADE SET BEFORE $settings VALUE TOKENS $value_tokens[1] <br />";
                        $settings = timetable_settings_update($school_id, $settings, 'grade_update', $value_tokens[1], $subject_string_copy);
                        //echo "UPDATE GRADE SET AFTER $settings <br />";
                        
                        $item_tokens_copy[$key] = $settings;
                        break;
                    } else {
                        $subject_string_copy = "";
                    }
                }
//                
                if ($count_item_tokens > 1 && $found){
                    $settings = implode(";", $item_tokens_copy);
                } elseif ($count_item_tokens > 1 && !$found) {
                    array_push($item_tokens_copy, $settings);
                    $settings = implode(";", $item_tokens_copy);
                } elseif ($found) {
                    $settings = $item_tokens_copy[0];
                } elseif (!$found) {
                    array_push($item_tokens_copy, $settings);
                    $settings = implode(";", $item_tokens_copy);
                }
                
            }
            break;

        default:
            break;
    }
    
    return $settings;
}

function timetable_settings_process_update($school_id, $settings, $type, $type_id, $more=null){
    
    //type_id = subject_color=|period_type=double|period_times=afternoons|grade_setting#grade_id=12:grade_subject_color=ab2567,notional_time=TEST,period_cycle=10,minimum_learners=TEST
    //settings = subject_id=18<subject_color=|period_type=double|period_times=afternoons|grade_setting#grade_id=12:grade_subject_color=ab2567,notional_time=TEST,period_cycle=10,minimum_learners=TEST
    $settings_tokens = explode("<", $settings);
    $test_tokens = explode("|", $settings_tokens[1]);
    $grade_tokens = explode("|", $type_id);

    foreach ($grade_tokens as $key => $value) {
        //echo "KEY $key VALUE $value <br />";
        if (strpos($value, '#')){
            $value_tokens = explode("#", $value);
            $more .= "$value_tokens[0]#";
            $search_test_tokens = explode("#", $test_tokens[$key]);
            $search_test_token = explode(":", $search_test_tokens[1]);
            $search_test_token = explode("=", $search_test_token[0]);
            $test_grades = array ();

            //echo "FOUND TOKENS $value_tokens[1] <br />";
            if (strpos($value_tokens[1], "+")){
                $test_grade_tokens = explode("+", $value_tokens[1]);
                $copy_test_grade_tokens = $test_grade_tokens;
                foreach ($test_grade_tokens as $key2 => $grade_values) {
                    $grade_values = explode(":", $grade_values);
                    array_push($test_grades, $grade_values[0]);
                }
            } else {
                $copy_test_grade_tokens = array ();
                $grade_values = explode(":", $value_tokens[1]);
                array_push($copy_test_grade_tokens, $grade_values);
                array_push($test_grades, $grade_values[0]);
            }

            $found = FALSE;
            foreach ($test_grades as $key2 => $value2) {
                $value2_tokens = explode("=", $value2);

                //echo "COMPARE $value2_tokens[1] === $search_test_token[1] <br />";
                if ($value2_tokens[1] === $search_test_token[1]){
                    $found = TRUE;
                    $copy_test_grade_tokens[$key2] = $search_test_tokens[1];
                    $settings = implode("+", $copy_test_grade_tokens);
                    break;
                    //$search_test_token[1];
                }

            }

            if (!$found){
                $value_tokens[1] .= "+$search_test_tokens[1]";
                $settings = $value_tokens[1];
            }

            $settings = $more.$settings;
            //echo "IMPLODE SET $settings <br />";
            break;

        } else {
            $entry = $test_tokens[$key];
            //echo "ENTRY $entry  <br />";
            $more .= "$entry|";
        }
    }
    
    return $settings;
}

function timetable_settings_process($school_id, $table, $settings, $type, $type_id){
    
    switch($type){
        
        case 'teacher':
            $process_update = 'teacher_update';
            break;
        
        case 'learner':
            $process_update = 'learner_update';
            break;
        
        case 'subject':
            $process_update = 'subject_update';
            break;
        
        case 'class':
            $process_update = 'class_update';
            break;
        
        default:
            break;
    }
    
    $sql = "select * from $table where school_id = $school_id";
    $timetable_settings = sqlQuery($sql);

    if (!empty($timetable_settings)){
        foreach ($timetable_settings as $setting_item) {
            $id = $setting_item[0][0];
            $item = $setting_item[0][1];
            break;
        }

        if (strpos($item, ";")){
            $item_tokens = explode(";", $item);
        } else {
            $item_tokens = array ();
            array_push($item_tokens, $item);
        }

        $count_item_tokens = count($item_tokens);
        $item_tokens_copy = $item_tokens;
        $found = FALSE;
        $subject_string_copy = "";
        foreach ($item_tokens as $key => $value) {
            $value_tokens = explode("<", $value);
            $subject_string_copy .= $value_tokens[0]."<";
            $subject_tokens = explode("=", $value_tokens[0]);

            if ($subject_tokens[1] == $type_id){
                $found = TRUE;
                echo "UPDATE GRADE SET BEFORE $settings VALUE TOKENS $value_tokens[1] <br />";
                $settings = timetable_settings_update($school_id, $settings, $process_update, $value_tokens[1], $subject_string_copy);
                echo "UPDATE GRADE SET AFTER $settings <br />";

                $item_tokens_copy[$key] = $settings;
                break;
            } else {
                $subject_string_copy = "";
            }
        }
//                
        if ($count_item_tokens > 1 && $found){
            $settings = implode(";", $item_tokens_copy);
        } elseif ($count_item_tokens > 1 && !$found) {
            array_push($item_tokens_copy, $settings);
            $settings = implode(";", $item_tokens_copy);
        } elseif ($found) {
            $settings = $item_tokens_copy[0];
        } elseif (!$found) {
            array_push($item_tokens_copy, $settings);
            $settings = implode(";", $item_tokens_copy);
        }

    }
    
    return $settings;
}

function timetable_settings_save($school_id, $settings, $type){

    switch ($type) {
        case 'general':
            $sql = "select * from schoollms_schema_userdata_timetable_settings where school_id = $school_id";
            $timetable_settings = sqlQuery($sql);

            if (!empty($timetable_settings)) {
                $q = "UPDATE schoollms_schema_userdata_timetable_settings SET settings='$settings' WHERE school_id=$school_id";
                $timetable_settings = sqlQuery($q); 

            } else {
                $q = "INSERT INTO schoollms_schema_userdata_timetable_settings VALUES ($school_id, '$settings')";
                $timetable_settings = sqlQuery($q);
            }
            break;
            
        case 'subject':
            $sql = "select * from schoollms_schema_userdata_timetable_subject_settings where school_id = $school_id";
            $timetable_settings = sqlQuery($sql);

            if (!empty($timetable_settings)) {
                //echo "SET $settings <br />";
                $q = "UPDATE schoollms_schema_userdata_timetable_subject_settings SET subject_settings='$settings' WHERE school_id=$school_id";
                //echo "Q $q <br />";
                $timetable_settings = sqlQuery($q); 

            } else {
                $q = "INSERT INTO schoollms_schema_userdata_timetable_subject_settings VALUES ($school_id, '$settings')";
                $timetable_settings = sqlQuery($q);
            }
            break;

        case 'class':
            $sql = "select * from schoollms_schema_userdata_timetable_class_settings where school_id = $school_id";
            $timetable_settings = sqlQuery($sql);

            if (!empty($timetable_settings)) {
                $q = "UPDATE schoollms_schema_userdata_timetable_class_settings SET settings='$settings' WHERE school_id=$school_id";
                $timetable_settings = sqlQuery($q); 

            } else {
                $q = "INSERT INTO schoollms_schema_userdata_timetable_class_settings VALUES ($school_id, '$settings')";
                $timetable_settings = sqlQuery($q);
            }
            
            timetable_generate_classlist($school_id, $settings);
            break;
        
        case 'learner':
            $sql = "select * from schoollms_schema_userdata_timetable_learner_settings where school_id = $school_id";
            $timetable_settings = sqlQuery($sql);

            if (!empty($timetable_settings)) {
                
                $q = "UPDATE schoollms_schema_userdata_timetable_learner_settings SET settings='$settings' WHERE school_id=$school_id";
                $timetable_settings = sqlQuery($q); 

            } else {
                $q = "INSERT INTO schoollms_schema_userdata_timetable_learner_settings VALUES ($school_id, '$settings')";
                $timetable_settings = sqlQuery($q);
            }
            
            break;
        
        case 'teacher':
            $sql = "select * from schoollms_schema_userdata_timetable_teacher_settings where school_id = $school_id";
            $timetable_settings = sqlQuery($sql);

            if (!empty($timetable_settings)) {
                $q = "UPDATE schoollms_schema_userdata_timetable_teacher_settings SET settings='$settings' WHERE school_id=$school_id";
                $timetable_settings = sqlQuery($q); 

            } else {
                $q = "INSERT INTO schoollms_schema_userdata_timetable_teacher_settings VALUES ($school_id, '$settings')";
                $timetable_settings = sqlQuery($q);
            }
            break;
            
        default:
            break;
    }
    
}


function timetable_subject_settings($school_id, $timetable_settings){
    
    //Get Grades - Looking at Timetable Settings
    $from_grade = $timetable_settings['from_grade'];
    $to_grade = $timetable_settings['to_grade'];
    
    //Create Grade Arrays
    $grades = array ();
    $grade_options = "";
    if ($from_grade === "R"){
        $grade_id = 0;
        $grades[$grade_id] = "$from_grade";
    } else {
        $grades[$grade_id] = $from_grade;
        $grade_id = $from_grade;
    }
    
    $grade_options .= "<option value=$grade_id> $from_grade </option>";
    
    $grade_id++;
    
    while ($grade_id <= $to_grade){
        $grade_options .= "<option value=$grade_id> $grade_id </option>";
        $grades[$grade_id] = $grade_id;
        $grade_id++;
    }
    
    //Get List of Subjects
    $subjects = sqlQuery('select *  from schoollms_schema_userdata_school_subjects order by subject_id');

    $subject_options = "<option value=0> None </option>";
    foreach ($subjects as $subject) {

        $id = $subject[0][0];
        $title = $subject[0][1];
        //$label = $period[0][3];
        //$end = $period[0][2];
        $subject_options .= "<option value=$id> $title </option>";
        //print "<td class=\"redips-mark dark\"><b>$label </b><br> $start-$end </td>";
    }
    //Check If Settings Exist
    //$sql = "select * from schoollms_schema_userdata_timetable_subject_settings where school_id = $school_id";
    //$subject_settings = sqlQuery($sql);

    //if (!empty($subject_settings)) {
        
    //} else {
        
    //}
    
    print "<form action=\"timetable_subject_settings_save.php\">";
    print "<fieldset>";
    print "<legend>Timetable Subject Settings:</legend>";
    print "Select Subject <select name='subject_id' onchange='showSubjectSettings(this.value,$school_id)'> $subject_options </select>";
    
    print "<div id='subject_settings'><b>Subject settings will be displayed here...</b></div>";
    
    //print "<table border=1><tr><th> Settings Variable</th><th> Current Value </th> <th> Select New Value </th> </tr>" .
    //        "<tr><th> Notional Time </th> <td>  </td> <td> <input type=\"text\" name=\"notional_time\" value=\"Type Notional Time per Week in Hours\" size=60> </td> </tr>" .
    //        "<tr><th> Grade Settings </th> <td>  </td> <td> <select name='grade_settings'> $grade_options </select></td> </tr>" .
    //        "</table>"; 
    print "<input type='hidden' name='school_id' value=$school_id>";
    print "<input type=\"submit\" value=\"Save Settings\">";
    print "</fieldset>";
    print "</form>"; 
     //print "<div id='subject_settings'><b>Subject settings will be displayed here...</b></div>";
}

function timetable_class_settings($school_id, $timetable_settings){
    
     $settings = timetable_settings_get($school_id, 'general');
     
    //Get Grades - Looking at Timetable Settings
    $from_grade = $timetable_settings['from_grade'];
    $to_grade = $timetable_settings['to_grade'];
    
    //Create Grade Arrays
    $grades = array ();
    $grade_options = "";
    if ($from_grade === "R"){
        $grade_id = 0;
        $grades[$grade_id] = "$from_grade";
    } else {
        $grades[$grade_id] = $from_grade;
        $grade_id = $from_grade;
    }
    
    $grade_options .= "<option value=$grade_id> $from_grade </option>";
    
    $grade_id++;
    
    while ($grade_id <= $to_grade){
        $grade_options .= "<option value=$grade_id> $grade_id </option>";
        $grades[$grade_id] = $grade_id;
        $grade_id++;
    }
    
    //Get List of Subjects
    $subjects = sqlQuery('select * from schoollms_schema_userdata_school_subjects order by subject_id');

    $subject_options = "<option value=0> None </option>";
    foreach ($subjects as $subject) {

        $id = $subject[0][0];
        $title = $subject[0][1];
        //$label = $period[0][3];
        //$end = $period[0][2];
        $subject_options .= "<option value=$id> $title </option>";
        //print "<td class=\"redips-mark dark\"><b>$label </b><br> $start-$end </td>";
    }
    //Check If Settings Exist
    //$sql = "select * from schoollms_schema_userdata_timetable_subject_settings where school_id = $school_id";
    //$subject_settings = sqlQuery($sql);

    //if (!empty($subject_settings)) {
        
    //} else {
        
    //}
    
    print "<form action=\"timetable_class_settings_save.php\">";
    print "<fieldset>";
    print "<legend>Timetable Class Settings:</legend>";
    print "Select Grade <select name='subject_id' onchange='showClassSettings(this.value,$school_id)'> $grade_options </select>";
    
    print "<div id='subject_settings'><b>Class settings will be displayed here...</b></div>";
    
    //print "<table border=1><tr><th> Settings Variable</th><th> Current Value </th> <th> Select New Value </th> </tr>" .
    //        "<tr><th> Notional Time </th> <td>  </td> <td> <input type=\"text\" name=\"notional_time\" value=\"Type Notional Time per Week in Hours\" size=60> </td> </tr>" .
    //        "<tr><th> Grade Settings </th> <td>  </td> <td> <select name='grade_settings'> $grade_options </select></td> </tr>" .
    //        "</table>"; 
    print "<input type='hidden' name='school_id' value=$school_id>";
    print "<input type=\"submit\" value=\"Save Settings\">";
    print "</fieldset>";
    print "</form>"; 
     //print "<div id='subject_settings'><b>Subject settings will be displayed here...</b></div>";
}