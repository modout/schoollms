<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// include config with database definition
include('config_mysql.php');


$subject_id = $_REQUEST['subject_id'];

//Default Current Settings
$color = "ab2567";
$notional_time = 0;
$period_type = "Random";
$periods_cycle = 0;
$period_times = "Random";

$sql = "select subject_title from schoollms_schema_userdata_school_subjects where subject_id = '$subject_id'";
$result = sqlQuery($sql);
foreach ($result as $value) {
    $subject = $value[0][0];
}
               

//print "<form action=\"timetable_subject_settings_save.php\">";
print "<fieldset>";
print "<legend>$subject Settings:</legend>";
//print "Select Subject <select name='subject_id' onchange=\"showSubjectSettins(this.value)\"> $subject_options </select>";

//print "<div id='subject_settings'><b>Subject settings will be displayed here...</b></div>";

print "<table border=1><tr><th> Settings Variable</th><th> Current Value </th> <th> Select New Value </th> </tr>" .
        "<tr><th> Color </th> <td> <input class=\"jscolor\" value=\"$color\"></td> <td> <input name='color' type='hidden' id='color_value' value=\"$color\"> <button class=\"jscolor {valueElement: 'color_value'}\">Pick a color</button></td> </tr>" .
        "<tr><th> Period Type </th> <td> $period_type </td> <td> <select name='period_type'> <option value='random'> Random </option> <option value='single'> Single </option><option value='double'> Double </option> </select></td> </tr>" .
        "<tr><th> Period Times </th> <td> $period_times </td> <td> <select name='period_times'> <option value='random'> Random </option> <option value='mornings'> Mornings </option><option value='afternoons'> Afternoons </option> <option value='even'> Even </option> </select></td> </tr>" . 
        "<tr><th> Grade Settings </th> <td>  </td> <td> <table border=1> <tr><th> Notional Time </th> <td> $notional_time </td> <td> <input type=\"text\" name=\"notional_time\" value=\"Type Notional Time per Week in Hours\" size=60> </td> </tr><tr><th> Period/Cycle </th> <td> $periods_cycle </td> <td> <select name='periods_cycle'> <option value=5> 5 </option> <option value=6> 6 </option> <option value=7> 7 </option> <option value=8> 8 </option> <option value=9> 9 </option> <option value=10> 10 </option> </select> </td> </tr> <tr><th> Minimum Learners/Class </th> <td> $periods_cycle </td> <td> <input type=\"text\" name=\"minimum_learners\" value=\"Type ideal number of learners required per class\" size=60> </td> </tr></table></td> </tr>" .
        "</table>"; 
//print "<input type='hidden' name='school_id' value=$school_id>";
//print "<input type=\"submit\" value=\"Save Settings\">";
print "</fieldset>";
//print "</form>"; 