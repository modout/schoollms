<?php
class SubjectGradeData
{
	var $link;
	var $lesson;
	var $score;
	var $total_time;
	
}

//get token


$url = "https://sphsadmin.schoollms.net/lesson_login.php?user=System Admin&passwd=$0W3t0&q=course/8640/natural-sciences-grade-9-term-1";


$index = strrpos($url,"/");
$index2 = strrpos($url,"&");
$urlpart = substr($url,$index+1,$index2 -$index-1);
//echo $urlpart ;
$searchfor = ucwords(str_replace("-"," ",substr($urlpart,0,strpos(strtolower($urlpart),"term")-1)));
//echo "<br/>$searchfor";
//exit;
$request = do_post_request($url,"");


$tabledata = substr($request,strrpos($request,$searchfor));
$index = strpos(strtolower($tabledata),"<tbody>");
$index2 = strpos(strtolower($tabledata),"</tbody>");

$tbl = substr($tabledata,$index,$index2-$index);
$tbl = str_replace("<tbody>","",$tbl);

$rows = explode("</tr>",$tbl);
$subjectLessons = array();
for($i=0;$i<count($rows);$i++)
{
	$subjectLessons[$i] = new SubjectGradeData();
	$cols = explode("</td><td>",$rows[$i]);
	if(count($cols) == 3)
	{
		$subjectLessons[$i]->lesson = trim(str_replace("<tr class=\"even\"><td>","",str_replace("<tr class=\"odd\"><td>","", str_replace("\n","",$cols[0]))));
		$lesson = $subjectLessons[$i]->lesson;
		$subjectLessons[$i]->score = trim($cols[1]);
		$subjectLessons[$i]->total_time = str_replace("</td>","",$cols[2]);	
		$subjectLessons[$i]->lesson = str_replace("</a>","",str_replace("class=\"\"","",$subjectLessons[$i]->lesson));
		$index = strpos($subjectLessons[$i]->lesson,">");
		$subjectLessons[$i]->link = substr($subjectLessons[$i]->lesson,0,$index);
		//$subjectLessons[$i]->lesson = str_replace($subjectLessons[$i]->link,"",$subjectLessons[$i]->lesson);
		$subjectLessons[$i]->lesson = $lesson;
		$subjectLessons[$i]->link = explode("href=",$subjectLessons[$i]->link);
		$subjectLessons[$i]->link = $subjectLessons[$i]->link[1];
		$subjectLessons[$i]->link = str_replace("\"","",$subjectLessons[$i]->link);
	}
}

echo json_encode($subjectLessons );

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