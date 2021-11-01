<?php
require '../update/database.php';

$arr = array();
$arr1 = array();
$result = array();
$i=0;
$j=0;
$previous_id='';
$id_now='';


$sql = mysqli_query($con, "SELECT tagname, timestamp, sender_node, valuefloat FROM metrics_history WHERE timestamp >= (NOW() - INTERVAL 7 DAY) AND (tagname = 'signpercentage_60min_mvavg' OR (tagname = 'current-epoch-signing-percentage' AND sender_node = 1)) ORDER BY sender_node, tagname, timestamp;");


while($row = mysqli_fetch_array($sql)) {

if ($row['tagname'] == 'signpercentage_60min_mvavg') {
$id_now= 'Node ' . $row['sender_node'];
}
else {
$id_now= 'Current epoch';
}

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