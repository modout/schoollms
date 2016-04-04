<?php

//include('data_db_mysqli.inc');
include('config_mysql_learn.php');
include('teacher.php');

//var_dump($_SERVER);

extract($_POST);
if(!isset($action))
{
	extract($_GET);
}

if(!isset($action))
{
	die();
}

if(strtoupper($action) == "GETSCHOOLINFO")
{
	$sql = "select * from schoollms_schema_userdata_schools where school_id = $school_id";
	$data->execSQL($sql);
	$result = array();
	while($row = $data->getRow())
	{
		$result = $row;
	}
	
	echo json_encode($result);
}

if(strtoupper($action) == "GETTEACHER")
{
	$sql = "select * from schoollms_schema_userdata_access_profile
		where
		  user_id = $id";
	$data->execSQL($sql);
	$result = array();
	while($row = $data->getRow())
	{
		$result[] = $row;
	}
	echo json_encode($result);
	
}

if(strtoupper($action) == "GETPERSONNEL")
{
	
	$sql = "select * from schoollms_schema_userdata_access_profile
		where
		  type_id = $type_id and school_id = $school_id";
	//echo $sql;
	$data->execSQL($sql);
	$result = array();
	while($row = $data->getRow())
	{
		$result[] = $row;
	}
	
	echo "{ \"total\": \"".$data->numrows."\",  \"rows\":";
	echo json_encode($result);
	echo "}";
}

if(strtoupper($action) == "GETPERSONNEL2")
{
	//echo "We are here";
	$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
	$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
	$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'access_id';
	$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
	$offset = ($page-1)*$rows;
	
	$sql = "select count(*) num
		from schoollms_schema_userdata_learner_schooldetails sd
		join schoollms_schema_userdata_school_grades sg on sg.grade_id = sd.grade_id
		join schoollms_schema_userdata_school_classes sc on sc.class_id = sd.class_id
		join schoollms_schema_userdata_access_profile ap on ap.user_id = sd.user_id
		where
		  type_id = $type_id and sg.grade_id = $grade_id and sd.year_id = $year_id and ap.school_id = $school_id ";
	//echo $sql;
	$data->execSQL($sql);
	
	$result = array();
	if($row=$data->getRow())
	{
		$result["total"] = $row->num;
	}
	
	$sql = "select ap.*, sg.grade_title, sg.grade_id,sc.class_label, sc.class_id 
		from schoollms_schema_userdata_learner_schooldetails sd
		join schoollms_schema_userdata_school_grades sg on sg.grade_id = sd.grade_id
		join schoollms_schema_userdata_school_classes sc on sc.class_id = sd.class_id
		join schoollms_schema_userdata_access_profile ap on ap.user_id = sd.user_id
		where
		  type_id = $type_id and sg.grade_id = $grade_id and sd.year_id = $year_id  and ap.school_id = $school_id  order by $sort $order limit $offset,$rows";
		  // and sg.grade_id  <= $end_grade_id 
	$data->execSQL($sql);
	//echo $sql;
	$items = array();
	while($row = $data->getRow())
	{
		$items[] = $row;
	}
	$result["rows"] = $items;
	
	//echo "{ \"total\": \"".$data->numrows."\",  \"rows\":";
	echo json_encode($result);
	//echo "}";
}

if(strtoupper($action) == "GETPERSONNEL3")
{
	$sql = "select ap.*, sg.grade_title, sg.grade_id,sc.class_label, sc.class_id 
		from schoollms_schema_userdata_learner_schooldetails sd
		join schoollms_schema_userdata_school_grades sg on sg.grade_id = sd.grade_id
		join schoollms_schema_userdata_school_classes sc on sc.class_id = sd.class_id
		join schoollms_schema_userdata_access_profile ap on ap.user_id = sd.user_id
		where
		  type_id = $type_id and  sd.user_id = $user_id";
	$data->execSQL($sql);
	$result = array();
	if($row= $data->getRow())
	{
		$result = $row;
	}
	echo json_encode($result);
}

if(strtoupper($action) == "GETSCHOOLSETTING")
{
	$sql = "select settings from schoollms_schema_userdata_timetable_settings where school_id = $school_id";
	$data->execSQL($sql);
	$result = array();
	$strresult = "";
	while($row = $data->getRow())
	{
		//echo $row->settings;
		$expl = explode("|",$row->settings);
		for($i = 0;$i<count($expl);$i++)
		{
			
			$result[] = $expl[$i];
			$strresult .= "{\"". str_replace("=","\":\"",$expl[$i])."\"} ,";
		}
	}
	//$strresult = "[".substr($strresult,0,strlen($strresult) -2)."]";
	//echo $strresult;
	//var_dump($result);
	echo json_encode($result);
}

