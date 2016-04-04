<?php

include("db.inc");
include("util.php");
//echo "HERE";

//Get ALL SCHOOLS

extract($_POST);
extract($_GET);

if (isset($save_user)){
    
    $time = time();
    
    $key = md5("TIME $time SAVE $save_user SCHOOL $school_id TYPE $type_id ACCESS $access_id NAME $name SURNAME $surname");
    
    //echo "KEY $key";
    
    //ADD USER
    
    //ADD DEVICE KEY
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $client_ip = $_SERVER['REMOTE_ADDR'];
    
    $user_os =  getOS($user_agent);
    
    //$user_browser   =   getBrowser();
    if (strpos($user_os, "Win")){
        $device_key_path = "C:\SchoolLMS\device_register.txt";
        $localhost = "127.0.0.1:8083";
    } else {
        $device_key_path = "/opt/.schoollms_device_register.txt";
        $localhost = "127.0.0.1";
    }
    
    $file_opened = TRUE;
    $keyfile = fopen("$device_key_path", "r") or $file_opened = FALSE;
    
    if ($file_opened){
        fwrite($keyfile, $key);
        fclose($keyfile);
        
        if ($client_ip = '127.0.0.1'){
            $q = "INSERT INTO schoollms_schema_userdata_access_profile (NULL, $school_id, $type_id, '$access_id', '$name', '$surname')";
            $data->execSQL($q);
            
            $q = "SELECT * FROM schoollms_schema_userdata_access_profile WHERE school_id = $school_id AND type_id = $type_id AND access_id = '$access_id' AND name = '$name' AND surname = '$surname'";
            $data->execSQL($q);
            $user_id = 0;
            if ($row = $data->getRow()){
                $user_id = $row->user_id;
                
                $q = "INSERT INTO schoollms_schema_securitydata_offline_access_register";
            }
            
            
        } else {
            alert("USER DEVICE REGISTRATION CAN ONLY HAPPEN OFFLINE");
        }
        $data->execSQL($q);
        
        redirect("http://$localhost/vas/timetable/");
    } else {
        alert("KEY FILE - FAILED - CLICK TO RETRY");
        redirect("http://$localhost/vas/timetable/register.php");
    }
} else {
    
    $form = "<html><head><title>SchoolLMS Device Register </title></head><body>";
    $form .= "<fieldset>";
    $form .= "<legend>User and Device Registration:</legend>";
    $form .= "<form>";
    $form .= "<center>SELECT YOUR SCHOOL <br><select name=\"school_id\" id=\"schools\">";
    
    $q = "SELECT * FROM schoollms_schema_userdata_schools";

    $data->execSQL($q);

    //$schools = array ();
    $form .= "<option value=0>Please select your school</option>";
    while ($row = $data->getRow()){
        $school_id = $row->school_id;
        $school_name = $row->school_name;
        $school_name = str_replace("^", " ", $school_name);
         $form .= "<option value='$school_id'>$school_name</option>";  
        //$schools[$school_id] = "$school_name";
    }
    $form .= "</select> <br><br>";

    //GET ALL ROLES

    $q = "SELECT * FROM schoollms_schema_userdata_user_type";

    $data->execSQL($q);

    $form .= "SELECT YOUR ROLE <br><select name=\"type_id\" id=\"types\">";
    
    $form .= "<option value=0>Please select your role</option>";
    while ($row = $data->getRow()){
        $type_id = $row->type_id;
        $type_title = $row->type_title;
        $form .= "<option value='$type_id'>$type_title</option>";
     
    }
    
    $form .= "</select><br><br>";
    
    $form .= "TYPE YOUR IDENTITY <br><input type=\"text\" id=\"access_id\" name=\"access_id\"  placeholder=\"South African ID or Passport\" /><br><br>";
    $form .= "TYPE YOUR NAME <br><input type=\"text\" id=\"name\" name=\"name\"  placeholder=\"Name\" /><br><br>";
    $form .= "TYPE YOUR SURNAME <br><input type=\"text\" id=\"surname\" name=\"surname\"  placeholder=\"Surname\" /><br><br>";
    $form .= "<input type=\"submit\" value=\"Submit\">";
    $form .= "<input type=\"hidden\" name=\"save_user\" value=\"SAVE\">";
    $form .= "</center></form></fieldset><body>";
    
    echo "$form";
    
    
}

?>
