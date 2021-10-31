<?php
//Load config array
$configs = include('config.php');

//Read configs
$server_post_url = $configs['server_post_url'];
$sender_node = $configs['sender_node'];

//Read file
$path= $configs['datasourcepath'] . "/metrics_local.txt";
$contents = file_get_contents($path);

$lines = preg_split('/\r\n|\r|\n/', $contents);

//Parse and send validator info
foreach ($lines as &$line) {
			
			if (strpos($line, 'hmy_consensus_bingo{consensus="bingo"}') !== false) {
			$line_value_array = explode(' ', trim($line));
			$bingo = str_replace(",", "", $line_value_array[1]);
			
			$tagname = "bingo";
			$url = $server_post_url . 'sender_node=' . $sender_node . '&';
			$url = $url . 'tagname=' . $tagname . '&';
			$url = $url . 'valuetype=int&value=' . $bingo;
			$response = file_get_contents($url);
			}
			
			if (strpos($line, 'hmy_consensus_bingo{consensus="signatures"}') !== false) {
			$line_value_array = explode(' ', trim($line));
			$signatures = str_replace(",", "", $line_value_array[1]);
			
			$tagname = "signatures";
			$url = $server_post_url . 'sender_node=' . $sender_node . '&';
			$url = $url . 'tagname=' . $tagname . '&';
			$url = $url . 'valuetype=int&value=' . $signatures;
			$response = file_get_contents($url);
			}
			
			if (strpos($line, 'hmy_consensus_finality_bucket{le="1000"}') !== false) {
			$line_value_array = explode(' ', trim($line));
			$le1000 = str_replace(",", "", $line_value_array[1]);
			
			$tagname = "le1000";
			$url = $server_post_url . 'sender_node=' . $sender_node . '&';
			$url = $url . 'tagname=' . $tagname . '&';
			$url = $url . 'valuetype=int&value=' . $le1000;
			$response = file_get_contents($url);
			
			exit();
			}
			
			}


?> 