class SubjectSettings{
	var $subject_info;
	var $subject_id;
	var $grade_setting;
}

if(strtoupper($action) == "GETSUBJECTSETTINGS")
{
	$sql = "select * from schoollms_schema_userdata_timetable_subject_settings where school_id = $school_id";
	$data->execSQL($sql);
	$result = array();
	
	while($row = $data->getRow())
	{
		$dat = explode(";",$row->subject_settings);
		//$dat = explode(";","subject_id=11<color=ab2567|period_type=random|period_times=random|grade_setting#grade_id=8:notional_time=Type Notional Time per Week in Hours,period_cycle=5,minimum_learners=Type ideal number of learners required per class;subject_id=18<color=|period_type=random|period_times=random|grade_setting#grade_id=10:grade_subject_color=ab2567,notional_time=Type Notional Time per Week in Hours,period_cycle=5,minimum_learners=Type ideal number of learners required per class;subject_id=15<color=|period_type=random|period_times=random|grade_setting#grade_id=10:grade_subject_color=ab2567,notional_time=Type Notional Time per Week in Hours,period_cycle=5,minimum_learners=Type ideal number of learners required per class");
		for($i = 0; $i < count($dat);$i++)
		{
			//echo "one <br/>";
			$subjectSettngs =new SubjectSettings();
			$subdata = explode("<",$dat[$i]);
			$subjectdata = explode("=", $subdata[0]);
			$subjectSettngs->subject_id =$subjectdata; 
			$sub = explode("#",$subdata[1]);
			$subjectSettngs->subject_info = explode("|",$sub[0]);
			$subjectSettngs->grade_setting = $sub[1];
			if($subject_id == $subjectdata[1])
			{
				$result[] = $subjectSettngs;
			}
		}
		
	}
	
	echo json_encode($result);
}

if(strtoupper($action) == "GETVENUES")
{
	$sql = "select * from schoollms_schema_userdata_school_building_rooms
		where room_type = $type";
	$data->execSQL($sql);
	$result = array();
	
	while($row = $data->getRow())
	{
		$result[] = $row;
	}
	echo json_encode($result);
}

if(strtoupper($action) == "GETCLASSES")
{
	$sql = "select * from schoollms_schema_userdata_school_classes where school_id = $school_id";
	$data->execSQL($sql);
	$result = array();
	
	while($row = $data->getRow())
	{
		$result[] = $row;
	}
	echo json_encode($result);
}


if(strtoupper( $action) == "GETSUBJECTS" )
{
	$sql = "select * from schoollms_schema_userdata_school_subjects";
	$data->execSQL($sql);
	$result = array();
	while($row = $data->getRow())
	{
		//$result[] = $row->subject_title;
		$result[] = $row;
	}
	echo json_encode($result);
}

if(strtoupper( $action) == "TIMETABLESLOT" )
{
	$result= "";
	$sql = "select concat(tbl_row,'_',tbl_col) as pos, timetabl_id, slot_code, slot_details
						from schoollms_schema_userdata_school_timetable_view
						where concat(tbl_row,'_',tbl_col) = '$rowcol' and slot_code = '$slot_code'";
	
	$data->execSQL($sql);
	if($row = $data->getRow())
	{
		$result = $row->slot_details;
	}
	
	$data = explode("<br>",$result);
	if(count($data) == 4)
	{
		echo "<table border=1>";
		echo "<tr><td>Subject</td><td>$data[0]</td></tr>";
		echo "<tr><td>Teacher</td><td>$data[1]</td></tr>";
		echo "<tr><td>Classroom</td><td>$data[2]</td></tr>";
		echo "<tr><td>Substitutes</td><td>$data[3]</td></tr>";
		echo "</table";
	}
	
	echo json_encode($result);	
}

if(strtoupper( $action) == "GETSLOTCLASSLIST" )
{
	$class = str_replace(" ","%",$class);
	$sql = "select access_id, name, surname
   from schoollms_schema_userdata_learner_schooldetails sd
   join schoollms_schema_userdata_access_profile p on p.user_id = sd.user_id
   join schoollms_schema_userdata_school_classes sc on sc.class_id = sd.class_id
   where class_label like '%$class%'";
   $data->execSQL($sql);
	$result = array();
	while($row = $data->getRow())
	{
		$result[] = $row;
	}
	echo json_encode($result);
}

