<?php
	$folder = "pages/";
	extract ($_GET);
	if(!isset($pagefile))extract ($_POST);
	if(!isset($pagefile)) $pagefile = "";
	//var_dump($_GET);
	$pagefile;
	if(!isset($page))
	{
		if(isset($currentpage))
		{
			saveData($_GET);
			switch(strtoupper($currentpage))
			{
				case "SCHOOLINFOFORM":
					$pagefile= "$folder"."building.php";
					break;
				case "SCHOOLBUILDING":
					$pagefile= "$folder"."fields.php";
					break;					
			}
		}
		//echo $pagefile;
		//die();
	}
	else{
		$pagefile= "$folder$page".".php";
	}
	
	if(file_exists ($pagefile))
	{
		$myfile = fopen($pagefile, "r") or die("Unable to open file!");
		$html = fread($myfile,filesize($pagefile));
		$html = str_replace(":SCHOOLID",getSchoolID(),$html );
		echo $html;
		fclose($myfile);
		//include_once("$pagefile");
	}
	else{
		include_once("pages/notavailable.php");
	}
	
	
function getSchoolID()
{
	return rand(0, 10) / 10;
}

function saveData($data )
{
	
}

?>