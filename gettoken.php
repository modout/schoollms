<?php

$url = "https://www.schoollms.net/?q=services/session/token";

/*$ch = curl_init();
curl_setopt( $ch,CURLOPT_URL, 'https://www.schoollms.net/?q=services/session/token' );
curl_setopt( $ch,CURLOPT_POST, true );
curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
$result = curl_exec($ch );
echo $result;
*/

echo do_post_request($url,"");



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

?>