if(strtoupper( $action) == "TIMETABLESELECT" )
{
	$sql = "select *
	from schoollms_schema_userdata_timetable_settings
	where school_id = $school_id";
	//echo $sql;
	$data->execSQL($sql);
	$settings = "";
	if($row=$data->getRow())
	{
		$settings = $row->settings;
	}		
	$settarray =explodetoAssoc($settings);
	
	$sql = "select *, replace(grade_title,'Grade','Class') theclass
		from schoollms_schema_userdata_school_grades
		where grade_id = '$timetable_label'";
	$classname = "";
	$data->execSQL($sql);	
	if($row=$data->getRow())
	{		
		$classname = $row->theclass;
	}		
	//echo $classname;
	
	$set = explode(",",$settarray["classletters"]);
	$in = "(''";
	foreach($set as $value)
	{
		//$val = str_replace(" ","","$classname$value");
		$val = str_replace("<br>","",trim("$classname$value"));
		$val = str_replace("\n","",$val );
		$val = str_replace("\r","",$val );
		$in .= ",'$val'";
	}
	$in .= ")";
	
	
	$sql = "select * from schoollms_schema_userdata_school_timetable where school_id = $school_id ";
	if(isset($timetable_label))
	{
		/*if($timetable_label != 0)
		{
			$sql .= " and timetable_label like '%$timetable_label%'";

		}*/
		$sql .= " and timetable_label in $in";
		$sql .= " order by timetable_label";
	}	
	//echo "$sql";
	//die();
	//$result = array();
	$data->execSQL($sql);
	$result = array();
	while($row = $data->getRow())
	{
		$result[] = $row;
	}
	echo json_encode($result);
}

if(strtoupper($action) == "GETGRADES")
{
	$sql = "select * from schoollms_schema_userdata_school_grades where grade_id >= $from and grade_id <= $to";
	$data->execSQL($sql);
	$result = array();
	while($row = $data->getRow())
	{
		$result[] = $row;
	}
	echo json_encode($result);
}

if(strtoupper($action) == "GETSTUDENTCLASS")
{
	$sql = "SELECT * FROM schoollms_schema_userdata_school_classes where grade_id = $grade_id";
	$data->execSQL($sql);
	$result = array();
	while($row = $data->getRow())
	{
		$result[] = $row;
	}
	echo json_encode($result);
}

if(strtoupper($action) == "GETSTUDENTCLASS2")
{
	if(strtoupper(str_replace(" ","",$letters)) == "A-Z")
	{
		$letters = "A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
	}
	
	$classes = explode(",",$letters);
	$result = "[";
	for($i = 0; $i < count($classes);$i++)
	{
		$result .= "{\"class_id\":\"$classes[$i]\",\"class_label\":\"$classes[$i]\"},";
	}
	$result = substr($result,0,strlen($result) -1);
	$result .= "]";
	//echo json_encode($result);
	echo $result;
}

if(strtoupper($action) == "PRINT_DAYS")
{ 
	

?>
	<table id="table2" style="width:100%">
						<colgroup>
							<col width="50"/>
							<col width="100"/>
							<col width="100"/>
							<col width="100"/>
							<col width="100"/>
							<col width="100"/>
                                                        <col width="100"/>
                                                        <col width="100"/>
                                                        <col width="100"/>
						</colgroup>
						<tbody>
							<tr>
								<!-- if checkbox is checked, clone school subjects to the whole table row  -->
								<td class="redips-mark blank">
									<input id="week" type="checkbox" title="Apply school subjects to the week" checked/>
									<input id="report" type="checkbox" title="Show subject report"/>
								</td>
                                                                <?php periods($school_id) ?>
<!--								<td class="redips-mark dark">Monday</td>
								<td class="redips-mark dark">Tuesday</td>
								<td class="redips-mark dark">Wednesday</td>
								<td class="redips-mark dark">Thursday</td>
								<td class="redips-mark dark">Friday</td>-->
							</tr>
                                                      
								<?php  
								
								print_days($id,$user_type,$user_id,$year_id,$school_id);
								/*if(isset($user_id)){
									print_days($id,$user_type,$user_id,$year_id);
								}
								else{
									print_days($id,$user_type);
								}*/

								 ?>
						</tbody>
					</table>
	
	<?php
}

