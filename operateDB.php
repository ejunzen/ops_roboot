
<?php

class operateDB {

	public $ip;
	public $username;
	public $password;
	public $database;
	public $con;
	
	function __construct($ip,$username,$password,$database){
		
		require("config.php");
		
		$this->ip = $ip;
		$this->username = $username;
		$this->password = $password1;
		$this->database = $database;
	
	}
	
	function getCon($ip,$username,$password){
		$this->con = mysql_connect($ip,$username,$password);
	}
	
	function selectDB($database){
		mysql_select_db($database,$this->con);
		mysql_query("SET NAMES 'UTF8'",$this->con);
	}
	
	function init(){
		$this->getCon($this->ip,$this->username,$this->password);
		$this->selectDB($this->database);
	}
	
	function execCmd($cmd){		
		
		$result = mysql_query($cmd,$this->con);
		if($result){
			return true;
		}else{
			return false;
		}
//		echo "$cmd";
//		return true;

	}
	
	function getResult($cmd){	
		$res = mysql_query($cmd,$this->con);
		return $res;
	}
	
}

?>
