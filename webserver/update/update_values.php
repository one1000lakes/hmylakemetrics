<?php
require 'database.php';

//Example updating of values:
//http://my.webserver.example/metrics_testi/update/update_values.php?api_key=xxxyyyzzz&sender_node=1&tagname=viewid&valuetype=int&value=12512515

//Check api key and exit if not provided or wrong

if(!isset($_GET["api_key"]) || $_GET["api_key"] !== API_KEY){
    mysqli_close($con);
    exit;
}

//Read passed arguments and escape them

$sender_node_notsafe = $_GET["sender_node"];
$tagname_notsafe = $_GET["tagname"];
$valuetype_notsafe = $_GET["valuetype"];
$value_notsafe = $_GET["value"];

$sender_node_safe = mysqli_real_escape_string($con, $sender_node_notsafe);
$tagname_safe = mysqli_real_escape_string($con, $tagname_notsafe);
$valuetype_safe = mysqli_real_escape_string($con, $valuetype_notsafe);
$value_safe = mysqli_real_escape_string($con, $value_notsafe);

//Select column by value type
if ($valuetype_safe == 'int') {
    $valuecol = 'valueint';
} elseif ($valuetype_safe == 'float') {
    $valuecol = 'valuefloat';
} elseif ($valuetype_safe == 'string') {
    $valuecol = 'valuestring';
} else {
    echo "Error: Value type not provided";
	mysqli_close($con);
    exit;
}

//Insert to history table
$sql_query = "INSERT INTO metrics_history (timestamp, sender_node, tagname, valuetype, " . $valuecol . ") VALUES (now()," . $sender_node_safe . ",'" . $tagname_safe . "','" . $valuetype_safe . "','" . $value_safe . "');";
$result = mysqli_query($con, $sql_query);

if ($result === TRUE) {
    echo "New history record created successfully <br>";
} else {
    echo "Error in history insert query.";
	//DEBUG: echo "Error: " . $sql_query . "<br>";
}

//Check if records exists in metrics_now table, if exists then update, if not exists, then insert
$sql_query = "SELECT id FROM metrics_now WHERE sender_node = '" . $sender_node_safe . "' AND tagname = '" . $tagname_safe . "';";
$result = mysqli_query($con, $sql_query);

$record_exists = false;
while($row = mysqli_fetch_array($result)) {
	$record_exists = true;
}

if ($record_exists === true) {
    $sql_query = "UPDATE metrics_now SET " . $valuecol . " = '" . $value_safe . "', timestamp = now() WHERE sender_node = " . $sender_node_safe . " AND tagname = '" . $tagname_safe . "';";
} else {
    $sql_query = "INSERT INTO metrics_now (timestamp, sender_node, tagname, valuetype, " . $valuecol . ") VALUES (now()," . $sender_node_safe . ",'" . $tagname_safe . "','" . $valuetype_safe . "','" . $value_safe . "');";
}

$result = mysqli_query($con, $sql_query);

if ($result === TRUE && $record_exists === true) {
    echo "Now record updated successfully <br>";
} elseif ($result === TRUE && $record_exists === false) {
    echo "New now record created successfully <br>";
} else {
    echo "Error in now-table update or insert query.";
	//DEBUG: echo "Error: " . $sql_query . "<br>";
}

//If tagname is le1000 it's last from the local metrics and calculation is called
if ($tagname_safe == 'le1000') {
	//Delete old from history
	$sql_query = "DELETE FROM metrics_history WHERE timestamp < NOW() - INTERVAL " . HISTORICAL_DAYS . " DAY;";
	$result = mysqli_query($con, $sql_query);

	mysqli_close($con);
	
	//Metrics calculation
	$self_update_url = str_replace("update_values.php", "metrics_calculation.php", $_SERVER["PHP_SELF"]);
	$self_update_url = HTTP_PREFIX . $_SERVER["SERVER_ADDR"] . $self_update_url . '?api_key=' . API_KEY . '&sender_node=' . $sender_node_safe);

	$response = file_get_contents($self_update_url);
}
else {
	mysqli_close($con);
}

?> 