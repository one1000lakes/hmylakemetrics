<?php
require '../update/database.php';

$arr = array();
$arr1 = array();
$result = array();
$i=0;
$j=0;
$previous_id='';
$id_now='';


$sql = mysqli_query($con, "SELECT tagname, timestamp, sender_node, valueint FROM metrics_history WHERE timestamp >= (NOW() - INTERVAL 24 HOUR) AND tagname = 'current-epoch-missed' AND sender_node = 2 ORDER BY sender_node, tagname, timestamp;");


while($row = mysqli_fetch_array($sql)) {

$id_now= 'Missed blocks';

if (($id_now != $previous_id) && $i > 0) {
array_push($result, $arr);
$arr = array();
$j=0;
}
$previous_id = $id_now;

$arr['name'] = $id_now;
$arr['yAxis'] = 0;
$arr['data'][$j] = array(strtotime((string)$row['timestamp']) * 1000, (float)$row['valueint']);
$i++;
$j++;
}
array_push($result, $arr);


echo json_encode($result);
mysqli_close($con);
?>