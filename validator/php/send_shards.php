<?php
//Load config array
$configs = include('config.php');

//Read configs
$server_post_url = $configs['server_post_url'];
$sender_node = $configs['sender_node'];

//Read file
$path= $configs['datasourcepath'] . "/shards_to_sign.txt";
$contents = file_get_contents($path);

$lines = preg_split('/\r\n|\r|\n/', $contents);

//Search non empty lines for shard numbers and send
foreach ($lines as &$line) {
			
			if ($line !== '') {
			$shard_number = trim($line);
						
			$tagname = "shard-to-sign";
			$url = $server_post_url . 'sender_node=' . $sender_node . '&';
			$url = $url . 'tagname=' . $tagname . '&';
			$url = $url . 'valuetype=int&value=' . $shard_number;
			$response = file_get_contents($url);
			
			exit();
			}
			
			}



?>