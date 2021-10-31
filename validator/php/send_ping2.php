<?php
//Load config array
$configs = include('config.php');

//Read configs
$server_post_url = $configs['server_post_url'];
$sender_node = $configs['sender_node'];

//Read file
$path= $configs['datasourcepath'] . "/ping2.txt";
$contents = file_get_contents($path);

$lines = preg_split('/\r\n|\r|\n/', $contents);

//Search line which contains ping summary and post avg
foreach ($lines as &$line) {
			
			if (strpos($line, ' min/avg/max/mdev') !== false) {
			$line_spaces_removed = preg_replace('!\s+!', ' ', $line); //Replace multiple spaces
			$line_value_array = explode(' ', trim($line_spaces_removed));
			$pings_with_slashes = explode('/', $line_value_array[3]);
			$avg_ping_ms = floatval($pings_with_slashes[1]);
			$avg_ping_ms_string = number_format((float)$avg_ping_ms, 1, '.', '');
			
			$tagname = "ping2";
			$url = $server_post_url . 'sender_node=' . $sender_node . '&';
			$url = $url . 'tagname=' . $tagname . '&';
			$url = $url . 'valuetype=float&value=' . $avg_ping_ms_string;
			$response = file_get_contents($url);
			
			exit();
			}
			
			}



?>