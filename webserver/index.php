<!DOCTYPE html>
<?php
require 'update/database.php';
require 'staticmenus.php';
?>

<html lang="en">
<head>
  <title><?php echo SITE_TITLE; ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/hmylakemetrics-template.css">
  <style>
  .fakeimg {
    height: 200px;
    background: #aaa;
  }
  
  #dynamic_data{
            border: 1px solid gray;
            border-radius: 10px;
            padding: 10px;
            text-decoration:none;
            float:left;
            margin:4px;
            text-align:center;
            display: block;
            color: green;
        }
  </style>
</head>
<body>

<?php
//draw navigation menu
staticmenus_drawnav();
?>

<main role="main" class="container-fluid">

  <div class="hmylakemetrics-template">
    
	<div class="lead d-flex justify-content-center">
    <div>Validator status</div>
	</div>
	<br>
	
	<div class="row justify-content-center">


	
	<?php
	//Load shard number that nodes are signing
    $result = mysqli_query($con, "SELECT valueint FROM metrics_now WHERE tagname = 'shard-to-sign' LIMIT 1;");
	while($row = mysqli_fetch_array($result))
	{
		$shard_to_sign = $row['valueint'];
	}

	//Set defaults to node 1 pings (if disabled)
	$node1_ping_to_local = 'N/A';
	$node1_ping_to_main = 'N/A';

	//Node 1 values
	$result = mysqli_query($con, "SELECT * FROM metrics_now WHERE sender_node = 1;");
	while($row = mysqli_fetch_array($result))
	{
	if ($row['tagname'] == 'Useddisk_GB') {
	$node1_used_disk = $row['valuefloat'];
	}
	elseif ($row['tagname'] == 'Disksize_GB') {
	$node1_disksize = $row['valuefloat'];
	}
	elseif ($row['tagname'] == 'Useddisk_percent') {
	$node1_disk_percent = $row['valueint'];
	}
	elseif ($row['tagname'] == 'signpercentage_instant') {
	$node1_signpercentage_instant = $row['valuefloat'];
	}
	elseif ($row['tagname'] == 'signpercentage_10min_mvavg') {
	$node1_10min_sign_percent = $row['valuefloat'];
	}
	elseif ($row['tagname'] == 'signpercentage_60min_mvavg') {
	$node1_60min_sign_percent = $row['valuefloat'];
	}
	elseif ($row['tagname'] == 'signpercentage_le1000_60min_mvavg') {
	$node1_lasthour_le1000_sign_percent = $row['valuefloat'];
	}
	elseif ($row['tagname'] == 'Localnode_Shard0_BlockID') {
	$node1_localnodeshard0_blockid = $row['valueint'];
	}
	elseif ($row['tagname'] == 'Localnode_Shard' . $shard_to_sign . '_BlockID') {
	$node1_localnodeshardx_blockid = $row['valueint'];
	}
	elseif ($row['tagname'] == 'ping1') {
	$node1_ping_to_local = $row['valuefloat'];
	}
	elseif ($row['tagname'] == 'ping2') {
	$node1_ping_to_main = $row['valuefloat'];
	}
	}
	
	//24h average
	$result = mysqli_query($con, "SELECT AVG(valuefloat) as average_sign_percent, sender_node FROM metrics_history WHERE tagname = 'signpercentage_10min_mvavg' AND timestamp >= (NOW() - INTERVAL 24 HOUR) GROUP BY sender_node;");
	while($row = mysqli_fetch_array($result))
	{
	if ($row['sender_node'] == '1') {
	$node1_24h_signpercent = number_format((float)$row['average_sign_percent'], 2, '.', '');
	}
	elseif ($row['sender_node'] == '2') {
	$node2_24h_signpercent = number_format((float)$row['average_sign_percent'], 2, '.', '');
	}
	}
	
	//Set defaults to node 2 pings (if disabled)
	$node2_ping_to_local = 'N/A';
	$node2_ping_to_main = 'N/A';
	
	//Node 2 values
	$result = mysqli_query($con, "SELECT * FROM metrics_now WHERE sender_node = 2;");
	while($row = mysqli_fetch_array($result))
	{
	if ($row['tagname'] == 'Useddisk_GB') {
	$node2_used_disk = $row['valuefloat'];
	}
	elseif ($row['tagname'] == 'Disksize_GB') {
	$node2_disksize = $row['valuefloat'];
	}
	elseif ($row['tagname'] == 'Useddisk_percent') {
	$node2_disk_percent = $row['valueint'];
	}
	elseif ($row['tagname'] == 'signpercentage_instant') {
	$node2_signpercentage_instant = $row['valuefloat'];
	}
	elseif ($row['tagname'] == 'signpercentage_10min_mvavg') {
	$node2_10min_sign_percent = $row['valuefloat'];
	}
	elseif ($row['tagname'] == 'signpercentage_60min_mvavg') {
	$node2_60min_sign_percent = $row['valuefloat'];
	}
	elseif ($row['tagname'] == 'signpercentage_le1000_60min_mvavg') {
	$node2_lasthour_le1000_sign_percent = $row['valuefloat'];
	}
	elseif ($row['tagname'] == 'Localnode_Shard0_BlockID') {
	$node2_localnodeshard0_blockid = $row['valueint'];
	}
	elseif ($row['tagname'] == 'Localnode_Shard' . $shard_to_sign . '_BlockID') {
	$node2_localnodeshardx_blockid = $row['valueint'];
	}
	elseif ($row['tagname'] == 'ping1') {
	$node2_ping_to_local = $row['valuefloat'];
	}
	elseif ($row['tagname'] == 'ping2') {
	$node2_ping_to_main = $row['valuefloat'];
	}
	}
	
	//Combined values
	$result = mysqli_query($con, "SELECT * FROM metrics_now WHERE tagname LIKE 'current-epoch-%' ORDER BY timestamp;");
	while($row = mysqli_fetch_array($result))
	{
	if ($row['tagname'] == 'current-epoch-signed') {
	$combined_currentepoch_signed = $row['valueint'];
	}
	elseif ($row['tagname'] == 'current-epoch-signing-percentage') {
	$combined_currentepoch_percentage = $row['valuefloat'];
	}
	elseif ($row['tagname'] == 'current-epoch-to-sign') {
	$combined_currentepoch_tosign = $row['valueint'];
	}
	elseif ($row['tagname'] == 'current-epoch-missed') {
	$combined_currentepoch_missed = $row['valueint'];
	}
	}
	
	//Mainnode block ids
	$result = mysqli_query($con, "SELECT * FROM metrics_now WHERE tagname LIKE 'Mainnode_Shard%' ORDER BY timestamp;");
	while($row = mysqli_fetch_array($result))
	{
	if ($row['tagname'] == 'Mainnode_Shard0_BlockID') {
	$mainnode_shard0_blockid = $row['valueint'];
	}
	elseif ($row['tagname'] == 'Mainnode_Shard' . $shard_to_sign . '_BlockID') {
	$mainnode_shardx_blockid = $row['valueint'];
	}
	}
	
	//Online/Offline
	$result = mysqli_query($con, "SELECT sender_node, TIMESTAMPDIFF(SECOND,timestamp,NOW()) as timespan FROM metrics_now WHERE tagname = 'Localnode_Shard" . $shard_to_sign . "_BlockID';");
	while($row = mysqli_fetch_array($result))
	{
	if ($row['sender_node'] == '1') {
	$node1_updated_sec_ago = intval($row['timespan']);
	}
	elseif ($row['sender_node'] == '2') {
	$node2_updated_sec_ago = intval($row['timespan']);
	}
	}
	
	//If not updated in 180 secs, mark offline, otherwise online
	if ($node1_updated_sec_ago < 180) {
	$node1_updated_cell_value = '<td class="bg-success">ONLINE</td>';
	}
	else {
	$node1_updated_cell_value = '<td class="bg-danger">OFFLINE</td>';
	}
	
	if ($node2_updated_sec_ago < 180) {
	$node2_updated_cell_value = '<td class="bg-success">ONLINE</td>';
	}
	else {
	$node2_updated_cell_value = '<td class="bg-danger">OFFLINE</td>';
	}
	
	if ($node1_updated_sec_ago < 180 || $node2_updated_sec_ago < 180) {
	$combined_updated_cell_value = '<td class="bg-success">ONLINE</td>';
	}
	else {
	$combined_updated_cell_value = '<td class="bg-danger">OFFLINE</td>';
	}


	echo '<table class="table table-responsive table-striped table-primary w-auto">
	<tr>
	<th>Metric</th>
	<th>Node 1</th>
	<th>Node 2</th>
	<th>Combined</th>
	</tr>';

	echo "<tr>";
	echo "<td>Status</td>";
	//echo '<td class="bg-success">yes</td>';
	//echo '<td class="bg-danger">no</td>';
	echo $node1_updated_cell_value;
	echo $node2_updated_cell_value;
	echo $combined_updated_cell_value;
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>Last update [sec ago]</td>";
	echo '<td>' . $node1_updated_sec_ago . '</td>';
	echo '<td>' . $node2_updated_sec_ago . '</td>';
	echo "<td></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>Signing shard</td>";
	echo "<td>" . $shard_to_sign . "</td>";
	echo "<td>" . $shard_to_sign . "</td>";
	echo "<td></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>Sign percentage 10min avg [%]</td>";
	echo "<td>" . $node1_10min_sign_percent . "</td>";
	echo "<td>" . $node2_10min_sign_percent . "</td>";
	echo "<td></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>Sign percentage 60min avg [%]</td>";
	echo "<td>" . $node1_60min_sign_percent . "</td>";
	echo "<td>" . $node2_60min_sign_percent . "</td>";
	echo "<td></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>Last 24h sign avg [%]</td>";
	echo "<td>" . $node1_24h_signpercent . "</td>";
	echo "<td>" . $node2_24h_signpercent . "</td>";
	echo "<td></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>Signed <1000ms 60min avg [%]</td>";
	echo "<td>" . $node1_lasthour_le1000_sign_percent . "</td>";
	echo "<td>" . $node2_lasthour_le1000_sign_percent . "</td>";
	echo "<td></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>Ping to main node [ms]</td>";
	echo "<td>" . $node1_ping_to_main . "</td>";
	echo "<td>" . $node2_ping_to_main . "</td>";
	echo "<td></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>Ping to local country [ms]</td>";
	echo "<td>" . $node1_ping_to_local . "</td>";
	echo "<td>" . $node2_ping_to_local . "</td>";
	echo "<td></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>Current epoch signed</td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td>" . $combined_currentepoch_signed . "</td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>Current epoch to sign</td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td>" . $combined_currentepoch_tosign . "</td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>Current epoch missed</td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td>" . $combined_currentepoch_missed . "</td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>Current epoch signed [%]</td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td>" . $combined_currentepoch_percentage . "</td>";
	echo "</tr>";
	
	//If signing other shard than 0 then show also shard 0 block ids. Otherwise just shard 0
	if ($shard_to_sign !== '0') {
	echo "<tr>";
	echo "<td>Shard 0 block id</td>";
	echo "<td>" . $node1_localnodeshard0_blockid . "</td>";
	echo "<td>" . $node2_localnodeshard0_blockid . "</td>";
	echo "<td>" . $mainnode_shard0_blockid . "</td>";
	echo "</tr>";
	}
	
	echo "<tr>";
	echo "<td>Shard " . $shard_to_sign . " block id</td>";
	echo "<td>" . $node1_localnodeshardx_blockid . "</td>";
	echo "<td>" . $node2_localnodeshardx_blockid . "</td>";
	echo "<td>" . $mainnode_shardx_blockid . "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Disk usage [GB]</td>";
	echo "<td>" . $node1_used_disk . "</td>";
	echo "<td>" . $node2_used_disk . "</td>";
	echo "<td></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>Disk size [GB]</td>";
	echo "<td>" . $node1_disksize . "</td>";
	echo "<td>" . $node2_disksize . "</td>";
	echo "<td></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>Disk usage [%]</td>";
	echo "<td>" . $node1_disk_percent . "</td>";
	echo "<td>" . $node2_disk_percent . "</td>";
	echo "<td></td>";
	echo "</tr>";
	
	echo "</table>";
	
	?>

    </div>
	<div class="row justify-content-center">
	  <a href="https://github.com/one1000lakes/hmylakemetrics" class="link-primary mt-4">created with hmylakemetrics</a>
	</div>
  </div>

</main>

<script src="js/jquery-3.0.0.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/feather.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>

<?php
mysqli_close($con);
?>