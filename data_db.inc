<?php

class data{
    var $username;
    var $password;
    var $host;
    var $db;
    var $recordset;
    var $numrows;
    var $connection;
    var $affectedrows;
    var $insertid;
    var $mysql_err;
    var $records;
	var $SQL;
	var $exception;
  
  
    function openDB(){        
        /*if(!isset($this->connection) or $this->connection == null){
           $this->connectDB();
           mysql_selectdb($this->db,$this->connection);
        }*/
		
			$this->exception = "";
			$this->connectDB();
			mysql_selectdb($this->db,$this->connection);
		
    }
    
    function closeBD(){
        mysql_close($this->connection);        
    }
        
    function connectDB(){
        if(isset($this->host) and isset($this->username) and isset($this->password)){
           $this->connection = mysql_connect($this->host,$this->username, $this->password);
		   mysql_query("SET NAMES 'utf8'");
			mysql_query("SET CHARACTER SET utf8");
			mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
           //echo "<br/>$this->host,$this->username, $this->password";
           //echo "<br/>".$this->connection." is the connection<br/>";
           //die();
        }
    }
       
    function execSQL($sql){
		
			$this->exception = "";
			$this->SQL = $sql;
			$this->openDB();
			$this->mysql_err = "";
			//$this->recordset = mysql_db_query($this->db,$sql,$this->connection);
			$this->recordset = mysql_query($this->SQL);
			//echo "<br/>$this->SQL<br/>";
			//$this->numrows = mysql_num_rows($this->recordset);
			//var_dump($this->recordset);
			if($this->recordset != false)
			{
				$this->numrows = mysql_num_rows(mysql_query($sql));
			}
			else{
				$this->numrows = 0;
			}
			$this->mysql_err = mysql_error();
			$this->closeBD();
		
    }
    
    function execNonSql($sql){
		
			$this->exception = "";
			$this->openDB();
			 $this->SQL = $sql;
			$this->mysql_err = "";
			//echo $this->SQL;
			//$this->recordset = mysql_db_query($this->db,$sql,$this->connection);
			$this->recordset = mysql_query($sql);
			if(strpos(strtoupper($sql),"INSERT") > -1) $this->insertid  = mysql_insert_id();        
			$this->mysql_err = mysql_error();
			$this->closeBD();
		
    }
    
    function exec_sql($sql, $restype = "object"){
        
			$this->exception = "";
			$this->openDB();
			$result = array();
			$this->numrows = 0;
			
			//echo "$sql<br/>";
			
			$res = mysql_query($sql);
			if($res != false){
			   switch($restype){
				  case "object" :     
						while($row = mysql_fetch_object($res)){
							$result[] = $row;
							$this->numrows++;
						}
						break;
				   case "array" :
						while($row = mysql_fetch_array($res)){
							$result[] = $row;
							$this->numrows++;
						}
						break;
				   case "assoc" :
						while($row = mysql_fetch_assoc($res)){
							$result[] = $row;
							$this->numrows++;
						}
						break;
			   }
			   $this->closeBD();
			  }
			  else{
				$result = false;
			  }
					 
			$this->records =$result;
		
        return $result;
   }
   
   function getRow($type="object",$mysql_num = MYSQL_BOTH)
   {
			//var_dump($this->recordset);
			switch($type)
			{
				case "object":
					if($this->recordset != false)
					{
						$this->exception = "";
						return mysql_fetch_object($this->recordset);
					}
					break;
					
				case "array":
					if($this->recordset != false)
					{
						$this->exception = "";
						return mysql_fetch_array($this->recordset,$mysql_num);
					}
					break;
			}		
		
   }
   
   function getTime()
   {
		$sql = "select now() dtime";
		execSql($sql);
		$result = "";
		if($row = getRow())
		{
			$result = $row->dtime;
		}		
		return $result;
   }
    
}

$data = new data();
$data->username = "root";
$data->password = "$0W3t0";
$data->host = "localhost";
$data->db = "school_lms_dev_support";

$data1 = new data();
$data1->username = "root";
$data1->password = "$0W3t0";
$data1->host = "localhost";
$data1->db = "school_lms_dev_support";


$imagedb = new data();
$imagedb->username = "root";
$imagedb->password = "$0W3t0";
$imagedb->host = "localhost";
$imagedb->db = "school_lms_dev_support";
?>