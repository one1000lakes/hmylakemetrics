<?php
require 'database.php';
require 'functions.php';

//Example updating of values:
//http://my.webserver.example/metrics_testi/update/update_values.php?api_key=xxxyyyzzz&sender_node=1&tagname=viewid&valuetype=int&value=12512515

//Check api key and exit if not provided or wrong

if(!isset($_GET["api_key"]) || $_GET["api_key"] !== API_KEY){
    mysqli_close($con);
    exit;
}

//Make sure timezone is set right
date_default_timezone_set('UTC');

//Uncomment these lines if updating data is only allowed from specific ip addresses. Only uncomment matching definitions from database.php
//$sender_ip_address = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
//if ($sender_ip_address !== ALLOWED_IP1 && $sender_ip_address !== ALLOWED_IP2) {
//	mysqli_close($con);
//  exit;
//}

//Read passed arguments and escape them

$sender_node_notsafe = htmlspecialchars($_GET["sender_node"]);
$tagname_notsafe = htmlspecialchars($_GET["tagname"]);
$valuetype_notsafe = htmlspecialchars($_GET["valuetype"]);
$value_notsafe = htmlspecialchars($_GET["value"]);

$sender_node_safe = mysqli_real_escape_string($con, $sender_node_notsafe);
$tagname_safe = mysqli_real_escape_string($con, $tagname_notsafe);
$valuetype_safe = mysqli_real_escape_string($con, $valuetype_notsafe);
$value_safe = mysqli_real_escape_string($con, $value_notsafe);

store_values($con, $sender_node_safe, $tagname_safe, $valuetype_safe, $value_safe);

//If tagname is le1000 it's last from the local metrics and calculation is called
if ($tagname_safe == 'le1000') {
	//Delete old from history
	$sql_query = "DELETE FROM metrics_history WHERE timestamp < NOW() - INTERVAL " . HISTORICAL_DAYS . " DAY;";
	$result = mysqli_query($con, $sql_query);

	metric_calculations($con, $sender_node_safe);
	
	mysqli_close($con);
}
else {
	mysqli_close($con);
}

?> 