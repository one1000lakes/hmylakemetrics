<?php
function getMedian($numbers){
//Returns median value of value array

sort($numbers);
$count = sizeof($numbers);   // cache the count
$index = floor($count/2);  // cache the index
if (!$count) {
    echo "no values";
	return -1;
} elseif ($count & 1) {    // count is odd
    return $numbers[$index];
} else {                   // count is even
    return ($numbers[$index-1] + $numbers[$index]) / 2;
}
}

function getAverage_wo_minmax($numbers){
//Counts average of middle numbers leaving out 1 biggest and 1 smallest. Minimum count of 3 numbers

sort($numbers);

$limit = sizeof($numbers) - 1;
$counted = 0;
$sum = 0.0;

//If not enough numbers then return 0.0;
if (sizeof($numbers) < 3) {
	return 0.0;
}

for ($i = 1; $i < $limit; ++$i) {
    $sum = $sum + $numbers[$i];
	$counted = $counted + 1;
}

return $sum / $counted;
}

function getAverage_wo_2min2max($numbers){
//Counts average of middle numbers leaving out 2 biggest and 2 smallest. Minimum count of 5 numbers

sort($numbers);

$limit = sizeof($numbers) - 2;
$counted = 0;
$sum = 0.0;

//If not enough numbers then return 0.0;
if (sizeof($numbers) < 5) {
	return 0.0;
}

for ($i = 2; $i < $limit; ++$i) {
    $sum = $sum + $numbers[$i];
	$counted = $counted + 1;
}

return $sum / $counted;
}

function store_values($con, $sender_node_safe, $tagname_safe, $valuetype_safe, $value_safe){
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
}

function metric_calculations($con, $sender_node_safe){
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
store_values($con, $sender_node_safe, 'signpercentage_instant', 'float', $sign_percentage_string);
store_values($con, $sender_node_safe, 'signrate_1h', 'int', intval($localnode_signatures_per_hour));
store_values($con, $sender_node_safe, 'missrate_1h', 'int', intval($missed_per_hour));
store_values($con, $sender_node_safe, 'le1000_signpercentage_instant', 'float', $le1000_sign_percentage_string);

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
store_values($con, $sender_node_safe, 'signpercentage_10min_mvavg', 'float', $sign_percentage_10min_string);

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
store_values($con, $sender_node_safe, 'signpercentage_60min_mvavg', 'float', $sign_percentage_60min_string);

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
store_values($con, $sender_node_safe, 'signpercentage_le1000_60min_mvavg', 'float', $sign_percentage_le1000_60min_string);
}



?> 