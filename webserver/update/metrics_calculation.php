<?php
require 'database.php';
require 'math_functions.php';

//Check api key and exit if not provided or wrong

if(!isset($_GET["api_key"]) || $_GET["api_key"] !== API_KEY){
    mysqli_close($con);
    exit;
}

//Read passed arguments and escape them

$sender_node_notsafe = htmlspecialchars($_GET["sender_node"]);
$sender_node_safe = mysqli_real_escape_string($con, $sender_node_notsafe);

//Format self update url
$self_update_url = str_replace("metrics_calculation.php", "update_values.php", $_SERVER["PHP_SELF"]);
$self_update_url = HTTP_SERVER_ADDRESS . $self_update_url . '?api_key=' . API_KEY . '&sender_node=' . $sender_node_safe;
//$self_update_url = HTTP_PREFIX . $_SERVER["SERVER_ADDR"] . $self_update_url . '?api_key=' . API_KEY);

//Get shard number node is signing
$sql_query = "SELECT valueint FROM metrics_now WHERE tagname = 'shard-to-sign' AND sender_node = " . $sender_node_safe . " LIMIT 1;";
$result = mysqli_query($con, $sql_query);

while($row = mysqli_fetch_array($result))
{	
$shard_to_sign = $row["valueint"];
}

//Get latest mainnode shard blockid and its timestamp
$sql_query = "SELECT valueint, timestamp FROM metrics_now WHERE tagname = 'Mainnode_Shard" . $shard_to_sign . "_BlockID' AND sender_node = " . $sender_node_safe . " ORDER BY timestamp DESC LIMIT 1;";
//DEBUG: echo $sql_query;
$result = mysqli_query($con, $sql_query);

while($row = mysqli_fetch_array($result))
{	
$mainnode_latest_blockid = $row["valueint"];
$mainnode_latest_blockid_timestamp = $row["timestamp"];
}

//Select previous mainnode shard blockid and its timestamp
$sql_query = "SELECT (" . $mainnode_latest_blockid . " - valueint) as increment, timestamp, TIMESTAMPDIFF(SECOND,timestamp,'" . $mainnode_latest_blockid_timestamp . "') as timespan_seconds FROM metrics_history WHERE tagname = 'Mainnode_Shard" . $shard_to_sign . "_BlockID' AND sender_node = " . $sender_node_safe . " AND timestamp < '" . $mainnode_latest_blockid_timestamp . "' ORDER BY timestamp DESC LIMIT 1;";
//DEBUG: echo $sql_query;
$result = mysqli_query($con, $sql_query);

while($row = mysqli_fetch_array($result))
{	
$mainnode_block_increment = $row["increment"];
$mainnode_timespan_seconds = $row["timespan_seconds"];
}

//calculate current rate as how many blocks per hour
$mainnode_blocks_per_hour = floatval($mainnode_block_increment) / (floatval($mainnode_timespan_seconds) / 3600.0);


//Get latest node signature count and its timestamp
$sql_query = "SELECT valueint, timestamp FROM metrics_now WHERE tagname = 'signatures' AND sender_node = " . $sender_node_safe . " LIMIT 1;";
$result = mysqli_query($con, $sql_query);

while($row = mysqli_fetch_array($result))
{	
$localnode_latest_signature_count = $row["valueint"];
$localnode_latest_signature_count_timestamp = $row["timestamp"];
}

//Select previous node signature count and its timestamp
$sql_query = "SELECT (" . $localnode_latest_signature_count . " - valueint) as increment, timestamp, TIMESTAMPDIFF(SECOND,timestamp,'" . $localnode_latest_signature_count_timestamp . "') as timespan_seconds FROM metrics_history WHERE tagname = 'signatures' AND sender_node = " . $sender_node_safe . " AND timestamp < '" . $localnode_latest_signature_count_timestamp . "' ORDER BY timestamp DESC LIMIT 1;";
$result = mysqli_query($con, $sql_query);

while($row = mysqli_fetch_array($result))
{	
$localnode_signatures_increment = $row["increment"];
$localnode_timespan_seconds = $row["timespan_seconds"];
}

//calculate current local node rate as how many blocks per hour
$localnode_signatures_per_hour = floatval($localnode_signatures_increment) / (floatval($localnode_timespan_seconds) / 3600.0);

//calculate missed blocks rate per hour
$missed_per_hour = $mainnode_blocks_per_hour - $localnode_signatures_per_hour;

//calculate momentarily sign percentage
$sign_percentage = ($localnode_signatures_per_hour / $mainnode_blocks_per_hour) * 100.0;

//format to 2 decimals
$sign_percentage_string = number_format((float)$sign_percentage, 2, '.', '');


//Get latest node le1000 signature count and its timestamp
$sql_query = "SELECT valueint, timestamp FROM metrics_now WHERE tagname = 'le1000' AND sender_node = " . $sender_node_safe . " LIMIT 1;";
$result = mysqli_query($con, $sql_query);

while($row = mysqli_fetch_array($result))
{	
$localnode_latest_le1000_signature_count = $row["valueint"];
$localnode_latest_le1000_signature_count_timestamp = $row["timestamp"];
}