if(strtoupper($action) == "GETSUBJECTTEACHER")
{
	$sql = "select * from schoollms_schema_userdata_timetable_teacher_settings";
	$data->execSQL($sql);
	$result = array();
	while($row = $data->getRow())
	{
		$result[] = $row;
		//echo "$row->settings<br/><br/>";
		$part1 = explode("<",$row->settings);
		$part1[0] = explode("=",$part1[0]);
		$tchers = explode("+",$part1[1]);
		$teachers = array();
		for($i =0;$i<count($tchers);$i++)
		{
			$teach = new teacher();
			$teach->year_id = $part1[0][1];
			if(strpos($tchers[$i],"#"))
			{
				$expl = explode("#",$tchers[$i]);
				$tchers[$i] = $expl[1];
				
			}
			if(strpos($tchers[$i],"subject_id=$subject_id") === FALSE)continue;
			//echo "$tchers[$i] <br/><br/><br/>";
			/*$res = tokenize($tchers[$i]);
			$subgrade = new subjectGrade();
			$thesubject = new clSubject();
			
			$thesubject->subjectsGrades[] = $subgrade; 
			$teach->subject[] = $thesubject;*/
			$teachers[] = tokenize($tchers[$i],$part1[0][1]);
		}
		echo json_encode($teachers);
		//echo "<br/>";
		//echo "<br/>";
	}
	//echo json_encode($tchers);
	
}

if(strtoupper($action) == "GETCLASSLIST")
{
	$sql = "select *
   from schoollms_schema_userdata_learner_schooldetails sd
   join schoollms_schema_userdata_access_profile p on p.user_id = sd.user_id
   where sd.class_id = $class_id and year_id = case when $year_id = 'All' then year_id else $year_id  end ";
   echo $sql;
   $data->execSQL($sql);
   $result = array();
   while($row = $data->getRow())
   {
	   $result[] = $row;
   }
   
   echo json_encode($result);   
}

if(strtoupper($action) == "GETIMAGE")
{
	$ctype = "image/png";
	switch( $file_extension ) {
		case "gif": $ctype="image/gif"; break;
		case "png": $ctype="image/png"; break;
		case "jpeg":
		case "jpg": $ctype="image/jpeg"; break;
		default:
	}
	header('Content-type: ' . $ctype);
	$sql = "select * 	from schoollms_schema_userdata_user_photo where user_id = '$user_id'";
	$imagedb->execSQL($sql);
	$result = array();
    while($row = $imagedb->getRow())
    {
	   //$result[] = $row->photo;
	   echo base64_decode($row->photo);
	   break;
    } 
	
    echo json_encode($result);
}

if(strtoupper($action) == "GETSTUDENTPARENT")
{
	$sql = "SELECT * FROM schoollms_schema_userdata_learner_parent";
	$data->execSQL($sql);
	$result = array();
	while($row = $data->getRow())
	{
		$result[] = $row;
	}
	echo json_encode($result);
}

if(strtoupper($action) == "GETCLASSTEACHER")
{
	$sql = "select group_concat(DISTINCT i.substitude_id ORDER BY i.substitude_id DESC SEPARATOR ',') substitude_ids,
		name,surname,access_id
		from schoollms_schema_userdata_school_timetable_items i
		join schoollms_schema_userdata_access_profile p on p.user_id = i.teacher_id
		where
		  class_id =(SELECT timetable_type_item_id 
			FROM schoollms_schema_userdata_school_timetable 
			WHERE timetabl_id = $class_id
			AND timetable_type_id = 3
			limit 0,1)
		group by name,surname,access_id";
	$data->execSQL($sql);
	$result = array();
	while($row = $data->getRow())
	{
		$result[] = $row;
	}
	echo json_encode($result);
}
if(strtoupper($action) == "TIMETABLEDAYS")
{
	$sql = "select * from schoollms_schema_userdata_school_timetable_days";
	$data->execSQL($sql);
	$result = array();
	while($row = $data->getRow())
	{
		$result[] = $row;
	}
	echo json_encode($result);
}
if(strtoupper($action) == "GETROOMS")
{
	$sql = "select * from schoollms_schema_userdata_school_building_rooms";
	$data->execSQL($sql);
	$result = array();
	while($row = $data->getRow())
	{
		$result[] = $row;
	}
	echo json_encode($result);
}

