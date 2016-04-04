<?php

function do_post_request($url, $data, $optional_headers = null)
{
	$params = array('http' => array(
			  'method' => 'POST',
			  'content' => $data
		   ));
	if ($optional_headers !== null) {
		$params['http']['header'] = $optional_headers;
	}
	$ctx = stream_context_create($params);
	$fp = @fopen($url, 'rb', false, $ctx);
	if (!$fp) {
		throw new Exception("Problem with $url, $php_errormsg");
	}
	$response = @stream_get_contents($fp);
	if ($response === false) {
		throw new Exception("Problem reading data from $url, $php_errormsg");
	}
	$response;
	return ($response);
 
}

function redirect($destination){
        echo "<script type=\"text/javascript\">
                   window.location = \"$destination\";
                </script> ";
}

function alert($message)
{
	 echo "<script type=\"text/javascript\">
                   alert(\"$message\");
                </script> ";
}

function goBack()
{
	 echo "<script type=\"text/javascript\">
                    window.history.back();
                </script> ";
}

function getJasonData($params)
{
	//alert($params);
	echo "<script type=\"text/javascript\">
                   getJasonData(\"$params\");
                </script> ";
}

function windowOpen($params)
{
	echo "<script type=\"text/javascript\">
                   getJasonData(\"$params\");
				   window.open($params,'1453910749489','width=700,height=500,toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,left=0,top=0');
                </script> ";
}

function closeWindow()
{
	echo "<script type=\"text/javascript\">
                   window.close();
                </script> ";
}

function getOS($user_agent) { 

    //global $user_agent;

    $os_platform    =   "Unknown OS Platform";

    $os_array       =   array(
                            '/windows nt 10/i'     =>  'Windows 10',
                            '/windows nt 6.3/i'     =>  'Windows 8.1',
                            '/windows nt 6.2/i'     =>  'Windows 8',
                            '/windows nt 6.1/i'     =>  'Windows 7',
                            '/windows nt 6.0/i'     =>  'Windows Vista',
                            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                            '/windows nt 5.1/i'     =>  'Windows XP',
                            '/windows xp/i'         =>  'Windows XP',
                            '/windows nt 5.0/i'     =>  'Windows 2000',
                            '/windows me/i'         =>  'Windows ME',
                            '/win98/i'              =>  'Windows 98',
                            '/win95/i'              =>  'Windows 95',
                            '/win16/i'              =>  'Windows 3.11',
                            '/macintosh|mac os x/i' =>  'Mac OS X',
                            '/mac_powerpc/i'        =>  'Mac OS 9',
                            '/linux/i'              =>  'Linux',
                            '/ubuntu/i'             =>  'Ubuntu',
                            '/iphone/i'             =>  'iPhone',
                            '/ipod/i'               =>  'iPod',
                            '/ipad/i'               =>  'iPad',
                            '/android/i'            =>  'Android',
                            '/blackberry/i'         =>  'BlackBerry',
                            '/webos/i'              =>  'Mobile'
                        );

    foreach ($os_array as $regex => $value) { 

        if (preg_match($regex, $user_agent)) {
            $os_platform    =   $value;
        }

    }   

    return $os_platform;

}

function getBrowser($user_agent) {

    //global $user_agent;

    $browser        =   "Unknown Browser";

    $browser_array  =   array(
                            '/msie/i'       =>  'Internet Explorer',
                            '/firefox/i'    =>  'Firefox',
                            '/safari/i'     =>  'Safari',
                            '/chrome/i'     =>  'Chrome',
                            '/edge/i'       =>  'Edge',
                            '/opera/i'      =>  'Opera',
                            '/netscape/i'   =>  'Netscape',
                            '/maxthon/i'    =>  'Maxthon',
                            '/konqueror/i'  =>  'Konqueror',
                            '/mobile/i'     =>  'Handheld Browser'
                        );

    foreach ($browser_array as $regex => $value) { 

        if (preg_match($regex, $user_agent)) {
            $browser    =   $value;
        }

    }

    return $browser;

}

//
//
//
//$device_details =   "<strong>Browser: </strong>".$user_browser."<br /><strong>Operating System: </strong>".$user_os."";
//
//print_r($device_details);
//
//echo("<br /><br /><br />".$_SERVER['HTTP_USER_AGENT']."");
?>