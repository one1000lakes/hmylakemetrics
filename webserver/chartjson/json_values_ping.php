<?php
require '../update/database.php';

$arr = array();
$arr1 = array();
$result = array();
$i=0;
$j=0;
$previous_id='';
$id_now='';


$sql = mysqli_query($con, "SELECT tagname, timestamp, sender_node, valuefloat FROM metrics_history WHERE timestamp >= (NOW() - INTERVAL 24 HOUR) AND (tagname = 'ping1' OR tagname = 'ping2') ORDER BY sender_node, tagname, timestamp;");


while($row = mysqli_fetch_array($sql)) {

if ($row['tagname'] == 'ping1') {
	$ping_destination = 'local';
}
else {
	$ping_destination = 'main';
}

$id_now= 'Node ' . $row['sender_node'] . ' <-> ' . $ping_destination;

if (($id_now != $previous_id) && $i > 0) {
array_push($result, $arr);
$arr = array();
$j=0;
}
$previous_id = $id_now;

$arr['name'] = $id_now;
$arr['yAxis'] = 0;
$arr['data'][$j] = array(strtotime((string)$row['timestamp']) * 1000, (float)$row['valuefloat']);
$i++;
$j++;
}
array_push($result, $arr);


echo json_encode($result);
mysqli_close($con);
?>