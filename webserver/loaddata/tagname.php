<?php
require '../update/database.php';

//Check api key and exit if not provided or wrong
if(!isset($_GET["api_key"]) || $_GET["api_key"] !== API_KEY){
    mysqli_close($con);
    exit;
}

$tagname_notsafe = $_GET["tagname"];
$tagname_safe = mysqli_real_escape_string($con, $tagname_notsafe);

//If sender node specified then read value from that sender node, if not then read only with tagname and pick newest entry
if (isset($_GET["sender_node"])) {
	$sender_node_notsafe = $_GET["sender_node"];
    $sender_node_safe = mysqli_real_escape_string($con, $sender_node_notsafe);
	
	$result = mysqli_query($con, "SELECT valueint, TIMESTAMPDIFF(SECOND,timestamp,NOW()) as seconds_ago FROM metrics_now WHERE tagname = '" . $tagname_safe . "' AND sender_node = '" . $sender_node_safe . "';");
}
else {
	$result = mysqli_query($con, "SELECT valueint, TIMESTAMPDIFF(SECOND,timestamp,NOW()) as seconds_ago FROM metrics_now WHERE tagname = '" . $tagname_safe . "' ORDER BY timestamp DESC LIMIT 1;");
}

$value = '';

while($row = mysqli_fetch_array($result))
{	
$value = $row["valueint"];
$seconds_ago = $row["seconds_ago"];
}

echo "<h2>" . $value . "</h2>  (updated " . $seconds_ago. " sec ago)";

?>