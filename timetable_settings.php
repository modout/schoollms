<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// include config with database definition
include('config_mysql.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta name="author" content="Modise Makhetha"/>
		<meta name="description" content="SchoolLMS Timetable Settings Page"/>
		<meta name="viewport" content="width=device-width, user-scalable=no"/><!-- "position: fixed" fix for Android 2.2+ -->
		<link rel="stylesheet" href="style.css" type="text/css" media="screen"/>
		<script type="text/javascript">
			var redipsURL = '/javascript/drag-and-drop-example-3/';
		</script>
		<!--<script type="text/javascript" src="header.js"></script>-->
		<script type="text/javascript" src="redips-drag-min.js"></script>
		<script type="text/javascript" src="script.js"></script>
                <script type="text/javascript" src="timetable.js"></script>
                <script type="text/javascript" src="jscolor.js"></script>
		<title>SchoolLMS Timetable Settings</title>
	</head>
    <body>
        <div id="main_container">
            <?php
                $school_id = $_REQUEST['school_id']; 
                $settings = timetable_settings($school_id); 
                
                if ($settings['found']){
                    echo '</ br>';
                    timetable_subject_settings($school_id, $settings);
                }
                ?> 
        </div>
        
        
    </body>
</html>
    