if(strtoupper($action) == "GETPERIODS")
{
	$sql = "select * from schoollms_schema_userdata_school_timetable_period
	where sChoOl_Id = $school_id";
	$sql = "select l.* ,period_start, period_end
	from schoollms_schema_userdata_school_timetable_period_labels l
	join schoollms_schema_userdata_school_timetable_period p on p.period_label_id = l.period_label_id and l.school_id = p.school_id
	where l.school_id = $school_id
	and week_day_id = 7
	order by UNIX_TIMESTAMP(concat('2016-01-01',' ',period_start))  asc";
	$data->execSQL($sql);
	$result = array();
	while($row = $data->getRow())
	{
		$result[] = $row;
	}
	echo json_encode($result);
}

if(strtoupper($action) == "GETPERIODLABELS")
{
	$sql = "select * from schoollms_schema_userdata_school_timetable_period_labels where school_id = $school_id";
	$data->execSQL($sql);
	$result = array();
	while($row = $data->getRow())
	{
		$result[] = $row;
	}
	echo json_encode($result);
}

function tokenize($value,$year_id =0)
{
	$tokenizers = array(":","%","!",",");
	//$value = "user_id=1513:subject_id=5%grade_id=8!number_periods=54&grade_id=10!number_periods=18*subject_id=3%grade_id=8!number_periods=54&grade_id=10!number_periods=18,substitute=1";
	$result = array();
	
	$values = explode("*",$value);
	$subjects = array();
	$subjectGrades = array();
	$teach = new teacher();
	for($i=0;$i<count($values);$i++)
	{
		$subjects[$i] = new clSubject();
		$values[$i] = explode("&",$values[$i]);
		for($j=0;$j<count($values[$i]);$j++)
		{
			$subjectGrades[$j] = new subjectGrade();
			$value = $values[$i][$j];
			//echo "Value = ". $value ;
			//echo "<br/>";
			for($z=0;$z<count($tokenizers);$z++)
			{	
				
				//echo "<hr/> $tokenizers[$i] $value <hr/>";
				$res = explode($tokenizers[$z],$value);
				
				//if(count($res) ==2)
				//{
					$data =  explode("=",$res[0]);
					//echo "<hr/>";
					//echo $res[0];
					//echo "<br/>";
					//var_dump($data);
					//echo "<hr/>";
					switch($data[0])
					{
						case "user_id" :
						{
							$teach->user_id = $data[1];
							break;
						}
						case "subject_id" :
						{
							//$subjects[$i] = new clSubject();
							$subjects[$i]->subject_id = $data[1];;
							break;
						}
						
						case "grade_id" :
						{
							$subjectGrades[$j]->grade_id = $data[1];
							break;
						}
						case "number_periods" :
						{
							//var_dump($data);
							//echo "<br/><hr/>";
							$subjectGrades[$j]->number_periods = $data[1];
							break;
						}
						case "substitute" :
						{
							$teach->substitute = $data[1];
							break;
						}
					}
					$result[] = $res[0];
					if(count($res) ==2)
					{
						$value = $res[1];
					}
				
				//}
				//echo "$tokenizers[$i] -- $value<br/>";
				$ii = $i;
			}
			$res = explode($tokenizers[count($tokenizers)-1],$value);
			$dt = explode("=",$res[0]);
			$teach->substitute = $dt[1];
			$subjects[$i]->subjectsGrades[] =$subjectGrades[$j];
		}
		
		$teach->subject[] = $subjects[$i];		
		$teach->year_id = $year_id;
		$sql = "select * from schoollms_schema_userdata_access_profile where user_id = $teach->user_id";
		global $data1;
		$data1->execSQL($sql);
		$result = array();
		
		if($row = $data1->getRow())
		{
			$teach->teacher_name = $row->name ." ".$row->surname;
		}
		return $teach;
	//echo json_encode($teach); 
	//exit;
	}
	
}

function explodetoAssoc($variable,$explodeby = "|",$assocdelimiter= "=") 
{ 
    //echo "$variable is the variable ";
	$list = explode($explodeby,$variable); 
	
	foreach($list as $key=>$value)
	{
		$data = explode($assocdelimiter,$value); 
		$result[$data[0]] = $data[1]; 
	}
	/*$result = array(); 
	for($i=0;$i< count($list);$i++) 
	{ 
		var_dump($list);
		$data = explode(@assocdelimiter,$list[$i]); 
		$result[$data[0]] = $data[1]; 
	} */
	//var_dump($result["classletters"]);
	//echo "<hr/>";
	return $result; 
}




?>