<?php

class teacher
{
	var $user_id;
	var $year_id;
	var $substitute;
	var $teacher_name;
	var $subject = array(); //array of type clSubject	
}

class clSubject
{
	var $subject_id;
	var $subjectsGrades = array(); //array of type subjectGrade
}

class subjectGrade
{
	var $grade_id;
	var $number_periods;	
}

?>