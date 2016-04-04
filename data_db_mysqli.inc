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
			//mysql_selectdb($this->db,$this->connection);
		
    }
    
    function closeBD(){
        mysqli_close($this->connection);        
    }
        
    function connectDB(){
        if(isset($this->host) and isset($this->username) and isset($this->password)){
		   //echo "Connecting DB $this->host,$this->username, $this->password,$this->db";
           $this->connection = mysqli_connect($this->host,$this->username, $this->password,$this->db) 
			or die(printf("Connect failed: %s\n", mysqli_connect_error())) ;
		   //var_dump($this->connection);
           //echo "<br/>$this->host,$this->username, $this->password";
           //echo "<br/>".$this->connection." is the connection<br/>";
           //die();
        }
    }
       
    function execSQL($sql){
			//echo "sadasdasda ";
			$this->exception = "";
			$this->SQL = $sql;
			$this->openDB();
			$this->mysql_err = "";
			//$this->recordset = mysql_db_query($this->db,$sql,$this->connection);
			
			$this->recordset = mysqli_query($this->connection,$this->SQL);
			if ( false===$this->recordset ) {
			  printf("error: %s\n", mysqli_error($this->connection));
			}
			//var_dump($this->recordset);
			//echo "<br/>$this->SQL<br/>";
			//$this->numrows = mysql_num_rows($this->recordset);
			$this->numrows = mysqli_num_rows($this->recordset);
			$this->mysql_err = mysqli_error($this->connection);
			$this->closeBD();
		
    }
    
    function execNonSql($sql){
		
			$this->exception = "";
			$this->openDB();
			 $this->SQL = $sql;
			$this->mysql_err = "";
			//echo " $sql";
			//var_dump($this->connection);
			//$this->recordset = mysql_db_query($this->db,$sql,$this->connection);
			$this->recordset = mysqli_query($this->connection,$sql);
			//echo "EHE";
			if(strpos(strtoupper($sql),"INSERT") > -1) $this->insertid  = mysqli_insert_id($this->connection);        
			$this->mysql_err = mysqli_error($this->connection);
			$this->closeBD();
		
    }
    
    function exec_sql($sql, $restype = "object"){
        
			$this->exception = "";
			$this->openDB();
			$result = array();
			$this->numrows = 0;
			
			//echo "$sql<br/>";
			
			$res = mysqli_query($this->connection,$sql);
			//echo "RES : ".$res."<br/>";
			if($res != false and $res != null){
			   switch($restype){
				  case "object" :     
						while($row = mysqli_fetch_object($res)){
							$result[] = $row;
							$this->numrows++;
						}
						break;
				   case "array" :
						while($row = mysqli_fetch_array($res)){
							$result[] = $row;
							$this->numrows++;
						}
						break;
				   case "assoc" :
						while($row = mysqli_fetch_assoc($res)){
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
   
   function getRow($type="object",$mysql_num = MYSQLI_BOTH)
   {
		//var_dump($this->recordset);
		switch($type)
		{
			case "object":
				if($this->recordset != false)
				{
					$this->exception = "";
					return mysqli_fetch_object($this->recordset);
				}
				break;
				
			case "array":
				if($this->recordset != false)
				{
					$this->exception = "";
					return mysqli_fetch_array($this->recordset,$mysql_num);
				}
				break;
		}		
		
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