//Select previous node le1000 signature count and its timestamp
$sql_query = "SELECT (" . $localnode_latest_le1000_signature_count . " - valueint) as increment, timestamp, TIMESTAMPDIFF(SECOND,timestamp,'" . $localnode_latest_le1000_signature_count_timestamp . "') as timespan_seconds FROM metrics_history WHERE tagname = 'le1000' AND sender_node = " . $sender_node_safe . " AND timestamp < '" . $localnode_latest_le1000_signature_count_timestamp . "' ORDER BY timestamp DESC LIMIT 1;";
$result = mysqli_query($con, $sql_query);

while($row = mysqli_fetch_array($result))
{	
$localnode_le1000_signatures_increment = $row["increment"];
$localnode_le1000_timespan_seconds = $row["timespan_seconds"];
}

//calculate current rate as how many le1000 blocks per hour
$localnode_le1000_signatures_per_hour = floatval($localnode_le1000_signatures_increment) / (floatval($localnode_le1000_timespan_seconds) / 3600.0);


//calculate le1000 sign percentage (of those which are signed!)
$le1000_sign_percentage = ($localnode_le1000_signatures_per_hour / $localnode_signatures_per_hour) * 100.0;

//format to 2 decimals
$le1000_sign_percentage_string = number_format((float)$le1000_sign_percentage, 2, '.', '');


//post values
$response = file_get_contents('&sender_node=' . $sender_node_safe . '&tagname=signpercentage_instant&valuetype=float&value=' . $sign_percentage_string);
$response = file_get_contents('&sender_node=' . $sender_node_safe . '&tagname=signrate_1h&valuetype=int&value=' . intval($localnode_signatures_per_hour));
$response = file_get_contents('&sender_node=' . $sender_node_safe . '&tagname=missrate_1h&valuetype=int&value=' . intval($missed_per_hour));
$response = file_get_contents('&sender_node=' . $sender_node_safe . '&tagname=le1000_signpercentage_instant&valuetype=float&value=' . $le1000_sign_percentage_string);

//Calculate 10 minute average signpercentage (avg without min and max because sometimes if block id's are not synced perfectly at read it may give wrong average)
$sql_query = "SELECT valuefloat FROM metrics_history WHERE tagname = 'signpercentage_instant' AND sender_node = " . $sender_node_safe . " AND timestamp >= (NOW() - INTERVAL 10 MINUTE);";
$result = mysqli_query($con, $sql_query);

$arr = array();

while($row = mysqli_fetch_array($result))
{	
array_push($arr, floatval($row["valuefloat"]));
}

$sign_percentage_10min = getAverage_wo_minmax($arr);

//Limit to 0.0-100.0%
if ($sign_percentage_10min > 100.0) {
$sign_percentage_10min = 100.0;
}
if ($sign_percentage_10min < 0.0) {
$sign_percentage_10min = 0.0;
}

//Format to 2 decimals and send value
$sign_percentage_10min_string = number_format((float)$sign_percentage_10min, 2, '.', '');
$response = file_get_contents('&sender_node=' . $sender_node_safe . '&tagname=signpercentage_10min_mvavg&valuetype=float&value=' . $sign_percentage_10min_string);

//Calculate 60 minute moving average signpercentage (avg without 2 min values and 2 max values because sometimes if block id's are not synced perfectly at read it may give wrong average)
$sql_query = "SELECT valuefloat FROM metrics_history WHERE tagname = 'signpercentage_instant' AND sender_node = " . $sender_node_safe . " AND timestamp >= (NOW() - INTERVAL 60 MINUTE);";
$result = mysqli_query($con, $sql_query);

$arr = array();

while($row = mysqli_fetch_array($result))
{	
array_push($arr, floatval($row["valuefloat"]));
}

$sign_percentage_60min = getAverage_wo_2min2max($arr);

//Limit to 0.0-100.0%
if ($sign_percentage_60min > 100.0) {
$sign_percentage_60min = 100.0;
}
if ($sign_percentage_60min < 0.0) {
$sign_percentage_60min = 0.0;
}

//Format to 2 decimals and send value
$sign_percentage_60min_string = number_format((float)$sign_percentage_60min, 2, '.', '');
$response = file_get_contents('&sender_node=' . $sender_node_safe . '&tagname=signpercentage_60min_mvavg&valuetype=float&value=' . $sign_percentage_60min_string);

//Calculate 60 minute moving average le1000 signpercentage (avg without 2 min values and 2 max values because sometimes if block id's are not synced perfectly at read it may give wrong average)
$sql_query = "SELECT valuefloat FROM metrics_history WHERE tagname = 'le1000_signpercentage_instant' AND sender_node = " . $sender_node_safe . " AND timestamp >= (NOW() - INTERVAL 60 MINUTE);";
$result = mysqli_query($con, $sql_query);

$arr = array();

while($row = mysqli_fetch_array($result))
{	
array_push($arr, floatval($row["valuefloat"]));
}

$sign_percentage_le1000_60min = getAverage_wo_2min2max($arr);

//Limit to 0.0-100.0%
if ($sign_percentage_le1000_60min > 100.0) {
$sign_percentage_le1000_60min = 100.0;
}
if ($sign_percentage_le1000_60min < 0.0) {
$sign_percentage_le1000_60min = 0.0;
}

//Format to 2 decimals and send value
$sign_percentage_le1000_60min_string = number_format((float)$sign_percentage_le1000_60min, 2, '.', '');
$response = file_get_contents('&sender_node=' . $sender_node_safe . '&tagname=signpercentage_le1000_60min_mvavg&valuetype=float&value=' . $sign_percentage_le1000_60min_string);


mysqli_close($con);
?> 