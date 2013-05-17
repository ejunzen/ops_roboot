<?php
require_once ("model/Server.php");
require_once ("model/Command.php");
require_once ("model/Result.php");
require_once ("config.php");
require_once("operateDB.php");
$server = $_GET["server"];
$type = $_GET["type"];

if ($server == null ||$type == null){
	$res = new Result();
	$res->code =  419;
	$res->content = "missing get parameter [server] or [type]!";
	echo json_encode($res);
	exit;
}

/* init database connection */

global $op;
$op= new operateDB($ip,$username,$password1,$database);
$op->init();

/**
 * get server by id
 */
function getServerById( $op,$id){

	$sql = "select * from servers where server_id=".$id;
	$res = $op->getResult($sql);
	$s = new Server();
	while ($row = mysql_fetch_row($res)){
		$s->server_id = $row[0];
		$s->ip = $row[1];
		$s->host = $row[2];
		$s->nickname = $row[3];
	}
	return $s;
}

/**
 * get Command by id
 */
function getCommandById($op,$id){
	$sql = "select * from common_cmd where cmd_type=".$id;
	$res = $op->getResult($sql);
	$c = new Command();
	while($row = mysql_fetch_row($res)){
		$c->cmd_type = $row[0];
                $c->content = $row[1];
                $c->need_sudo = $row[2];
                $c->cmd_desc= $row[3];
	}
	return $c;
}


function getRemoteResult($cmd){
	$handle = popen($cmd,"r");
	$read = fread($handle, 2096);
	pclose($handle);
	return  $read;

}

/* list all server */
if($type=="1"){

	$sql = "select * from servers";
	$res = $op->getResult($sql);
	$srvs = array();
	while ($row = mysql_fetch_row($res)){

		$s = new Server();
		$s->server_id = $row[0];
		$s->ip = $row[1];
		$s->host = $row[2];
		$s->nickname = $row[3];
		array_push($srvs,$s);
	}

	echo (json_encode($srvs));
}

/* list availble commands for a server */
if($type == "2"){
	$sql = "select * from common_cmd";
	$res = $op->getResult($sql);
	$cmds = array();
	while($row = mysql_fetch_row($res)){
		$c = new Command();
		$c->cmd_type = $row[0];
		$c->content = $row[1];
		$c->need_sudo = $row[2];
		$c->cmd_desc= $row[3];
		array_push($cmds,$c);
	}	
	echo (json_encode($cmds));
}
/* run command */
if($type == "3"){
	$plan = $_GET["plan"];
	if ($plan == null){
		$result = new Result();
        	$result->code = 404;
        	$result->content = "missing get parameter [plan].";
		echo json_encode($result);
		exit;
	}	
	$srv = getServerById($op,$server);
	$cmd = getCommandById($op,$plan);
	$ssh_cmd = "ssh -p 2248 ".$srv->nickname." \"".$cmd->content."\"";
	
	$res =  getRemoteResult($ssh_cmd);
	$result = new Result();
	$result->code = 200;
	$result->content = $res;
	echo json_encode($result);
}

?>
