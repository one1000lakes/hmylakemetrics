<?php
//Load config array
$configs = include('config.php');

//Read configs
$server_post_url = $configs['server_post_url'];
$sender_node = $configs['sender_node'];

//Read file
$path= $configs['datasourcepath'] . "/headers_localnode.txt";
$contents = file_get_contents($path);

$lines = preg_split('/\r\n|\r|\n/', $contents);

//Parse and send localnode headers
foreach ($lines as &$line) {
			
			if (strpos($line, '"shardID":') !== false) {
			$line_value_array = explode(' ', trim($line));
			$shard_id = str_replace(",", "", $line_value_array[1]);
			}
			
			if (strpos($line, '"viewID":') !== false) {
			$line_value_array = explode(' ', trim($line));
			$view_id = str_replace(",", "", $line_value_array[1]);
			
			$tagname = "Localnode_Shard" . $shard_id . "_BlockID";
			$url = $server_post_url . 'sender_node=' . $sender_node . '&';
			$url = $url . 'tagname=' . $tagname . '&';
			$url = $url . 'valuetype=int&value=' . $view_id;
			$response = file_get_contents($url);
			}
			
			}

//Read file
$path= $configs['datasourcepath'] . "/headers_main.txt";
$contents = file_get_contents($path);

$lines = preg_split('/\r\n|\r|\n/', $contents);

//Parse and send main node headers
foreach ($lines as &$line) {
			
			if (strpos($line, '"shardID":') !== false) {
			$line_value_array = explode(' ', trim($line));
			$shard_id = str_replace(",", "", $line_value_array[1]);
			}
			
			if (strpos($line, '"viewID":') !== false) {
			$line_value_array = explode(' ', trim($line));
			$view_id = str_replace(",", "", $line_value_array[1]);
			
			$tagname = "Mainnode_Shard" . $shard_id . "_BlockID";
			$url = $server_post_url . 'sender_node=' . $sender_node . '&';
			$url = $url . 'tagname=' . $tagname . '&';
			$url = $url . 'valuetype=int&value=' . $view_id;
			$response = file_get_contents($url);
			}
			
			}

//Read file
$path= $configs['datasourcepath'] . "/headers_remotebackup.txt";
$contents = file_get_contents($path);

$lines = preg_split('/\r\n|\r|\n/', $contents);

//Parse and send remote node headers
foreach ($lines as &$line) {
			
			if (strpos($line, '"shardID":') !== false) {
			$line_value_array = explode(' ', trim($line));
			$shard_id = str_replace(",", "", $line_value_array[1]);
			}
			
			if (strpos($line, '"viewID":') !== false) {
			$line_value_array = explode(' ', trim($line));
			$view_id = str_replace(",", "", $line_value_array[1]);
			
			$tagname = "Remotenode_Shard" . $shard_id . "_BlockID";
			$url = $server_post_url . 'sender_node=' . $sender_node . '&';
			$url = $url . 'tagname=' . $tagname . '&';
			$url = $url . 'valuetype=int&value=' . $view_id;
			$response = file_get_contents($url);
			}
			
			}

?> 
